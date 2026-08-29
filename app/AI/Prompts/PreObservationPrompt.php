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
Analyze this pre-observation data and write a brief coaching brief for the supervisor. No preamble, no reasoning — go straight to the output.

Teacher: {$teacherName}
Subject: {$subject} | Grade: {$gradeLevel} | SY: {$schoolYear}
Observation Type: {$obsType}

{$lessonPlanSection}

Objective: {$objective}
Strategies: {$strategies}
Materials: {$materials}
Assessment: {$assessment}

{$rubrics}

Write exactly these 4 sections using markdown. Keep each to 2-3 short sentences:

## Lesson Focus
What the lesson aims to achieve, based on the plan.

## Key Things to Watch
3 specific, concrete things the supervisor should look for during the observation.

## Pre-Conference Talking Points
2-3 short questions referencing details from the lesson plan.

## Potential Challenges
2 brief risks or areas the teacher may need support with.

Rules:
- Reference actual details from the lesson plan — never give generic advice.
- Be direct. No filler. No "Here is" or "Based on" introductions.
- Max 200 words total. Do not explain your reasoning.
PROMPT;
    }
}
