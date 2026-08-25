<?php

namespace App\AI\Prompts;

class OverallRecommendationPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $ratingsSummary = $context['ratings_summary'] ?? 'No ratings recorded.';
        $indicatorContext = $context['indicator_context'] ?? '';
        $ratingScale = $context['rating_scale'] ?? '';
        $developmentNeeds = $context['development_needs'] ?? '';

        return <<<PROMPT
You are an expert instructional coach supporting a DepEd (Philippines) school supervisor.

GOAL: Synthesize the observation evidence and ratings below into prioritized, actionable development recommendations for the teacher.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}

OBSERVATION RATINGS SUMMARY:
{$ratingsSummary}

{$indicatorContext}

{$ratingScale}

Identified development needs: {$developmentNeeds}

TASK:
1. Synthesize the evidence across all rated indicators.
2. Provide actionable recommendations aligned to PPST/COT indicators.
3. Prioritize the most important areas first (highest impact on teaching and learning).

STRICT RULE: Do NOT assign or recommend a final rating, promotion, or disciplinary action. AI output is advisory only — the supervisor makes all final decisions.

Respond ONLY with valid JSON in exactly this structure:
{
  "overview": "2-3 sentence synthesis of overall performance",
  "priority_areas": [{"area": "...", "why": "...", "indicator_codes": ["..."], "priority": 1}],
  "recommendations": [{"recommendation": "...", "indicator_codes": ["..."]}],
  "follow_up": ["Suggested follow-up actions for the supervisor"]
}
No markdown, no commentary outside the JSON.
PROMPT;
    }
}
