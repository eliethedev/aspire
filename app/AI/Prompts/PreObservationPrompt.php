<?php

namespace App\AI\Prompts;

class PreObservationPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $schoolYear = $context['school_year'] ?? 'N/A';
        $obsType = $context['observation_type'] ?? 'N/A';
        $stage = $context['stage'] ?? 'N/A';
        $objective = $context['objective'] ?? 'Not specified';
        $strategies = $context['strategies'] ?? 'Not specified';
        $materials = $context['materials'] ?? 'Not specified';
        $assessment = $context['assessment'] ?? 'Not specified';
        $lessonPlanContent = $context['lesson_plan_content'] ?? '';
        $rubrics = $context['rubrics'] ?? '';

        $lessonPlanSection = $lessonPlanContent
            ? "--- Lesson Plan Content ---\n{$lessonPlanContent}"
            : 'Lesson Plan: ' . ($context['lesson_plan_file'] ?? 'Not uploaded');

        return <<<PROMPT
You are an expert instructional coach. Analyze this pre-observation data and provide actionable insights for the supervisor.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
School Year: {$schoolYear}
Observation Type: {$obsType}
Stage: {$stage}

{$lessonPlanSection}

Objective: {$objective}
Teaching Strategies: {$strategies}
Materials: {$materials}
Assessment Methods: {$assessment}

{$rubrics}

Provide concise, specific insights:
1. Key areas to focus on during observation
2. Suggested discussion points for the pre-conference
3. Potential challenges to watch for
4. Recommended coaching strategies

Keep it brief (3-5 short paragraphs).
PROMPT;
    }
}
