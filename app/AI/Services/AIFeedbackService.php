<?php

namespace App\AI\Services;

use App\AI\Prompts\PostObservationPrompt;
use App\Models\AiFeedback;
use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;

class AIFeedbackService extends AIService
{
    protected string $stage = 'feedback';

    public function generateFeedback(int $cotRatingId): ?AiFeedback
    {
        $cotRating = CotRating::with(['observation.observee.user'])->findOrFail($cotRatingId);

        if (!$this->isAvailable()) {
            return null;
        }

        return $this->generateWithProvider($cotRating);
    }

    protected function generateWithProvider(CotRating $cotRating): ?AiFeedback
    {
        // Not Applicable indicators have no score to analyze — skip them.
        if ($cotRating->isNotApplicable()) {
            Log::info("Skipping AI feedback for COT Rating {$cotRating->id} (marked Not Applicable)");
            return null;
        }

        $observation = $cotRating->observation;
        $teacherName = $observation?->observee?->user?->name ?? 'Unknown';
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $obsType = $observation->observation_type ?? 'N/A';
        $domain = $cotRating->domain;
        $indicator = $cotRating->indicator;
        $rating = $cotRating->numericRating();
        $scaleMax = $observation?->ratingScaleMax() ?? 6;
        $percentage = $cotRating->isNotObserved() ? 0 : round(($rating / $scaleMax) * 100, 1);
        $scoreLabel = $cotRating->isNotObserved() ? 'NO (Not Observed)' : "{$rating}/{$scaleMax}";

        $rubricContext = $this->rubrics->getDomainContext($domain);
        $ratingScaleContext = $this->rubrics->getRatingScaleContext($observation?->ratingScale() ?: config('cot.rating_scale', []));

        $prompt = PostObservationPrompt::build([
            'teacher_name' => $teacherName,
            'subject' => $subject,
            'grade_level' => $gradeLevel,
            'observation_type' => $obsType,
            'domain' => $domain,
            'indicator' => $indicator,
            'score_label' => $scoreLabel,
            'percentage' => $percentage,
            'rubrics' => "{$rubricContext}\n\n{$ratingScaleContext}",
        ]);

        $data = $this->generateJson($prompt);

        if (!$data) {
            return null;
        }

        $confidence = $percentage >= 80
            ? config('ai.confidence.high', 0.85)
            : config('ai.confidence.medium', 0.60);

        $aiFeedback = AiFeedback::create([
            'cot_rating_id' => $cotRating->id,
            'analysis' => $data['analysis'] ?? 'Analysis generated.',
            'recommendations' => $data['recommendations'] ?? [],
            'strengths' => $data['strengths'] ?? [],
            'areas_for_improvement' => $data['areas_for_improvement'] ?? [],
            'confidence_score' => $confidence,
            'model_version' => $this->getModelForStage(),
        ]);

        Log::info("AI Feedback generated for COT Rating {$cotRating->id}");
        return $aiFeedback;
    }

    public function getFeedbackNeedingReview(): \Illuminate\Support\Collection
    {
        $lowConfidence = config('ai.confidence.low', 0.40);
        return AiFeedback::where('confidence_score', '<', $lowConfidence)
            ->with(['cotRating.observation.observee'])
            ->get();
    }

    public function regenerateFeedback(int $cotRatingId): ?AiFeedback
    {
        AiFeedback::where('cot_rating_id', $cotRatingId)->delete();
        return $this->generateFeedback($cotRatingId);
    }
}
