<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\CotRating;
use Illuminate\Support\Facades\Log;

class AISuggestionService
{
    protected GeminiService $gemini;

    protected AIFeedbackService $aiFeedback;

    public function __construct(GeminiService $gemini, AIFeedbackService $aiFeedback)
    {
        $this->gemini = $gemini;
        $this->aiFeedback = $aiFeedback;
    }

    public function generateObservationSuggestions(Observation $observation): ?string
    {
        if (!$this->gemini->isConfigured()) {
            return null;
        }

        $observation->loadMissing(['preObservationPlanning', 'preConference', 'observee']);

        $teacherName = $observation->observee?->name ?? 'Unknown';
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $obsType = $observation->observation_type ?? 'N/A';
        $planning = $observation->preObservationPlanning;
        $preConference = $observation->preConference;

        $suggestedFocus = $planning?->suggested_focus ?? 'Not specified';
        $finalizedFocus = $preConference?->finalized_focus ?? 'Not specified';
        $discussionNotes = $preConference?->discussion_notes ?? 'Not specified';
        $aiInsights = $planning?->ai_insights ?? null;
        $lessonPlanFile = $planning?->lesson_plan_file;

        $aiInsightsText = '';
        if ($aiInsights) {
            $aiInsightsText = is_array($aiInsights)
                ? (json_encode($aiInsights) ?: '')
                : $aiInsights;
        }

        $prompt = <<<PROMPT
You are an expert classroom observation coach. The supervisor is about to conduct a classroom observation. Provide targeted suggestions based on the pre-conference data below.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Observation Type: {$obsType}

Pre-Conference Finalized Focus: {$finalizedFocus}
Suggested Focus: {$suggestedFocus}
Discussion Notes: {$discussionNotes}
AI Pre-Observation Insights: {$aiInsightsText}

Provide concise, actionable suggestions for the supervisor:

1. **Key Indicators to Watch** — What specific teaching behaviors or practices should the supervisor focus on during this observation based on the finalized focus.
2. **Look-fors** — Specific observable actions, student engagement signs, and classroom practices to note.
3. **Guiding Questions** — 2-3 questions the supervisor can reflect on during the observation.
4. **Coaching Opportunities** — Potential areas where constructive feedback could be most valuable.

Keep it brief (3-5 short paragraphs). Be specific and actionable.
PROMPT;

        $result = $this->gemini->generate($prompt, [
            'temperature' => 0.4,
            'max_output_tokens' => 800,
        ]);

        return $result ?: null;
    }

    public function buildSummary(CotRating $cotRating): array
    {
        $percentage = $cotRating->percentage();
        $domain = $cotRating->domain;
        $indicator = $cotRating->indicator;
        $rating = $cotRating->rating;

        $summary = [
            'analysis' => $this->buildAnalysis($percentage, $domain),
            'recommendations' => $this->buildRecommendations($percentage, $domain),
            'strengths' => $this->buildStrengths($percentage, $domain),
            'areas_for_improvement' => $this->buildAreasForImprovement($percentage, $domain),
        ];

        $aiFeedback = $cotRating->aiFeedback;
        if ($aiFeedback && $aiFeedback->confidence_score >= 0.85) {
            if (!empty($aiFeedback->analysis)) {
                $summary['analysis'] = $aiFeedback->analysis;
            }
            if (!empty($aiFeedback->recommendations)) {
                $summary['recommendations'] = $aiFeedback->recommendations;
            }
            if (!empty($aiFeedback->strengths)) {
                $summary['strengths'] = $aiFeedback->strengths;
            }
            if (!empty($aiFeedback->areas_for_improvement)) {
                $summary['areas_for_improvement'] = $aiFeedback->areas_for_improvement;
            }
        }

        return $summary;
    }

    public function compileOverallSummary(Observation $observation): array
    {
        $observation->loadMissing(['cotRatings.aiFeedback']);

        $allStrengths = [];
        $allAreas = [];
        $allRecommendations = [];

        foreach ($observation->cotRatings as $rating) {
            $summary = $this->buildSummary($rating);
            $allStrengths = array_merge($allStrengths, $summary['strengths']);
            $allAreas = array_merge($allAreas, $summary['areas_for_improvement']);
            $allRecommendations = array_merge($allRecommendations, $summary['recommendations']);
        }

        $allStrengths = array_unique($allStrengths);
        $allAreas = array_unique($allAreas);
        $allRecommendations = array_unique($allRecommendations);

        $aiInsights = $observation->preObservationPlanning?->ai_insights;
        $aiComparison = $observation->postConference?->ai_comparison;

        return [
            'strengths' => $allStrengths,
            'areas_for_improvement' => $allAreas,
            'recommendations' => $allRecommendations,
            'ai_insights' => $aiInsights,
            'ai_comparison' => $aiComparison,
        ];
    }

    protected function buildAnalysis(float $percentage, string $domain): string
    {
        return match (true) {
            $percentage >= 90 => "Excellent performance in {$domain}. The teacher demonstrates mastery with consistent application of best practices.",
            $percentage >= 80 => "Proficient performance in {$domain}. The teacher shows good understanding with minor areas for refinement.",
            $percentage >= 70 => "Developing performance in {$domain}. The teacher is approaching proficiency with targeted support needed.",
            $percentage >= 60 => "Beginning performance in {$domain}. Structured coaching and professional development recommended.",
            default => "Performance in {$domain} requires immediate intervention. Intensive support and monitoring necessary.",
        };
    }

    protected function buildRecommendations(float $percentage, string $domain): array
    {
        return match (true) {
            $percentage < 70 => [
                "Schedule follow-up observation for {$domain} within 2 weeks",
                "Provide targeted professional development resources for {$domain}",
                "Arrange peer mentoring with high-performing teacher in {$domain}",
            ],
            $percentage < 80 => [
                "Share best practices and exemplar materials for {$domain}",
                "Encourage self-reflection and goal setting for {$domain}",
            ],
            default => [
                "Document exemplary practices in {$domain} for knowledge sharing",
                "Consider peer coaching opportunities in {$domain}",
            ],
        };
    }

    protected function buildStrengths(float $percentage, string $domain): array
    {
        $strengths = [];
        if ($percentage >= 80) {
            $strengths[] = "Strong command of {$domain}";
        }
        if ($percentage >= 90) {
            $strengths[] = "Consistently exceeds expectations";
            $strengths[] = "Serves as model for peers";
        }
        return $strengths;
    }

    protected function buildAreasForImprovement(float $percentage, string $domain): array
    {
        $areas = [];
        if ($percentage < 70) {
            $areas[] = "Fundamental skills in {$domain}";
        }
        if ($percentage < 80) {
            $areas[] = "Consistency in applying {$domain} techniques";
        }
        if ($percentage < 60) {
            $areas[] = "Core competencies requiring intensive support";
        }
        return $areas;
    }
}
