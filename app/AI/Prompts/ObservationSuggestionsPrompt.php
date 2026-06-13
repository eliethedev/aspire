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

Provide concise, actionable suggestions for the supervisor:

1. **Key Indicators to Watch** — What specific teaching behaviors or practices should the supervisor focus on during this observation based on the finalized focus.<br>
2. **Look-fors** — Specific observable actions, student engagement signs, and classroom practices to note.<br>
3. **Guiding Questions** — 2-3 questions the supervisor can reflect on during the observation.<br>
4. **Coaching Opportunities** — Potential areas where constructive feedback could be most valuable.<br>

Keep it brief (3-5 short paragraphs). Be specific and actionable.
PROMPT;
    }
}
