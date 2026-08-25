<?php

namespace App\AI\Support;

use App\AI\Exceptions\AIRateLimitException;
use App\AI\Providers\AIProviderManager;
use Illuminate\Support\Facades\Log;

/**
 * Builds friendly, user-facing explanations for why AI is unavailable.
 *
 * Every payload follows the same shape so the UI can render a consistent
 * "AI isn't available because ..." notice with a manual-entry option:
 *
 *   ['error' => string, 'reason' => string, 'retry_after' => ?int, 'manual_available' => true]
 */
class AIStatus
{
    public static function unavailable(string $stage, ?AIRateLimitException $e = null): array
    {
        if ($e !== null) {
            return [
                'error' => sprintf(
                    "AI isn't available right now because you've reached your usage limit for this task (%s). You can try again in %s.",
                    self::taskLabel($stage),
                    self::humanDelay($e->retryAfter)
                ),
                'reason' => 'rate_limit',
                'retry_after' => max(1, $e->retryAfter),
                'manual_available' => true,
            ];
        }

        if (! config('ai.enabled', true)) {
            return [
                'error' => "AI isn't available because AI features are currently turned off for your school. "
                    .'You can still fill this in yourself below.',
                'reason' => 'disabled',
                'retry_after' => null,
                'manual_available' => true,
            ];
        }

        $manager = app(AIProviderManager::class);

        if ($manager->fallbackChain($stage) === []) {
            return [
                'error' => "AI isn't available because no AI service is set up yet — an API key may be missing, "
                    .'or the selected model was retired by the provider. '
                    .'Please ask your administrator to check Admin → AI Settings. '
                    .'Meanwhile, you can write this section yourself.',
                'reason' => 'no_provider',
                'retry_after' => null,
                'manual_available' => true,
            ];
        }

        Log::channel(config('ai.logging.channel', 'stack'))->warning('AI task reported unavailable after generation attempts', [
            'task' => $stage,
        ]);

        return [
            'error' => "AI isn't available because the AI service could not be reached. "
                .'This can happen during short outages or when a model was discontinued. '
                .'Please try again in a few minutes, or write it yourself below.',
            'reason' => 'service_error',
            'retry_after' => null,
            'manual_available' => true,
        ];
    }

    public static function humanDelay(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        }

        if ($seconds < 3600) {
            $minutes = (int) ceil($seconds / 60);

            return "about {$minutes} minute".($minutes === 1 ? '' : 's');
        }

        $hours = (int) ceil($seconds / 3600);

        return "about {$hours} hour".($hours === 1 ? '' : 's');
    }

    protected static function taskLabel(string $stage): string
    {
        return match ($stage) {
            'pre_observation' => 'pre-observation insights & suggestions',
            'post_conference' => 'plan-vs-actual comparison',
            default => str_replace('_', ' ', $stage),
        };
    }
}
