<?php

namespace App\Jobs;

use App\AI\Services\AIFeedbackService;
use App\Models\CotRating;
use App\Models\Observation;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GeneratePostObservationFeedback implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected CotRating $cotRating;

    public function __construct(CotRating $cotRating)
    {
        $this->cotRating = $cotRating;
    }

    public function handle(AIFeedbackService $feedbackService): void
    {
        Log::info("Generating AI feedback for COT Rating {$this->cotRating->id}");

        $feedback = $feedbackService->generateFeedback($this->cotRating->id);

        if ($feedback) {
            Log::info("AI feedback generated for COT Rating {$this->cotRating->id}");

            app(AuditLogService::class)->log(
                'feedback_generated',
                'ai',
                (string) $this->cotRating->id,
                "Post-observation AI feedback generated for COT rating #{$this->cotRating->id}",
                'success',
                [],
                [],
                ['type' => 'post_observation', 'cot_rating_id' => $this->cotRating->id, 'observation_id' => $this->cotRating->observation_id],
            );
        } else {
            Log::warning("Failed to generate AI feedback for COT Rating {$this->cotRating->id}");

            app(AuditLogService::class)->log(
                'feedback_failed',
                'ai',
                (string) $this->cotRating->id,
                "Post-observation AI feedback failed for COT rating #{$this->cotRating->id}",
                'failed',
                [],
                [],
                ['type' => 'post_observation', 'cot_rating_id' => $this->cotRating->id, 'observation_id' => $this->cotRating->observation_id],
            );
        }

        $this->refreshObservationAiStatus($feedback === null);
    }

    /**
     * Advance the parent observation's deferred-AI flag (Architecture B).
     *
     * Only observations synced with ai_status=pending are touched; the online
     * flow leaves ai_status=none and its badges derive from real feedback.
     * Not-Applicable ratings need no AI, so they count as satisfied.
     */
    protected function refreshObservationAiStatus(bool $justFailed): void
    {
        $observation = Observation::find($this->cotRating->observation_id);

        if (! $observation || $observation->ai_status !== 'pending') {
            return;
        }

        if (! $observation->cotRatings()->exists()) {
            return;
        }

        $stillWaiting = $observation->cotRatings()
            ->where('not_applicable', false)
            ->whereDoesntHave('aiFeedback')
            ->exists();

        if (! $stillWaiting) {
            $observation->update(['ai_status' => 'done']);

            return;
        }

        $hasAnyFeedback = $observation->cotRatings()->whereHas('aiFeedback')->exists();

        if ($justFailed && ! $hasAnyFeedback) {
            $observation->update(['ai_status' => 'failed']);
        }
        // Otherwise: other ratings' jobs are still queued — stay pending.
    }

    public function tags(): array
    {
        return ['ai', 'feedback', "cot_rating:{$this->cotRating->id}"];
    }
}
