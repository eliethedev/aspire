<?php

namespace App\Services;

use App\Models\AiFeedback;
use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Support\Str;

class CoachingFocusSuggestionService
{
    private const MAX_SUGGESTIONS = 6;

    private const MAX_TEXT_LENGTH = 500;

    /**
     * Build suggested coaching focus areas for an observation.
     *
     * Sources, in priority order:
     *  1. Published AI feedback (areas for improvement + recommendations).
     *  2. Rule-based: weak COT ratings (lowest scores first, then Not Observed).
     *  3. Rule-based: post-conference prioritized next steps.
     *
     * @return array<int, array{text: string, source: string, label: string, badge: string}>
     */
    public function suggest(Observation $observation): array
    {
        $suggestions = [];
        $seen = [];

        $push = function (?string $rawText, string $source, string $label, string $badge) use (&$suggestions, &$seen): void {
            $text = trim(preg_replace('/\s+/u', ' ', (string) $rawText));

            if ($text === '') {
                return;
            }

            if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
                $text = rtrim(Str::limit($text, self::MAX_TEXT_LENGTH - 1, ''), '.… ');
                $text .= '.';
            }

            // Exact key plus a normalized key without any trailing parenthetical,
            // so "Indicator (Domain 1)" merges with the same AI-suggested indicator.
            $keys = [mb_strtolower($text)];

            $base = mb_strtolower(rtrim(preg_replace('/\s*\([^()]*\)\s*$/u', '', $text)));

            if ($base !== '' && $base !== $keys[0]) {
                $keys[] = $base;
            }

            foreach ($keys as $key) {
                if (isset($seen[$key])) {
                    return;
                }
            }

            foreach ($keys as $key) {
                $seen[$key] = true;
            }

            $suggestions[] = [
                'text' => $text,
                'source' => $source,
                'label' => $label,
                'badge' => $badge,
            ];
        };

        // 1. Published AI feedback insights.
        $aiFeedbacks = $observation->relationLoaded('aiFeedbacks')
            ? $observation->aiFeedbacks
            : $observation->aiFeedbacks()->where('status', 'published')->get();

        $aiFeedbacks
            ->filter(fn (AiFeedback $feedback) => $feedback->status === 'published')
            ->each(function (AiFeedback $feedback) use ($push): void {
                foreach ((array) $feedback->areas_for_improvement as $area) {
                    $push(is_string($area) ? $area : null, 'ai', 'AI Insight', 'bg-purple-100 text-purple-700');
                }

                foreach ((array) $feedback->recommendations as $recommendation) {
                    $push(is_string($recommendation) ? $recommendation : null, 'ai', 'AI Insight', 'bg-purple-100 text-purple-700');
                }
            });

        // 2. Rule-based: weak COT ratings (lowest first, Not Observed last).
        $cotRatings = $observation->relationLoaded('cotRatings')
            ? $observation->cotRatings
            : $observation->cotRatings()->get();

        $cotRatings
            ->filter(fn (CotRating $rating) => $rating->not_observed || ($rating->rating !== null && $rating->rating <= 3))
            ->sort(function (CotRating $a, CotRating $b): int {
                $aScore = $a->not_observed ? 99 : $a->rating;
                $bScore = $b->not_observed ? 99 : $b->rating;

                return [$aScore, $a->indicator_code ?? ''] <=> [$bScore, $b->indicator_code ?? ''];
            })
            ->each(function (CotRating $rating) use ($push, $observation): void {
                if ($rating->not_observed) {
                    $push(
                        sprintf('%s (%s)', $rating->indicator, $rating->domain),
                        'rule',
                        'Not Observed',
                        'bg-gray-100 text-gray-600'
                    );

                    return;
                }

                $push(
                    sprintf('%s (%s)', $rating->indicator, $rating->domain),
                    'rule',
                    sprintf('COT %d/%d · %s', $rating->rating, $observation->ratingScaleMax(), $rating->descriptiveLabel($observation->ratingScale())),
                    $rating->rating <= 2 ? 'bg-red-100 dark:bg-red-900/30 text-red-700' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700'
                );
            });

        // 3. Rule-based: post-conference prioritized next steps.
        $nextSteps = $observation->relationLoaded('postConference')
            ? $observation->postConference?->prioritized_next_steps
            : $observation->postConference()->value('prioritized_next_steps');

        if (is_string($nextSteps) && trim($nextSteps) !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $nextSteps) as $line) {
                $step = preg_replace('/^\s*(?:[-*•]|\d+[.)])\s*/u', '', (string) $line);

                if (trim((string) $step) !== '') {
                    $push($step, 'post_conference', 'Post-Conference', 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700');
                }
            }
        }

        return array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
    }
}
