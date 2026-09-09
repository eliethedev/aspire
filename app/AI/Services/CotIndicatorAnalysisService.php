<?php

namespace App\AI\Services;

use App\AI\Prompts\CotIndicatorAnalysisPrompt;
use App\Models\CotRating;

/**
 * Task: cot_indicator_analysis
 *
 * Retrieves the applicable COT indicator, compares observation evidence
 * against it, and explains strengths/gaps. NEVER assigns the final rating —
 * the supervisor remains responsible for that decision.
 */
class CotIndicatorAnalysisService extends AIService
{
    protected string $stage = 'cot_indicator_analysis';

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected \App\AI\RAG\CotIndicatorRepository $indicators,
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    public function analyze(CotRating $cotRating): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $cotRating->loadMissing(['observation.observee.user']);
        $observation = $cotRating->observation;

        // Targeted RAG: only the selected indicator + its domain/strand info.
        $indicator = $this->indicators->getIndicatorByCode($cotRating->indicator_code);

        $indicatorContext = $indicator
            ? $this->rubrics->getIndicatorsContext([$cotRating->indicator_code])
            : '';

        $prompt = CotIndicatorAnalysisPrompt::build([
            'teacher_name' => $observation?->observee?->user?->name ?? 'Unknown',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'indicator_code' => $cotRating->indicator_code,
            'indicator_description' => $indicator['description'] ?? $cotRating->indicator,
            'domain' => $indicator['domain'] ?? $cotRating->domain,
            'score_label' => $cotRating->isNotObserved()
                ? 'NO (Not Observed)'
                : $cotRating->numericRating().'/'.($observation?->ratingScaleMax() ?? 6),
            'comments' => $cotRating->comments ?: 'No comments recorded.',
            'rating_scale' => $this->rubrics->getRatingScaleContext($observation?->ratingScale() ?: config('cot.rating_scale', [])),
            'indicator_context' => $indicatorContext,
        ]);

        $data = $this->generateJson($prompt);

        if (! $this->validateJsonResponse($data, ['evidence', 'gaps', 'considerations'])) {
            return null;
        }

        return $data;
    }
}
