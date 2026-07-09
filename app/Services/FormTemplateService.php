<?php

namespace App\Services;

use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Observation;
use Illuminate\Support\Collection;

class FormTemplateService
{
    private array $cachedTemplates = [];

    public function getActiveTemplate(string $schoolYear, ?string $observationType = null): ?FormTemplate
    {
        $cacheKey = $schoolYear . '|' . ($observationType ?? '*');

        if (isset($this->cachedTemplates[$cacheKey])) {
            return $this->cachedTemplates[$cacheKey];
        }

        $this->cachedTemplates[$cacheKey] = FormTemplate::active($schoolYear, $observationType)
            ->with(['sections.fields'])
            ->first();

        return $this->cachedTemplates[$cacheKey];
    }

    public function getTemplateForObservation(Observation $observation): ?FormTemplate
    {
        if ($observation->form_template_id) {
            return FormTemplate::with(['sections.fields'])
                ->find($observation->form_template_id);
        }

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
        return $this->getActiveTemplate($schoolYear, $observation->observation_type);
    }

    public function getSections(string $schoolYear, ?string $observationType = null): Collection
    {
        $template = $this->getActiveTemplate($schoolYear, $observationType);
        if (!$template) {
            return collect();
        }

        return $template->sections;
    }

    public function getFieldsForSection(string $schoolYear, string $sectionKey, ?string $observationType = null): Collection
    {
        $template = $this->getActiveTemplate($schoolYear, $observationType);
        if (!$template) {
            return collect();
        }

        $section = $template->sections->firstWhere('key', $sectionKey);
        if (!$section) {
            return collect();
        }

        return $section->fields;
    }

    public function getValidationRules(string $schoolYear, string $sectionKey, ?string $observationType = null): array
    {
        $fields = $this->getFieldsForSection($schoolYear, $sectionKey, $observationType);
        $rules = [];

        foreach ($fields as $field) {
            if (!$field->getIsInputField()) {
                continue;
            }

            $fieldRules = $field->getValidationRulesString();
            if (!empty($fieldRules)) {
                $rules[$field->key] = $fieldRules;
            }
        }

        return $rules;
    }

    public function parseFormData(string $schoolYear, string $sectionKey, array $input, ?string $observationType = null): array
    {
        $fields = $this->getFieldsForSection($schoolYear, $sectionKey, $observationType);

        $mappedData = [];
        $formResponses = [];

        foreach ($fields as $field) {
            if (!$field->getIsInputField()) {
                continue;
            }

            $key = $field->key;
            if (!array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];

            if ($field->type === 'file') {
                continue;
            }

            if ($field->column_map) {
                $mappedData[$field->column_map] = $value;
            } else {
                $formResponses[$key] = $value;
            }
        }

        if (!empty($formResponses)) {
            $mappedData['form_responses'] = $formResponses;
        }

        return $mappedData;
    }

    public function getAvailableTemplates(?string $observationType = null): Collection
    {
        $query = FormTemplate::withCount('sections');

        if ($observationType) {
            $query->where(function ($q) use ($observationType) {
                $q->where('observation_type', $observationType)
                  ->orWhereNull('observation_type');
            });
        }

        return $query->orderBy('school_year', 'desc')
            ->orderBy('observation_type')
            ->orderBy('version', 'desc')
            ->get();
    }

    public function getTypeLabel(?string $observationType): string
    {
        return match ($observationType) {
            'teacher_observation' => 'Teacher Observation',
            'school_head_observation' => 'School Head Observation',
            default => 'All Types',
        };
    }

    public function duplicateTemplate(FormTemplate $template, string $newSchoolYear): FormTemplate
    {
        $newTemplate = $template->replicate();
        $newTemplate->school_year = $newSchoolYear;
        $newTemplate->is_active = false;
        $newTemplate->version = 1;
        $newTemplate->push();

        foreach ($template->sections as $section) {
            $newSection = $section->replicate();
            $newSection->template_id = $newTemplate->id;
            $newSection->push();

            foreach ($section->fields as $field) {
                $newField = $field->replicate();
                $newField->section_id = $newSection->id;
                $newField->push();
            }
        }

        $newTemplate->load(['sections.fields']);

        return $newTemplate;
    }
}
