<?php

namespace App\Services;

use App\AI\Services\ObservationGuidanceService;
use App\Models\Observation;
use App\Models\CotRating;

class AISuggestionService
{
    protected ObservationGuidanceService $guidanceService;

    public function __construct(ObservationGuidanceService $guidanceService)
    {
        $this->guidanceService = $guidanceService;
    }

    public function generateObservationSuggestions(Observation $observation): ?string
    {
        return $this->guidanceService->generateSuggestions($observation);
    }

    public function buildSummary(CotRating $cotRating): array
    {
        return $this->guidanceService->buildSummary($cotRating);
    }

    public function compileOverallSummary(Observation $observation): array
    {
        return $this->guidanceService->compileOverallSummary($observation);
    }
}
