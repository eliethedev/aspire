<?php

namespace App\AI\Services;

use App\Models\Observation;
use App\Models\Teacher;
use App\Services\CareerProgressionService;

/**
 * Compiles a personalised teacher profile context array for AI prompts.
 *
 * Pulls career stage, experience, subjects, grade level, and optional
 * TeacherProfile details (strand, advisory class, certifications) into
 * a single associative array that prompt builders can consume.
 */
class TeacherContextBuilder
{
    public function __construct(
        protected CareerProgressionService $careerProgression,
    ) {}

    /**
     * Build teacher context from an Observation (resolves the observee Teacher).
     */
    public function fromObservation(Observation $observation): array
    {
        $observation->loadMissing('observee.user', 'observee.subjects', 'observee.user.teacherProfile');

        $teacher = $observation->observee;

        if (! $teacher instanceof Teacher) {
            return $this->empty();
        }

        return $this->fromTeacher($teacher);
    }

    /**
     * Build teacher context directly from a Teacher model.
     */
    public function fromTeacher(Teacher $teacher): array
    {
        $teacher->loadMissing('user', 'subjects', 'user.teacherProfile');

        $careerContext = $this->careerProgression->contextFor($teacher);
        $profile = $teacher->user?->teacherProfile;

        return [
            'name' => $teacher->user?->name ?? 'Unknown',
            'position' => $careerContext['position'] ?? $teacher->position ?? 'Teacher',
            'career_stage' => $careerContext['career_stage'] ?? $teacher->career_stage,
            'career_stage_label' => $careerContext['career_stage_label'] ?? $teacher->careerStage()?->label() ?? 'N/A',
            'years_of_service' => $teacher->years_of_service,
            'department' => $teacher->department ?? $profile?->department ?? null,
            'subjects' => $teacher->subjects_label ?? $teacher->subject ?? null,
            'grade_level' => $teacher->grade_level ?? $profile?->grade_level_label ?? null,
            'strand_specialization' => $profile?->strand_specialization ?? null,
            'advisory_class' => $profile?->has_advisory_class
                ? ($profile->advisory_section ?? 'Yes')
                : null,
            'teacher_load' => $profile?->teacher_load ?? null,
            'certifications_training' => $profile?->certification_training ?? null,
        ];
    }

    /**
     * Format the context array into a human-readable block for prompt injection.
     */
    public function formatForPrompt(array $context): string
    {
        $context = array_filter($context, fn ($v) => $v !== null && $v !== '' && $v !== 0);

        if ($context === []) {
            return '';
        }

        $lines = [];
        foreach ($context as $key => $value) {
            $label = str_replace('_', ' ', ucfirst($key));
            $lines[] = "{$label}: {$value}";
        }

        return implode("\n", $lines);
    }

    private function empty(): array
    {
        return [
            'name' => 'Unknown',
            'position' => null,
            'career_stage' => null,
            'career_stage_label' => null,
            'years_of_service' => null,
            'department' => null,
            'subjects' => null,
            'grade_level' => null,
            'strand_specialization' => null,
            'advisory_class' => null,
            'teacher_load' => null,
            'certifications_training' => null,
        ];
    }
}
