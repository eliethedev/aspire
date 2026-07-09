<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Services\FormTemplateService;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    protected FormTemplateService $formTemplateService;

    public function __construct(FormTemplateService $formTemplateService)
    {
        $this->formTemplateService = $formTemplateService;
    }

    public function index(Request $request)
    {
        $observationType = $request->input('observation_type');
        $templates = $this->formTemplateService->getAvailableTemplates($observationType);
        return view('admin.form-templates.index', compact('templates', 'observationType'));
    }

    public function create()
    {
        $schoolYears = $this->getSchoolYearOptions();
        $observationTypes = $this->getObservationTypeOptions();
        $sectionKeys = [
            'pre_observation_planning' => 'Pre-Observation Planning',
            'pre_conference' => 'Pre-Conference',
            'observation' => 'Observation (COT)',
            'post_conference' => 'Post-Conference',
        ];
        $fieldTypes = [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'select' => 'Dropdown Select',
            'radio' => 'Radio Group',
            'checkbox' => 'Checkbox Group',
            'file' => 'File Upload',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'number' => 'Number Input',
            'heading' => 'Section Heading',
            'paragraph' => 'Paragraph Text',
            'hr' => 'Horizontal Rule',
        ];
        return view('admin.form-templates.create', compact('schoolYears', 'observationTypes', 'sectionKeys', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_year' => 'required|string|max:20',
            'observation_type' => 'nullable|in:teacher_observation,school_head_observation',
        ]);

        $template = FormTemplate::create($validated);

        $sections = $request->input('sections', []);
        foreach ($sections as $order => $sectionData) {
            if (empty($sectionData['key']) || empty($sectionData['label'])) continue;

            $section = $template->sections()->create([
                'key' => $sectionData['key'],
                'label' => $sectionData['label'],
                'order' => $order,
            ]);

            $fields = $sectionData['fields'] ?? [];
            foreach ($fields as $fieldOrder => $fieldData) {
                if (empty($fieldData['key']) || empty($fieldData['label'])) continue;

                $section->fields()->create([
                    'key' => $fieldData['key'],
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'] ?? 'text',
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'validation_rules' => !empty($fieldData['validation_rules']) ? explode(',', $fieldData['validation_rules']) : null,
                    'options' => !empty($fieldData['options']) ? $this->parseOptions($fieldData['options']) : null,
                    'order' => $fieldOrder,
                    'required' => !empty($fieldData['required']),
                    'column_map' => $fieldData['column_map'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.form-templates.edit', $template)
            ->with('success', 'Form template created successfully.');
    }

    public function edit(FormTemplate $formTemplate)
    {
        $formTemplate->load(['sections.fields']);
        $schoolYears = $this->getSchoolYearOptions();
        $observationTypes = $this->getObservationTypeOptions();
        $sectionKeys = [
            'pre_observation_planning' => 'Pre-Observation Planning',
            'pre_conference' => 'Pre-Conference',
            'observation' => 'Observation (COT)',
            'post_conference' => 'Post-Conference',
        ];
        $fieldTypes = [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'select' => 'Dropdown Select',
            'radio' => 'Radio Group',
            'checkbox' => 'Checkbox Group',
            'file' => 'File Upload',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'number' => 'Number Input',
            'heading' => 'Section Heading',
            'paragraph' => 'Paragraph Text',
            'hr' => 'Horizontal Rule',
        ];
        $columnMapOptions = $this->getColumnMapOptions();

        return view('admin.form-templates.edit', compact(
            'formTemplate', 'schoolYears', 'observationTypes', 'sectionKeys', 'fieldTypes', 'columnMapOptions'
        ));
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_year' => 'required|string|max:20',
            'observation_type' => 'nullable|in:teacher_observation,school_head_observation',
        ]);

        $formTemplate->update($validated);

        // Get existing section IDs to track deletions
        $existingSectionIds = $formTemplate->sections()->pluck('id')->toArray();
        $submittedSectionIds = [];

        $sections = $request->input('sections', []);
        foreach ($sections as $order => $sectionData) {
            if (empty($sectionData['key']) || empty($sectionData['label'])) continue;

            if (!empty($sectionData['id']) && in_array($sectionData['id'], $existingSectionIds)) {
                $section = FormSection::find($sectionData['id']);
                if ($section) {
                    $section->update([
                        'key' => $sectionData['key'],
                        'label' => $sectionData['label'],
                        'order' => $order,
                    ]);
                    $submittedSectionIds[] = $section->id;
                }
            } else {
                $section = $formTemplate->sections()->create([
                    'key' => $sectionData['key'],
                    'label' => $sectionData['label'],
                    'order' => $order,
                ]);
                if ($section) $submittedSectionIds[] = $section->id;
            }

            if (!$section) continue;

            // Handle fields within this section
            $existingFieldIds = $section->fields()->pluck('id')->toArray();
            $submittedFieldIds = [];

            $fields = $sectionData['fields'] ?? [];
            foreach ($fields as $fieldOrder => $fieldData) {
                if (empty($fieldData['key']) || empty($fieldData['label'])) continue;

                if (!empty($fieldData['id']) && in_array($fieldData['id'], $existingFieldIds)) {
                    $field = FormField::find($fieldData['id']);
                    if ($field) {
                        $field->update([
                            'key' => $fieldData['key'],
                            'label' => $fieldData['label'],
                            'type' => $fieldData['type'] ?? 'text',
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'help_text' => $fieldData['help_text'] ?? null,
                            'default_value' => $fieldData['default_value'] ?? null,
                            'validation_rules' => !empty($fieldData['validation_rules']) ? explode(',', $fieldData['validation_rules']) : null,
                            'options' => !empty($fieldData['options']) ? $this->parseOptions($fieldData['options']) : null,
                            'order' => $fieldOrder,
                            'required' => !empty($fieldData['required']),
                            'column_map' => $fieldData['column_map'] ?? null,
                        ]);
                        $submittedFieldIds[] = $field->id;
                    }
                } else {
                    $field = $section->fields()->create([
                        'key' => $fieldData['key'],
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'] ?? 'text',
                        'placeholder' => $fieldData['placeholder'] ?? null,
                        'help_text' => $fieldData['help_text'] ?? null,
                        'default_value' => $fieldData['default_value'] ?? null,
                        'validation_rules' => !empty($fieldData['validation_rules']) ? explode(',', $fieldData['validation_rules']) : null,
                        'options' => !empty($fieldData['options']) ? $this->parseOptions($fieldData['options']) : null,
                        'order' => $fieldOrder,
                        'required' => !empty($fieldData['required']),
                        'column_map' => $fieldData['column_map'] ?? null,
                    ]);
                    if ($field) $submittedFieldIds[] = $field->id;
                }
            }

            // Delete removed fields
            $fieldsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
            if (!empty($fieldsToDelete)) {
                FormField::whereIn('id', $fieldsToDelete)->delete();
            }
        }

        // Delete removed sections
        $sectionsToDelete = array_diff($existingSectionIds, $submittedSectionIds);
        if (!empty($sectionsToDelete)) {
            FormSection::whereIn('id', $sectionsToDelete)->delete();
        }

        return redirect()->route('admin.form-templates.edit', $formTemplate)
            ->with('success', 'Form template updated successfully.');
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();
        return redirect()->route('admin.form-templates.index')
            ->with('success', 'Form template deleted successfully.');
    }

    public function activate(FormTemplate $formTemplate)
    {
        $formTemplate->activate();
        return redirect()->route('admin.form-templates.index')
            ->with('success', "Form template '{$formTemplate->name}' is now active for {$formTemplate->school_year}.");
    }

    public function duplicate(Request $request, FormTemplate $formTemplate)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20',
        ]);

        $newTemplate = $this->formTemplateService->duplicateTemplate($formTemplate, $validated['school_year']);

        return redirect()->route('admin.form-templates.edit', $newTemplate)
            ->with('success', "Template duplicated to {$validated['school_year']} successfully.");
    }

    private function parseOptions(string $optionsText): array
    {
        $lines = explode("\n", trim($optionsText));
        $options = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (str_contains($line, '|')) {
                $parts = explode('|', $line, 2);
                $options[] = [
                    'value' => trim($parts[0]),
                    'label' => trim($parts[1]),
                ];
            } else {
                $options[] = [
                    'value' => $line,
                    'label' => $line,
                ];
            }
        }

        return $options;
    }

    private function getObservationTypeOptions(): array
    {
        return [
            '' => 'All Observation Types',
            'teacher_observation' => 'Teacher Observation',
            'school_head_observation' => 'School Head Observation',
        ];
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

    private function getColumnMapOptions(): array
    {
        return [
            'pre_conference' => [
                'discussion_notes' => 'Discussion Notes',
                'finalized_focus' => 'Finalized Focus',
                'conference_date' => 'Conference Date',
                'teacher_reflection' => 'Teacher Reflection',
                'lesson_plan_review' => 'Lesson Plan Review',
                'instructional_materials' => 'Instructional Materials',
            ],
            'post_conference' => [
                'ai_comparison' => 'AI Comparison',
                'feedback' => 'Feedback',
                'conference_date' => 'Conference Date',
                'star_notes' => 'STAR Notes',
                'areas_for_improvement' => 'Areas for Improvement',
                'challenges_facing_teacher' => 'Challenges',
                'ideas_for_addressing_challenges' => 'Ideas/Solutions',
                'prioritized_next_steps' => 'Prioritized Next Steps',
                'teacher_reflection' => 'Teacher Reflection',
                'supervisor_notes' => 'Supervisor Notes',
            ],
            'pre_observation_planning' => [
                'lesson_plan_file' => 'Lesson Plan File',
                'supervisor_notes' => 'Supervisor Notes',
                'observation_tool' => 'Observation Tool',
            ],
        ];
    }
}
