<?php

namespace App\AI\Services;

use App\AI\Prompts\ObservationSuggestionsPrompt;
use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Support\Facades\Storage;

class ObservationGuidanceService extends AIService
{
    protected string $stage = 'observation_guidance';

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected DocumentExtractorService $documentExtractor
    ) {
        parent::__construct($provider, $rubrics);
    }

    public function generateSuggestions(Observation $observation): ?string
    {
        $observation->loadMissing(['preObservationPlanning', 'preConference', 'observee.user']);

        $teacherName = $observation->observee?->user?->name ?? 'Unknown';
        $planning = $observation->preObservationPlanning;
        $preConference = $observation->preConference;

        $lessonPlanContent = '';
        $lessonPlanFile = $planning?->lesson_plan_file;
        if ($lessonPlanFile && Storage::disk('public')->exists($lessonPlanFile)) {
            $fullPath = Storage::disk('public')->path($lessonPlanFile);
            $lessonPlanContent = $this->documentExtractor->extractText($fullPath);
        }

        if ($this->isAvailable()) {
            $aiInsights = $planning?->ai_insights ?? null;
            $aiInsightsText = '';
            if ($aiInsights) {
                $aiInsightsText = is_array($aiInsights)
                    ? (json_encode($aiInsights) ?: '')
                    : $aiInsights;
            }

            $rubricContext = $this->rubrics->getSystemContext();

            $prompt = ObservationSuggestionsPrompt::build([
                'teacher_name' => $teacherName,
                'subject' => $observation->subject ?? 'N/A',
                'grade_level' => $observation->grade_level ?? 'N/A',
                'observation_type' => $observation->observation_type ?? 'N/A',
                'finalized_focus' => $preConference?->finalized_focus ?? 'Not specified',
                'suggested_focus' => $planning?->suggested_focus ?? 'Not specified',
                'discussion_notes' => $preConference?->discussion_notes ?? 'Not specified',
                'ai_insights' => $aiInsightsText,
                'lesson_plan_content' => $lessonPlanContent,
                'rubrics' => $rubricContext,
            ]);

            $result = $this->generate($prompt);
            if ($result) {
                return $result;
            }
        }

        if (config('ai.fallback', true)) {
            return $this->buildFallbackSuggestions(
                $teacherName,
                $observation->subject ?? 'N/A',
                $observation->grade_level ?? 'N/A',
                $observation->observation_type ?? 'N/A',
                $preConference?->finalized_focus ?? '',
                $planning?->suggested_focus ?? '',
            );
        }

        return null;
    }

    protected function buildFallbackSuggestions(
        string $teacherName,
        string $subject,
        string $gradeLevel,
        string $obsType,
        string $finalizedFocus,
        string $suggestedFocus
    ): string {
        $text = "**Observation Suggestions for {$teacherName}**\n\n";

        $text .= "**Key Indicators to Watch**\n";
        $text .= "Based on the observation focus, pay attention to:\n";
        if ($finalizedFocus) {
            $text .= "- How the teacher addresses: {$finalizedFocus}\n";
        }
        $text .= "- Clarity of instruction and communication of learning objectives\n";
        $text .= "- Use of instructional time and transition between activities\n";
        $text .= "- Responsiveness to student questions and misconceptions\n\n";

        $text .= "**Look-fors**\n";
        $text .= "During the observation, note the following observable behaviors:\n";
        $text .= "- Student engagement levels throughout the lesson\n";
        $text .= "- Quality of student-teacher interactions\n";
        $text .= "- Evidence of differentiated instruction for diverse learners\n";
        $text .= "- Use of formative assessment to guide instruction\n";
        $text .= "- Classroom management strategies and their effectiveness\n\n";

        $text .= "**Guiding Questions**\n";
        $text .= "Reflect on these questions during the observation:\n";
        $text .= "1. Do students understand what they are learning and why?\n";
        $text .= "2. Are all students actively participating, or are some disengaged?\n";
        $text .= "3. How does the teacher adjust instruction based on student responses?\n\n";

        $text .= "**Coaching Opportunities**\n";
        $text .= "Potential areas for post-observation discussion:\n";
        $text .= "- Alignment between planned activities and actual lesson delivery\n";
        $text .= "- Strategies for increasing student participation and ownership of learning\n";
        $text .= "- Integration of assessment data to inform next steps\n";
        if ($gradeLevel !== 'N/A' && $subject !== 'N/A') {
            $text .= "- Subject-specific pedagogical approaches for {$subject} at {$gradeLevel}\n";
        }
        $text .= "\n*These suggestions are generated based on the pre-conference data and general best practices. Specific observations may reveal additional insights.*\n";

        return $text;
    }

    public function buildSummary(CotRating $cotRating): array
    {
        $percentage = $cotRating->percentage();
        $domain = $cotRating->domain;

        $summary = [
            'analysis' => $this->buildAnalysis($percentage, $domain),
            'recommendations' => $this->buildRecommendations($percentage, $domain),
            'strengths' => $this->buildStrengths($percentage, $domain),
            'areas_for_improvement' => $this->buildAreasForImprovement($percentage, $domain),
        ];

        $aiFeedback = $cotRating->aiFeedback;
        if ($aiFeedback && $aiFeedback->confidence_score >= config('ai.confidence.high', 0.85)) {
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

        return [
            'strengths' => $allStrengths,
            'areas_for_improvement' => $allAreas,
            'recommendations' => $allRecommendations,
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
