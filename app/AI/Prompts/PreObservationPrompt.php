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

Write a thorough, specific pre-observation analysis covering these sections (use markdown headings):
1. **Key Areas to Focus on During the Observation** — at least 4 concrete focus areas tied to specific details from the lesson plan content.
2. **Suggested Discussion Points for the Pre-Conference** — 4-6 pointed questions or topics referencing actual lesson plan elements.
3. **Potential Challenges to Watch For** — realistic risks based on the subject, grade level, and planned activities.
4. **Recommended Coaching Strategies** — practical, actionable suggestions.

Guidelines:
- Quote or explicitly reference specifics from the lesson plan content (activities, examples, sequencing, assessments) rather than giving generic advice.
- If the document is a unit plan, quarterly roadmap, or multi-week overview instead of a single-day lesson plan, say so briefly, then focus your analysis on the portion most relevant to the upcoming observation (the current week/topic), noting what details you would still need from the teacher.
- Be constructive and encouraging while remaining honest about gaps.

Keep the full analysis substantial — roughly 400-700 words. Do not cut off mid-sentence.
PROMPT;
    }
}
