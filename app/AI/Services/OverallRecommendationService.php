<?php

namespace App\AI\Services;

use App\AI\Prompts\OverallRecommendationPrompt;
use App\Models\Observation;

/**
 * Task: overall_recommendation
 *
 * Synthesizes observation evidence, ratings and identified development
 * needs into prioritized, actionable recommendations. Advisory only —
 * the supervisor makes all final decisions.
 */
class OverallRecommendationService extends AIService
{
    protected string $stage = 'overall_recommendation';

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected \App\AI\RAG\CotIndicatorRepository $indicators,
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    public function recommend(Observation $observation): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $observation->loadMissing(['cotRatings', 'observee.user']);

        $ratings = $observation->cotRatings;
        if ($ratings->isEmpty()) {
            return null;
        }

        // Ratings summary: one line per rated indicator (Not Applicable rows are excluded).
        $lines = [];
        foreach ($ratings->reject(fn ($r) => $r->isNotApplicable()) as $rating) {
            $score = $rating->isNotObserved()
                ? 'NO (Not Observed)'
                : $rating->numericRating().'/6';

            $comment = $rating->comments ? " — Comments: ".mb_substr($rating->comments, 0, 300) : '';
            $lines[] = "- {$rating->indicator_code} ({$rating->domain}): {$score}{$comment}";
        }
        $ratingsSummary = implode("\n", $lines);

        // Targeted RAG: only indicators represented in this observation.
        $indicatorCodes = $ratings->pluck('indicator_code')->filter()->unique()->values()->all();
        $indicatorContext = $this->rubrics->getIndicatorsContext($indicatorCodes);

        // Development needs: indicators rated below the attention threshold.
        $lowThreshold = 3;
        $needs = $ratings
            ->filter(fn ($r) => ! $r->isNotObserved() && $r->numericRating() > 0 && $r->numericRating() <= $lowThreshold)
            ->map(fn ($r) => "{$r->indicator_code}: {$r->numericRating()}/6")
            ->implode('; ');

        $prompt = OverallRecommendationPrompt::build([
            'teacher_name' => $observation->observee?->user?->name ?? 'Unknown',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'ratings_summary' => $ratingsSummary,
            'indicator_context' => $indicatorContext !== '' ? $indicatorContext : $this->rubrics->getSystemContext(),
            'rating_scale' => $this->rubrics->getRatingScaleContext(),
            'development_needs' => $needs !== '' ? $needs : 'None below threshold.',
        ]);

        $data = $this->generateJson($prompt);

        if (! $this->validateJsonResponse($data, ['overview', 'recommendations'])) {
            return null;
        }

        return $data;
    }
}
