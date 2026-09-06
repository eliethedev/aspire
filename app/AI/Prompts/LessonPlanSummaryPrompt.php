<?php

namespace App\AI\Prompts;

class LessonPlanSummaryPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $lessonPlanContent = $context['lesson_plan_content'] ?? '';
        $teacherContext = $context['teacher_context'] ?? '';

        $teacherProfileSection = $teacherContext
            ? "--- Teacher Profile ---\n{$teacherContext}"
            : '';

        return <<<PROMPT
You are an assistant to a DepEd (Philippines) school supervisor.

GOAL: Summarize ONLY the instructionally relevant content of the lesson plan below. Be concise; avoid unnecessary text, boilerplate, or administrative details.

{$teacherProfileSection}
Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}

--- Lesson Plan Content ---
{$lessonPlanContent}

TASK: Preserve and organize the important information:
- learning objectives
- teaching strategies and activities
- materials
- assessment methods

Respond ONLY with valid JSON in exactly this structure:
{
  "objectives": ["..."],
  "strategies_activities": ["..."],
  "materials": ["..."],
  "assessment": ["..."],
  "summary": "2-3 sentence overview of the lesson flow"
}
No markdown, no commentary outside the JSON.
PROMPT;
    }
}
