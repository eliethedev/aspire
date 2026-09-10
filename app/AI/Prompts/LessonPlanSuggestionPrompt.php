<?php

namespace App\AI\Prompts;

class LessonPlanSuggestionPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $objective = $context['objective'] ?? 'Not specified';
        $strategies = $context['strategies'] ?? 'Not specified';
        $materials = $context['materials'] ?? 'Not specified';
        $assessment = $context['assessment'] ?? 'Not specified';
        $lessonPlanContent = $context['lesson_plan_content'] ?? '';
        $rubrics = $context['rubrics'] ?? '';
        $teacherContext = $context['teacher_context'] ?? '';
        $modeLabel = $context['mode_label'] ?? '';
        $modeDirectives = $context['mode_directives'] ?? '';

        $lessonPlanSection = $lessonPlanContent
            ? "--- Lesson Plan Content ---\n{$lessonPlanContent}"
            : "Lesson Plan: not uploaded. Base suggestions on the planning details below.";

        $teacherProfileSection = $teacherContext
            ? "--- Teacher Profile ---\n{$teacherContext}"
            : '';

        $modeSection = ($modeLabel !== '' || $modeDirectives !== '')
            ? 'Generation mode: '.($modeLabel !== '' ? $modeLabel : 'specialized').".\n{$modeDirectives}\n"
            : '';

        return <<<PROMPT
You are an expert instructional coach supporting a DepEd (Philippines) school supervisor.

GOAL: Review the lesson plan below and provide improvement suggestions for the supervisor to discuss with the teacher.

{$teacherProfileSection}
Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Objective: {$objective}
Teaching Strategies: {$strategies}
Materials: {$materials}
Assessment Methods: {$assessment}

 {$lessonPlanSection}

 {$rubrics}

{$modeSection}TASK:
1. Identify weaknesses or gaps in the lesson plan (objectives, strategies, activities, differentiation, assessment alignment).
2. Suggest practical, specific improvements the teacher can implement.
3. Align every suggestion with the applicable PPST/COT indicators listed above (cite indicator codes).

Respond ONLY with valid JSON in exactly this structure:
{
  "strengths": ["..."],
  "gaps": [{"area": "...", "detail": "...", "indicator_codes": ["..."]}],
  "suggestions": [{"suggestion": "...", "indicator_codes": ["..."], "priority": "high|medium|low"}],
  "summary": "One short paragraph overall assessment"
}
No markdown, no commentary outside the JSON.
PROMPT;
    }
}
