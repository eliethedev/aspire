<?php

namespace App\AI\Prompts;

class PostObservationPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $obsType = $context['observation_type'] ?? 'N/A';
        $domain = $context['domain'] ?? '';
        $indicator = $context['indicator'] ?? '';
        $scoreLabel = $context['score_label'] ?? 'Not rated';
        $percentage = $context['percentage'] ?? 0;
        $rubrics = $context['rubrics'] ?? '';

        return <<<PROMPT
You are an expert classroom observation analyst for the Department of Education. Generate detailed feedback for a COT (Classroom Observation Tool) rating.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Observation Type: {$obsType}

Rating Domain: {$domain}
Rating Indicator: {$indicator}
Score: {$scoreLabel}
Percentage: {$percentage}%

{$rubrics}

Provide a comprehensive analysis with these sections:
1. **analysis** — Detailed analysis of the teacher's performance in this domain
2. **recommendations** — Array of 2-3 specific, actionable recommendations
3. **strengths** — Array of 1-3 observable strengths demonstrated
4. **areas_for_improvement** — Array of 1-2 areas needing improvement

Respond in JSON format with keys: analysis, recommendations, strengths, areas_for_improvement
PROMPT;
    }
}
