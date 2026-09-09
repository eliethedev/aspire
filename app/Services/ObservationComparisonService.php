<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\CotRating;
use App\AI\RAG\PPSTRubricRepository;
use App\AI\Services\AIService;

class ObservationComparisonService
{
    protected IndicatorTrendService $trendService;
    protected PPSTRubricRepository $rubrics;

    public function __construct(IndicatorTrendService $trendService, PPSTRubricRepository $rubrics)
    {
        $this->trendService = $trendService;
        $this->rubrics = $rubrics;
    }

    public function getPreviousObservation(Observation $current): ?Observation
    {
        return Observation::where('observee_id', $current->observee_id)
            ->where('observee_type', $current->observee_type)
            ->where('id', '!=', $current->id)
            ->where('status', 'completed')
            ->whereNotNull('overall_score')
            ->with('cotRatings')
            ->orderBy('observation_date', 'desc')
            ->first();
    }

    public function compareWithPrevious(Observation $current): ?array
    {
        $previous = $this->getPreviousObservation($current);
        if (!$previous) {
            return null;
        }

        $comparison = $this->trendService->compareObservations($current, $previous);

        $comparison['current_date'] = $current->observation_date?->format('F d, Y') ?? 'N/A';
        $comparison['previous_date'] = $previous->observation_date?->format('F d, Y') ?? 'N/A';
        $comparison['current_id'] = $current->id;
        $comparison['previous_id'] = $previous->id;

        return $comparison;
    }

    public function generateComparisonNarrative(Observation $current): ?string
    {
        $comparison = $this->compareWithPrevious($current);
        if (!$comparison) {
            return null;
        }

        $scaleMax = $current->ratingScaleMax();
        $teacherName = $current->observee?->user?->name ?? 'the teacher';
        $currentScore = $comparison['current_overall'];
        $previousScore = $comparison['previous_overall'];
        $overallDelta = $comparison['overall_delta'];
        $overallDirection = $comparison['overall_direction'];

        $narrative = "## Observation Progress Comparison\n\n";
        $narrative .= "**{$teacherName}**\n\n";
        $narrative .= "| | Previous ({$comparison['previous_date']}) | Current ({$comparison['current_date']}) | Change |\n";
        $narrative .= "|---|---|---|---|\n";
        $narrative .= "| **Overall Score** | {$previousScore}% | {$currentScore}% | " . ($overallDelta > 0 ? "+" : "") . "{$overallDelta}% |\n\n";

        if ($overallDirection === 'improved') {
            $narrative .= "📈 **Overall performance has improved** by " . abs($overallDelta) . " percentage points since the previous observation.\n\n";
        } elseif ($overallDirection === 'declined') {
            $narrative .= "📉 **Overall performance has declined** by " . abs($overallDelta) . " percentage points since the previous observation.\n\n";
        } else {
            $narrative .= "➡️ **Overall performance has remained stable** since the previous observation.\n\n";
        }

        if ($comparison['improved']->isNotEmpty()) {
            $narrative .= "### Most Improved Areas\n\n";
            $sorted = $comparison['improved']->sortByDesc('delta');
            foreach ($sorted as $item) {
                $narrative .= "- **{$item['code']}: {$item['indicator']}** — improved from {$item['previous_rating']}/{$scaleMax} to {$item['current_rating']}/{$scaleMax} (+{$item['delta']})\n";
            }
            $narrative .= "\n";
        }

        if ($comparison['declined']->isNotEmpty()) {
            $narrative .= "### Areas That Need Attention\n\n";
            $sorted = $comparison['declined']->sortBy('delta');
            foreach ($sorted as $item) {
                $narrative .= "- **{$item['code']}: {$item['indicator']}** — declined from {$item['previous_rating']}/{$scaleMax} to {$item['current_rating']}/{$scaleMax} ({$item['delta']})\n";
            }
            $narrative .= "\n";
        }

        if ($comparison['same']->isNotEmpty()) {
            $narrative .= "### Consistent Performance\n\n";
            foreach ($comparison['same'] as $item) {
                $narrative .= "- {$item['code']}: {$item['indicator']} — maintained at {$item['current_rating']}/{$scaleMax}\n";
            }
            $narrative .= "\n";
        }

        return $narrative;
    }
}
