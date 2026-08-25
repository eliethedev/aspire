<?php

namespace App\AI\Prompts;

class ObservationSuggestionsPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $obsType = $context['observation_type'] ?? 'N/A';
        $finalizedFocus = $context['finalized_focus'] ?? 'Not specified';
        $suggestedFocus = $context['suggested_focus'] ?? 'Not specified';
        $discussionNotes = $context['discussion_notes'] ?? 'Not specified';
        $aiInsights = $context['ai_insights'] ?? '';
        $lessonPlanContent = $context['lesson_plan_content'] ?? '';
        $rubrics = $context['rubrics'] ?? '';

        $lessonPlanSection = $lessonPlanContent
            ? "--- Lesson Plan Content ---\n{$lessonPlanContent}"
            : '';

        return <<<PROMPT
You are an expert classroom observation coach. The supervisor is about to conduct a classroom observation. Provide targeted suggestions based on the pre-conference data below.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Observation Type: {$obsType}

Pre-Conference Finalized Focus: {$finalizedFocus}
Suggested Focus: {$suggestedFocus}
Discussion Notes: {$discussionNotes}
AI Pre-Observation Insights: {$aiInsights}

{$lessonPlanSection}

{$rubrics}

Provide detailed, actionable suggestions for the supervisor:

1. **Key Indicators to Watch** — What specific teaching behaviors or practices should the supervisor focus on during this observation based on the finalized focus.<br>
2. **Look-fors** — Specific observable actions, student engagement signs, and classroom practices to note.<br>
3. **Guiding Questions** — 3-4 questions the supervisor can reflect on during the observation.<br>
4. **Coaching Opportunities** — Potential areas where constructive feedback could be most valuable.<br>

Be specific and reference actual lesson plan details wherever possible. Roughly 300-500 words; do not cut off mid-sentence.
PROMPT;
    }
}
