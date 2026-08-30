<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CotIndicator;
use App\Models\CotRating;
use App\Models\PpstStandard;
use App\Services\CotIndicatorService;
use Illuminate\Http\Request;

/**
 * Admin management of the PPST standards library (Domain → Strand → Indicator).
 *
 * PPST is the standards foundation of the system and is a single, unversioned
 * library: it is not scoped per school year. COT instruments (CotIndicatorVersion)
 * assemble a subset of these standards into per-school-year observation forms and
 * reference the canonical standard through cot_indicators.ppst_standard_id.
 *
 * Historical observations and ratings keep their own snapshots, so editing the
 * PPST library never rewrites past COT ratings.
 */
class PpstStandardController extends Controller
{
    protected CotIndicatorService $cotIndicatorService;

    public function __construct(CotIndicatorService $cotIndicatorService)
    {
        $this->cotIndicatorService = $cotIndicatorService;
    }

    public function index()
    {
        $standards = PpstStandard::orderBy('sort_order')->orderBy('indicator_code')->get();

        $domains = $standards
            ->groupBy('domain')
            ->map(fn ($domainStandards) => $domainStandards->groupBy('strand'));

        $totals = [
            'domains' => $standards->groupBy('domain')->count(),
            'strands' => $standards->groupBy('strand')->count(),
            'indicators' => $standards->count(),
            'active' => $standards->where('is_active', true)->count(),
            'inactive' => $standards->where('is_active', false)->count(),
        ];

        // Domain meta for organized, color-coded presentation (kept in sync with config/ppst.php order)
        $domainMeta = [
            'Content Knowledge and Pedagogy' => ['number' => 1, 'color' => 'indigo', 'icon' => 'fa-book-open'],
            'Learning Environment' => ['number' => 2, 'color' => 'emerald', 'icon' => 'fa-school'],
            'Diversity of Learners' => ['number' => 3, 'color' => 'amber', 'icon' => 'fa-users'],
            'Curriculum and Planning' => ['number' => 4, 'color' => 'violet', 'icon' => 'fa-clipboard-list'],
            'Assessment and Reporting' => ['number' => 5, 'color' => 'rose', 'icon' => 'fa-chart-bar'],
            'Community Linkages and Professional Engagement' => ['number' => 6, 'color' => 'teal', 'icon' => 'fa-handshake'],
            'Personal Growth and Professional Development' => ['number' => 7, 'color' => 'blue', 'icon' => 'fa-seedling'],
        ];

        $usageByStandard = CotIndicator::query()
            ->whereNotNull('ppst_standard_id')
            ->with('version:id,school_year,label')
            ->get()
            ->groupBy('ppst_standard_id')
            ->map(function ($indicators) {
                return $indicators
                    ->pluck('version')
                    ->filter()
                    ->unique('id')
                    ->map(fn ($version) => [
                        'label' => $version->label,
                        'school_year' => $version->school_year,
                    ])
                    ->values()
                    ->toArray();
            });

        return view('admin.ppst-standards.index', compact(
            'domains',
            'totals',
            'usageByStandard',
            'standards',
            'domainMeta'
        ));
    }

    public function create()
    {
        $domainOptions = $this->getDomainOptions();
        $defaultSortOrder = (int) PpstStandard::max('sort_order') + 1;

        return view('admin.ppst-standards.create', compact('domainOptions', 'defaultSortOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $strand = $this->strandFromCode($validated['indicator_code']);

        PpstStandard::create([
            'domain' => $validated['domain'],
            'strand' => $strand,
            'indicator_code' => $validated['indicator_code'],
            'description' => $validated['description'],
            'sort_order' => $validated['sort_order'] ?? ((int) PpstStandard::max('sort_order') + 1),
            'is_active' => !empty($validated['is_active']),
        ]);

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.ppst-standards.index')
            ->with('success', "PPST indicator {$validated['indicator_code']} added.");
    }

    public function edit(PpstStandard $ppstStandard)
    {
        $domainOptions = $this->getDomainOptions();

        return view('admin.ppst-standards.edit', compact('ppstStandard', 'domainOptions'));
    }

    public function update(Request $request, PpstStandard $ppstStandard)
    {
        $validated = $request->validate($this->rules($ppstStandard->id));

        $ppstStandard->update([
            'domain' => $validated['domain'],
            'strand' => $this->strandFromCode($validated['indicator_code']),
            'indicator_code' => $validated['indicator_code'],
            'description' => $validated['description'],
            'sort_order' => $validated['sort_order'] ?? $ppstStandard->sort_order,
            'is_active' => !empty($validated['is_active']),
        ]);

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.ppst-standards.index')
            ->with('success', "PPST indicator {$validated['indicator_code']} updated.");
    }

    /**
     * Quick activate/deactivate toggle. Deactivating keeps historical
     * observations intact and only removes the standard from new COT picks.
     */
    public function toggleActive(Request $request, PpstStandard $ppstStandard)
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ppstStandard->update([
            'is_active' => !empty($validated['is_active']),
        ]);

        $this->cotIndicatorService->clearCache();

        $state = $ppstStandard->fresh()->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.ppst-standards.index')
            ->with('success', "PPST indicator {$ppstStandard->indicator_code} {$state}.");
    }

    /**
     * Only unreferenced standards can be deleted. Standards referenced by a COT
     * template or by historical observation ratings must be deactivated instead
     * so linked COT indicators / observation snapshots stay intact.
     */
    public function destroy(PpstStandard $ppstStandard)
    {
        if ($ppstStandard->cotIndicators()->exists()) {
            return redirect()->route('admin.ppst-standards.index')
                ->with('error', "PPST indicator {$ppstStandard->indicator_code} is referenced by COT indicators. Deactivate it instead of deleting.");
        }

        if (CotRating::where('indicator_code', $ppstStandard->indicator_code)->exists()) {
            return redirect()->route('admin.ppst-standards.index')
                ->with('error', "PPST indicator {$ppstStandard->indicator_code} appears in historical observation records. Deactivate it instead of deleting.");
        }

        $code = $ppstStandard->indicator_code;
        $ppstStandard->delete();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.ppst-standards.index')
            ->with('success', "PPST indicator {$code} deleted.");
    }

    private function rules(?int $exceptId = null): array
    {
        $unique = 'unique:ppst_standards,indicator_code';
        if ($exceptId !== null) {
            $unique .= ",{$exceptId}";
        }

        return [
            'domain' => ['required', 'string', 'max:255'],
            'indicator_code' => ['required', 'string', 'max:20', 'regex:/^\d{1,2}\.\d{1,2}\.\d{1,2}$/', $unique],
            'description' => ['required', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function strandFromCode(string $code): string
    {
        return implode('.', array_slice(explode('.', $code), 0, 2));
    }

    private function getDomainOptions(): array
    {
        return PpstStandard::query()
            ->distinct()
            ->orderBy('sort_order')
            ->pluck('domain')
            ->unique()
            ->values()
            ->toArray();
    }
}
