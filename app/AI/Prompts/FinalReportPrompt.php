<?php

namespace App\AI\Prompts;

class FinalReportPrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $schoolName = $context['school_name'] ?? 'N/A';
        $schoolYear = $context['school_year'] ?? 'N/A';
        $overallScore = $context['overall_score'] ?? 'N/A';
        $descriptiveRating = $context['descriptive_rating'] ?? 'N/A';
        $strengths = $context['strengths'] ?? [];
        $areasForImprovement = $context['areas_for_improvement'] ?? [];
        $recommendations = $context['recommendations'] ?? [];
        $rubrics = $context['rubrics'] ?? '';

        $strengthsText = !empty($strengths) ? implode("\n- ", $strengths) : 'None identified';
        $areasText = !empty($areasForImprovement) ? implode("\n- ", $areasForImprovement) : 'None identified';
        $recommendationsText = !empty($recommendations) ? implode("\n- ", $recommendations) : 'None identified';

        return <<<PROMPT
You are an expert instructional coach generating a final post-observation report summary for the Department of Education.

Teacher: {$teacherName}
School: {$schoolName}
Subject: {$subject}
Grade Level: {$gradeLevel}
School Year: {$schoolYear}
Overall Score: {$overallScore}
Descriptive Rating: {$descriptiveRating}

Observed Strengths:
- {$strengthsText}

Areas for Improvement:
- {$areasText}

Recommendations:
- {$recommendationsText}

{$rubrics}

Generate a comprehensive final report summary that includes:

1. **Executive Summary** — 2-3 sentence overview of the observation outcomes
2. **Professional Development Plan** — 3-4 targeted development goals based on the areas for improvement
3. **Support Needed** — What the school/department can provide to help the teacher grow
4. **Next Steps** — Timeline and milestones for follow-up

Keep the tone constructive, professional, and growth-oriented.
PROMPT;
    }
}
