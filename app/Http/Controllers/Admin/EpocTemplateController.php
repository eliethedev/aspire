<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EpocTemplate;
use Illuminate\Http\Request;

class EpocTemplateController extends Controller
{
    public function index()
    {
        $templates = EpocTemplate::withCount('indicators')->latest()->get();

        return view('admin.epoc-templates.index', compact('templates'));
    }

    public function create()
    {
        $schoolYears = $this->getSchoolYearOptions();

        return view('admin.epoc-templates.create', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_year' => 'required|string|max:20',
            'domains' => 'nullable|array',
            'domains.*.name' => 'required_with:domains|string|max:255',
            'domains.*.indicators' => 'nullable|string',
        ]);

        $template = EpocTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'school_year' => $validated['school_year'],
        ]);

        $this->syncIndicators($template, $validated['domains'] ?? []);

        return redirect()->route('admin.epoc-templates.edit', $template)
            ->with('success', 'EPOC template created successfully.');
    }

    public function edit(EpocTemplate $epocTemplate)
    {
        $epocTemplate->load('indicators');
        $schoolYears = $this->getSchoolYearOptions();
        $grouped = $epocTemplate->indicators->groupBy('domain');

        return view('admin.epoc-templates.edit', compact('epocTemplate', 'schoolYears', 'grouped'));
    }

    public function update(Request $request, EpocTemplate $epocTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_year' => 'required|string|max:20',
            'domains' => 'nullable|array',
            'domains.*.name' => 'required_with:domains|string|max:255',
            'domains.*.indicators' => 'nullable|string',
        ]);

        $epocTemplate->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'school_year' => $validated['school_year'],
        ]);

        $this->syncIndicators($epocTemplate, $validated['domains'] ?? []);

        return redirect()->route('admin.epoc-templates.edit', $epocTemplate)
            ->with('success', 'EPOC template updated successfully.');
    }

    public function destroy(EpocTemplate $epocTemplate)
    {
        $epocTemplate->delete();

        return redirect()->route('admin.epoc-templates.index')
            ->with('success', 'EPOC template deleted successfully.');
    }

    public function activate(EpocTemplate $epocTemplate)
    {
        $epocTemplate->activate();

        return redirect()->route('admin.epoc-templates.index')
            ->with('success', "EPOC template '{$epocTemplate->name}' is now active for {$epocTemplate->school_year}.");
    }

    public function duplicate(Request $request, EpocTemplate $epocTemplate)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
        ]);

        $copy = $epocTemplate->replicate(['is_active', 'version']);
        $copy->school_year = $validated['school_year'];
        $copy->is_active = false;
        $copy->version = $epocTemplate->version + 1;
        $copy->save();

        foreach ($epocTemplate->indicators()->orderBy('order')->get() as $indicator) {
            $row = $indicator->replicate(['template_id']);
            $row->template_id = $copy->id;
            $row->save();
        }

        return redirect()->route('admin.epoc-templates.edit', $copy)
            ->with('success', "Template duplicated to {$validated['school_year']} successfully.");
    }

    /**
     * Replace all indicator rows from the submitted domain blocks.
     * Safe: EPOC ratings store domain/indicator text, never indicator IDs.
     */
    private function syncIndicators(EpocTemplate $template, array $domains): void
    {
        $template->indicators()->delete();

        $order = 0;
        foreach ($domains as $domainData) {
            $name = trim($domainData['name'] ?? '');
            if ($name === '') continue;

            foreach ($this->parseLines($domainData['indicators'] ?? '') as $line) {
                $template->indicators()->create([
                    'domain' => $name,
                    'indicator' => $line,
                    'order' => $order++,
                ]);
            }
        }
    }

    private function parseLines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
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
}
