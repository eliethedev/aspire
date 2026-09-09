<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TeacherCareerStage;
use App\Http\Controllers\Controller;
use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\PpstStandard;
use App\Services\CareerStageResolver;
use App\Services\CotIndicatorService;
use App\Services\CotDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CotIndicatorController extends Controller
{
    protected CotIndicatorService $cotIndicatorService;

    protected CareerStageResolver $careerStageResolver;

    public function __construct(CotIndicatorService $cotIndicatorService, CareerStageResolver $careerStageResolver)
    {
        $this->cotIndicatorService = $cotIndicatorService;
        $this->careerStageResolver = $careerStageResolver;
    }

    public function index()
    {
        $versions = CotIndicatorVersion::withCount('indicators')
            ->withCount('observations')
            ->orderBy('school_year', 'desc')
            ->get();

        return view('admin.cot-indicators.index', compact('versions'));
    }

    public function downloadTemplate(CotIndicatorVersion $cotIndicatorVersion)
    {
        $service = app(CotDocumentService::class);

        $filename = $service->templateFilename($cotIndicatorVersion) . '.docx';
        $temp = $service->templateDocumentPath($cotIndicatorVersion);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function create()
    {
        $schoolYears = $this->getSchoolYearOptions();
        $rateeRoles = $this->getRateeRoleOptions();
        $careerStages = TeacherCareerStage::options();

        return view('admin.cot-indicators.create', array_merge(
            compact('schoolYears', 'rateeRoles', 'careerStages'),
            $this->versionContextViewData()
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'label' => 'required|string|max:255',
            'framework' => ['nullable', 'string', 'max:50', Rule::in(array_keys($this->careerStageResolver->frameworks()))],
            'career_track' => ['nullable', 'string', 'max:60'],
            'ratee_position' => ['nullable', 'string', 'max:60'],
            'instrument' => ['nullable', 'string', 'max:50'],
            'ratee_role' => ['nullable', 'string', 'max:50'],
            'career_stage' => ['nullable', 'string', 'max:50'],
            'requires_post_conference' => ['nullable', 'boolean'],
            'is_default' => 'nullable|boolean',
        ]);

        $context = $this->resolveVersionContext($validated);

        if (! $context['ok']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['career_track' => $context['error']]);
        }

        if ($this->versionExistsFor(
            $validated['school_year'],
            $context['framework'],
            $context['career_track'],
            $context['ratee_position'],
            $context['instrument'],
            $context['ratee_role'],
            $context['career_stage'],
        )) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['school_year' => 'A version already exists for this school year, framework, career track, and ratee position.']);
        }

        $version = CotIndicatorVersion::create([
            'school_year' => $validated['school_year'],
            'label' => $validated['label'],
            'framework' => $context['framework'],
            'career_track' => $context['career_track'],
            'ratee_position' => $context['ratee_position'],
            'instrument' => $context['instrument'],
            'ratee_role' => $context['ratee_role'],
            'career_stage' => $context['career_stage'],
            'requires_post_conference' => $validated['requires_post_conference'] ?? true,
            'is_default' => ! empty($validated['is_default']),
            'status' => CotIndicatorVersion::STATUS_DRAFT,
        ]);

        if ($version->is_default) {
            CotIndicatorVersion::where('id', '!=', $version->id)->update(['is_default' => false]);
        }

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $version)
            ->with('success', 'COT indicator version created. Add indicators below.');
    }

    public function edit(CotIndicatorVersion $cotIndicatorVersion)
    {
        $cotIndicatorVersion->load(['indicators', 'observations']);
        $schoolYears = $this->getSchoolYearOptions();
        $rateeRoles = $this->getRateeRoleOptions();
        $careerStages = TeacherCareerStage::options();

        return view('admin.cot-indicators.edit', array_merge(
            compact('cotIndicatorVersion', 'schoolYears', 'rateeRoles', 'careerStages'),
            $this->versionContextViewData(),
            $this->ppstPickerViewData($cotIndicatorVersion)
        ));
    }

    /**
     * View data for the "Add from PPST Standards" picker in the COT editor:
     * the standards library grouped by domain → strand, plus the set of codes
     * already used by this version (so the picker can flag/disable them).
     */
    private function ppstPickerViewData(CotIndicatorVersion $cotIndicatorVersion): array
    {
        $standards = PpstStandard::query()->ordered()->get();

        $domains = $standards
            ->groupBy('domain')
            ->map(fn ($domainStandards) => $domainStandards->groupBy('strand'));

        $usedCodes = $cotIndicatorVersion->indicators
            ->pluck('code')
            ->filter()
            ->flip();

        return [
            'ppstStandards' => $standards,
            'ppstDomains' => $domains,
            'ppstUsedCodes' => $usedCodes,
        ];
    }

    public function update(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()
                ->with('error', 'Published or archived versions are immutable. Unpublish before editing.');
        }

        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
            'label' => 'required|string|max:255',
            'framework' => ['nullable', 'string', 'max:50', Rule::in(array_keys($this->careerStageResolver->frameworks()))],
            'career_track' => ['nullable', 'string', 'max:60'],
            'ratee_position' => ['nullable', 'string', 'max:60'],
            'instrument' => ['nullable', 'string', 'max:50'],
            'ratee_role' => ['nullable', 'string', 'max:50'],
            'career_stage' => ['nullable', 'string', 'max:50'],
            'requires_post_conference' => ['nullable', 'boolean'],
            'is_default' => 'nullable|boolean',
        ]);

        $context = $this->resolveVersionContext($validated, $cotIndicatorVersion);

        if (! $context['ok']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['career_track' => $context['error']]);
        }

        $identityChanged = $cotIndicatorVersion->framework !== $context['framework']
            || $cotIndicatorVersion->career_track !== $context['career_track']
            || $cotIndicatorVersion->ratee_position !== $context['ratee_position']
            || $cotIndicatorVersion->instrument !== $context['instrument'];

        if ($identityChanged && $cotIndicatorVersion->observations()->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['framework' => 'This version is used by existing observations. Create a new version instead of changing its framework, track, or position.']);
        }

        if ($this->versionExistsFor(
            $validated['school_year'],
            $context['framework'],
            $context['career_track'],
            $context['ratee_position'],
            $context['instrument'],
            $context['ratee_role'],
            $context['career_stage'],
            $cotIndicatorVersion->id,
        )) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['school_year' => 'A version already exists for this school year, framework, career track, and ratee position.']);
        }

        $cotIndicatorVersion->update([
            'school_year' => $validated['school_year'],
            'label' => $validated['label'],
            'framework' => $context['framework'],
            'career_track' => $context['career_track'],
            'ratee_position' => $context['ratee_position'],
            'instrument' => $context['instrument'],
            'ratee_role' => $context['ratee_role'],
            'career_stage' => $context['career_stage'],
            'requires_post_conference' => $validated['requires_post_conference'] ?? $cotIndicatorVersion->requires_post_conference ?? true,
            'is_default' => ! empty($validated['is_default']),
        ]);

        if (! empty($validated['is_default'])) {
            CotIndicatorVersion::where('id', '!=', $cotIndicatorVersion->id)->update(['is_default' => false]);
        }

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', 'COT indicator version updated.');
    }

    public function destroy(CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()
                ->with('error', 'Only draft versions can be deleted. Archived versions are kept for historical observations.');
        }

        if ($cotIndicatorVersion->observations()->exists()) {
            return redirect()->back()
                ->with('error', 'This version cannot be deleted because observations reference it. Archive it instead.');
        }

        $cotIndicatorVersion->delete();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.index')
            ->with('success', 'COT indicator version deleted.');
    }

    public function publish(CotIndicatorVersion $cotIndicatorVersion)
    {
        if ($cotIndicatorVersion->isPublished()) {
            return redirect()->back()->with('error', 'This version is already published.');
        }

        if ($cotIndicatorVersion->indicators()->count() === 0) {
            return redirect()->back()->with('error', 'Add at least one indicator before publishing.');
        }

        $cotIndicatorVersion->publish();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.index')
            ->with('success', "Version for SY {$cotIndicatorVersion->school_year} published. It is now the default and is immutable.");
    }

    public function unpublish(CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->isPublished()) {
            return redirect()->back()->with('error', 'Only published versions can be moved back to draft.');
        }

        $cotIndicatorVersion->unpublish();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', 'Version moved back to draft. Historical observations keep using their pinned version.');
    }

    public function archive(CotIndicatorVersion $cotIndicatorVersion)
    {
        if ($cotIndicatorVersion->isArchived()) {
            return redirect()->back()->with('error', 'This version is already archived.');
        }

        $cotIndicatorVersion->archive();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.index')
            ->with('success', 'Version archived. Historical observations remain intact.');
    }

    public function storeIndicator(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'domain' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $existing = CotIndicator::where('version_id', $cotIndicatorVersion->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($existing) {
            return redirect()->back()->withErrors(['code' => 'An indicator with this code already exists in this version.']);
        }

        $maxOrder = (int) CotIndicator::where('version_id', $cotIndicatorVersion->id)->max('sort_order');

        CotIndicator::create([
            'version_id' => $cotIndicatorVersion->id,
            'code' => $validated['code'],
            'description' => $validated['description'],
            'domain' => $validated['domain'],
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
            'is_active' => ! empty($validated['is_active']),
        ]);

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', "Indicator {$validated['code']} added.");
    }

    /**
     * Add a PPST library standard as an indicator of the version. Pulls the
     * code, description and domain straight from the canonical standard and
     * links it via ppst_standard_id so the template stays traceable.
     */
    public function addStandardIndicator(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'ppst_standard_id' => ['required', 'integer'],
        ]);

        $standard = PpstStandard::findOrFail($validated['ppst_standard_id']);

        $existing = CotIndicator::where('version_id', $cotIndicatorVersion->id)
            ->where('code', $standard->indicator_code)
            ->exists();

        if ($existing) {
            return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
                ->with('error', "Indicator {$standard->indicator_code} is already in this version.");
        }

        $maxOrder = (int) CotIndicator::where('version_id', $cotIndicatorVersion->id)->max('sort_order');

        CotIndicator::create([
            'version_id' => $cotIndicatorVersion->id,
            'ppst_standard_id' => $standard->id,
            'code' => $standard->indicator_code,
            'description' => $standard->description,
            'domain' => $standard->domain,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', "PPST indicator {$standard->indicator_code} added to this version.");
    }

    public function updateIndicator(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'code' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'domain' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $indicator = CotIndicator::where('version_id', $cotIndicatorVersion->id)->findOrFail($validated['id']);

        $duplicate = CotIndicator::where('version_id', $cotIndicatorVersion->id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $indicator->id)
            ->exists();

        if ($duplicate) {
            return redirect()->back()->withErrors(['code' => 'An indicator with this code already exists in this version.']);
        }

        $indicator->update([
            'code' => $validated['code'],
            'description' => $validated['description'],
            'domain' => $validated['domain'],
            'is_active' => ! empty($validated['is_active']),
            'ppst_standard_id' => $request->input('ppst_standard_id') ?: $indicator->ppst_standard_id,
        ]);

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', "Indicator {$indicator->code} updated.");
    }

    public function destroyIndicator(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $indicator = CotIndicator::where('version_id', $cotIndicatorVersion->id)->findOrFail($validated['id']);

        $code = $indicator->code;
        $indicator->delete();

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', "Indicator {$code} removed.");
    }

    public function reorderIndicators(Request $request, CotIndicatorVersion $cotIndicatorVersion)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'reorder_orders' => ['required', 'array'],
            'reorder_orders.*.id' => ['required', 'integer'],
            'reorder_orders.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['reorder_orders'] as $item) {
            CotIndicator::where('version_id', $cotIndicatorVersion->id)
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', 'Indicator order updated.');
    }

    public function moveIndicator(Request $request, CotIndicatorVersion $cotIndicatorVersion, CotIndicator $cotIndicator)
    {
        if (! $cotIndicatorVersion->canEdit()) {
            return redirect()->back()->with('error', 'Published or archived versions are immutable.');
        }

        $validated = $request->validate([
            'direction' => ['required', 'string', 'in:up,down'],
        ]);

        $indicators = CotIndicator::where('version_id', $cotIndicatorVersion->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $ids = $indicators->keys()->all();
        $index = array_search($cotIndicator->id, $ids, true);

        if ($index === false) {
            abort(404);
        }

        $swapIndex = $validated['direction'] === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex < 0 || $swapIndex >= count($ids)) {
            return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
                ->with('error', 'Indicator is already at the edge of the list.');
        }

        $current = $indicators[$ids[$index]];
        $swap = $indicators[$ids[$swapIndex]];

        $tmpOrder = $current->sort_order;
        $current->update(['sort_order' => $swap->sort_order]);
        $swap->update(['sort_order' => $tmpOrder]);

        $this->normalizeSortOrders($cotIndicatorVersion);
        $this->cotIndicatorService->clearCache();

        return redirect()->route('admin.cot-indicators.edit', $cotIndicatorVersion)
            ->with('success', "Indicator {$current->code} moved {$validated['direction']}.");
    }

    private function normalizeSortOrders(CotIndicatorVersion $cotIndicatorVersion): void
    {
        $indicators = CotIndicator::where('version_id', $cotIndicatorVersion->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($indicators as $i => $indicator) {
            if ((int) $indicator->sort_order !== $i) {
                $indicator->update(['sort_order' => $i]);
            }
        }
    }

    private function getSchoolYearOptions(): array
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($i = -1; $i <= 3; $i++) {
            $start = $currentYear + $i;
            $end = $start + 1;
            $years["{$start}-{$end}"] = "{$start}-{$end}";
        }

        return $years;
    }

    private function getRateeRoleOptions(): array
    {
        return [
            CotIndicatorVersion::DEFAULT_RATEE_ROLE => 'Teacher',
            'school_head' => 'School Head',
        ];
    }

    /**
     * View data for the dependent Framework → Career Track → Ratee Position
     * selects and the auto-determined career stage display.
     */
    private function versionContextViewData(): array
    {
        $frameworks = $this->careerStageResolver->frameworks();

        $tracks = [];
        $positions = [];
        foreach ($frameworks as $framework => $frameworkLabel) {
            $tracks[$framework] = $this->careerStageResolver->tracksFor($framework);

            foreach ($tracks[$framework] as $track => $trackLabel) {
                $positions[$framework][$track] = $this->careerStageResolver->positionsFor($framework, $track);
            }
        }

        $stageLabels = config('career_stages.career_stage_labels', []);
        $instruments = config('career_stages.instruments', []);

        return compact('frameworks', 'tracks', 'positions', 'stageLabels', 'instruments');
    }

    /**
     * Resolve the version's identity + career stage on the server.
     *
     * When a `framework` is supplied (new Create/Edit Version form) the
     * career stage and ratee role are derived from framework + track +
     * position; the browser-provided `career_stage` is ignored. When no
     * framework is present (legacy requests) the old ratee_role/career_stage
     * payload is honoured for backward compatibility.
     *
     * @return array{ok: bool, error?: string, framework: ?string, career_track: ?string, ratee_position: ?string, instrument: ?string, ratee_role: ?string, career_stage: ?string}
     */
    private function resolveVersionContext(array $validated, ?CotIndicatorVersion $version = null): array
    {
        if (empty($validated['framework'])) {
            return [
                'ok' => true,
                'framework' => $version?->framework,
                'career_track' => $version?->career_track,
                'ratee_position' => $version?->ratee_position,
                'instrument' => $version?->instrument,
                'ratee_role' => $validated['ratee_role'] ?? $version?->ratee_role ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE,
                'career_stage' => $validated['career_stage'] ?? $version?->career_stage,
            ];
        }

        $framework = $validated['framework'];
        $track = $validated['career_track'] ?? null;
        $position = $validated['ratee_position'] ?? null;

        $careerStage = $this->careerStageResolver->resolve($framework, $track, $position);

        if ($careerStage === null) {
            return [
                'ok' => false,
                'framework' => $framework,
                'career_track' => $track,
                'ratee_position' => $position,
                'instrument' => null,
                'ratee_role' => null,
                'career_stage' => null,
                'error' => 'The selected framework, career track, and ratee position combination is invalid.',
            ];
        }

        return [
            'ok' => true,
            'framework' => $framework,
            'career_track' => $track,
            'ratee_position' => $position,
            'instrument' => $validated['instrument'] ?? $version?->instrument ?? CotIndicatorVersion::DEFAULT_INSTRUMENT,
            'ratee_role' => $this->careerStageResolver->rateeRoleFor($framework),
            'career_stage' => $careerStage,
        ];
    }

    /**
     * Whether a version already occupies the same context. A context is the
     * framework + career track + ratee position + instrument when present
     * (new flow), or the legacy ratee_role + career_stage pair for records
     * created before the context columns existed.
     */
    private function versionExistsFor(
        string $schoolYear,
        ?string $framework,
        ?string $careerTrack,
        ?string $rateePosition,
        ?string $instrument,
        ?string $rateeRole,
        ?string $careerStage,
        ?int $exceptId = null,
    ): bool {
        $query = CotIndicatorVersion::where('school_year', $schoolYear);

        if ($framework !== null && $careerTrack !== null && $rateePosition !== null) {
            $query->where('framework', $framework)
                ->where('career_track', $careerTrack)
                ->where('ratee_position', $rateePosition)
                ->where('instrument', $instrument);
        } else {
            $query->where('ratee_role', $rateeRole ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE);

            if ($careerStage === null) {
                $query->whereNull('career_stage');
            } else {
                $query->where('career_stage', $careerStage);
            }
        }

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
