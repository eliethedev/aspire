<?php

namespace App\Services;

use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Support\Collection;

class IndicatorTrendService
{
    public function getIndicatorTrends(int $observeeId, string $observeeType): array
    {
        $observations = Observation::where('observee_id', $observeeId)
            ->where('observee_type', $observeeType)
            ->where('status', 'completed')
            ->whereNotNull('overall_score')
            ->with(['cotRatings' => fn($q) => $q->where('not_observed', false)->where('not_applicable', false)])
            ->orderBy('observation_date', 'asc')
            ->get();

        if ($observations->isEmpty()) {
            return [
                'indicators' => collect(),
                'domains' => collect(),
                'low_indicators' => collect(),
                'total_observations' => 0,
                'date_range' => [
                    'start' => null,
                    'end' => null,
                ],
            ];
        }

        $allRatings = CotRating::whereHas('observation', fn($q) => $q
            ->where('observee_id', $observeeId)
            ->where('observee_type', $observeeType)
            ->where('status', 'completed')
        )
        ->where('not_observed', false)
        ->where('not_applicable', false)
        ->get();

        $grouped = $allRatings->groupBy('indicator_code');

        $indicators = $grouped->map(function ($ratings, $code) use ($observations) {
            $avg = $ratings->avg('rating');
            $first = $ratings->first();
            $last = $ratings->last();
            $trend = $this->calculateTrend($ratings);
            $occurrences = $ratings->count();

            return [
                'code' => $code,
                'domain' => $first->domain ?? '',
                'indicator' => $first->indicator ?? '',
                'average_rating' => round($avg, 2),
                'average_percentage' => round(($avg / 6) * 100, 1),
                'descriptive' => $this->descriptiveLabel($avg),
                'min_rating' => $ratings->min('rating'),
                'max_rating' => $ratings->max('rating'),
                'trend' => $trend['direction'],
                'trend_delta' => $trend['delta'],
                'occurrences' => $occurrences,
                'history' => $ratings->map(fn($r) => [
                    'observation_id' => $r->observation_id,
                    'date' => $r->observation->observation_date?->format('M d, Y'),
                    'rating' => $r->rating,
                    'percentage' => round(($r->rating / 6) * 100, 1),
                ])->toArray(),
            ];
        })->values();

        $lowIndicators = $indicators->filter(fn($i) => $i['average_rating'] < 3 && $i['occurrences'] >= 2)->values();

        $domainGrouped = $indicators->groupBy('domain')->map(function ($inds, $domain) {
            $avg = $inds->avg('average_rating');
            return [
                'domain' => $domain,
                'average_rating' => round($avg, 2),
                'average_percentage' => round(($avg / 6) * 100, 1),
                'descriptive' => $this->descriptiveLabel($avg),
                'indicator_count' => $inds->count(),
                'low_indicator_count' => $inds->filter(fn($i) => $i['average_rating'] < 3)->count(),
            ];
        })->values();

        return [
            'indicators' => $indicators,
            'domains' => $domainGrouped,
            'low_indicators' => $lowIndicators,
            'total_observations' => $observations->count(),
            'date_range' => [
                'start' => $observations->first()?->observation_date?->format('M d, Y'),
                'end' => $observations->last()?->observation_date?->format('M d, Y'),
            ],
        ];
    }

    public function getConsistentlyLowIndicators(int $observeeId, string $observeeType, int $minObservations = 2): Collection
    {
        $trends = $this->getIndicatorTrends($observeeId, $observeeType);

        return $trends['indicators']->filter(fn($i) =>
            $i['average_rating'] < 3 && $i['occurrences'] >= $minObservations
        )->sortBy('average_rating')->values();
    }

    public function compareObservations(Observation $current, Observation $previous): array
    {
        $currentRatings = $current->cotRatings->where('not_observed', false)->where('not_applicable', false);
        $previousRatings = $previous->cotRatings->where('not_observed', false)->where('not_applicable', false);

        $currentByCode = $currentRatings->keyBy('indicator_code');
        $previousByCode = $previousRatings->keyBy('indicator_code');

        $allCodes = $currentByCode->keys()->merge($previousByCode->keys())->unique();

        $comparisons = $allCodes->map(function ($code) use ($currentByCode, $previousByCode) {
            $cur = $currentByCode->get($code);
            $prev = $previousByCode->get($code);

            $curRating = $cur?->rating ?? null;
            $prevRating = $prev?->rating ?? null;
            $delta = ($curRating && $prevRating) ? $curRating - $prevRating : null;

            return [
                'code' => $code,
                'domain' => $cur?->domain ?? $prev?->domain ?? '',
                'indicator' => $cur?->indicator ?? $prev?->indicator ?? '',
                'current_rating' => $curRating,
                'previous_rating' => $prevRating,
                'delta' => $delta,
                'direction' => $delta === null ? 'new' : ($delta > 0 ? 'improved' : ($delta < 0 ? 'declined' : 'same')),
            ];
        })->values();

        $overallDelta = ($current->overall_score && $previous->overall_score)
            ? round($current->overall_score - $previous->overall_score, 2)
            : null;

        return [
            'comparisons' => $comparisons,
            'current_overall' => $current->overall_score,
            'previous_overall' => $previous->overall_score,
            'overall_delta' => $overallDelta,
            'overall_direction' => $overallDelta === null ? 'new' : ($overallDelta > 0 ? 'improved' : ($overallDelta < 0 ? 'declined' : 'same')),
            'improved' => $comparisons->where('direction', 'improved'),
            'declined' => $comparisons->where('direction', 'declined'),
            'same' => $comparisons->where('direction', 'same'),
        ];
    }

    private function calculateTrend(Collection $ratings): array
    {
        if ($ratings->count() < 2) {
            return ['direction' => 'insufficient_data', 'delta' => null];
        }

        $sorted = $ratings->sortBy('observation_id');
        $first = $sorted->first()->rating;
        $last = $sorted->last()->rating;
        $delta = $last - $first;

        if ($delta > 0.5) {
            $direction = 'improving';
        } elseif ($delta < -0.5) {
            $direction = 'declining';
        } else {
            $direction = 'stable';
        }

        return ['direction' => $direction, 'delta' => round($delta, 2)];
    }

    private function descriptiveLabel(float $rating): string
    {
        return match (true) {
            $rating >= 5.5 => 'Outstanding',
            $rating >= 4.5 => 'Very Satisfactory',
            $rating >= 3.5 => 'Satisfactory',
            $rating >= 2.5 => 'Unsatisfactory',
            default => 'Poor',
        };
    }
}
