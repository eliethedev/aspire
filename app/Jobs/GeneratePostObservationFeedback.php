<?php

namespace App\Jobs;

use App\AI\Services\AIFeedbackService;
use App\Models\CotRating;
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
    }

    public function tags(): array
    {
        return ['ai', 'feedback', "cot_rating:{$this->cotRating->id}"];
    }
}
