<?php

namespace App\Services;

use App\AI\Services\AIFeedbackService as NewAIFeedbackService;
use App\AI\Services\PostConferenceService;
use App\AI\Services\PreObservationService;
use App\Models\AiFeedback;
use App\Models\CotRating;
use App\Models\Observation;

class AIFeedbackService
{
    protected NewAIFeedbackService $newService;

    protected PreObservationService $preObservation;

    protected PostConferenceService $postConference;

    public function __construct(
        NewAIFeedbackService $newService,
        PreObservationService $preObservation,
        PostConferenceService $postConference
    ) {
        $this->newService = $newService;
        $this->preObservation = $preObservation;
        $this->postConference = $postConference;
    }

    public function generateFeedback(int $cotRatingId): ?AiFeedback
    {
        return $this->newService->generateFeedback($cotRatingId);
    }

    public function generatePreObservationInsights(Observation $observation): ?string
    {
        return $this->preObservation->generateInsights($observation);
    }

    public function generatePostConferenceComparison(Observation $observation): ?string
    {
        return $this->postConference->generateComparison($observation);
    }

    public function getFeedbackNeedingReview(): \Illuminate\Support\Collection
    {
        return $this->newService->getFeedbackNeedingReview();
    }

    public function regenerateFeedback(int $cotRatingId): ?AiFeedback
    {
        return $this->newService->regenerateFeedback($cotRatingId);
    }

    public function isGeminiConfigured(): bool
    {
        return $this->newService->isAvailable();
    }
}
