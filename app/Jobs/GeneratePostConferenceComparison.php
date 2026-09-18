<?php

namespace App\Jobs;

use App\AI\Exceptions\AIRateLimitException;
use App\Models\Observation;
use App\Services\AIFeedbackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePostConferenceComparison implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 45;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    protected Observation $observation;

    public function __construct(Observation $observation)
    {
        $this->observation = $observation;
    }

    /**
     * Generate the AI post-conference comparison and persist the result.
     *
     * The HTTP call is wrapped in explicit try/catch blocks for connection
     * failures and rate limits so the worker can retry gracefully instead of
     * surfacing an unhandled exception that marks the job as failed.
     */
    public function handle(AIFeedbackService $feedbackService): void
    {
        Log::info("Generating post-conference AI comparison for observation {$this->observation->id}");

        $this->observation->loadMissing(['postConference', 'preObservationPlanning', 'observee']);

        try {
            $comparison = $feedbackService->generatePostConferenceComparison($this->observation);
        } catch (ConnectionException $e) {
            Log::warning(
                "AI connection failed for post-conference comparison on observation {$this->observation->id}: {$e->getMessage()}",
            );
            throw $e;
        } catch (AIRateLimitException $e) {
            Log::warning(
                "AI rate limit hit for post-conference comparison on observation {$this->observation->id}: {$e->getMessage()}",
            );
            return;
        } catch (\Throwable $e) {
            Log::error(
                "AI post-conference comparison failed for observation {$this->observation->id}: {$e->getMessage()}",
                ['exception' => $e],
            );
            return;
        }

        if (! $comparison) {
            Log::info("Post-conference comparison returned no content for observation {$this->observation->id}");

            return;
        }

        $this->observation->postConference()->updateOrCreate(
            ['observation_id' => $this->observation->id],
            ['ai_comparison' => $comparison],
        );

        Log::info("Post-conference AI comparison saved for observation {$this->observation->id}");
    }

    /**
     * Get the tags that should be applied to the job for dispatching.
     */
    public function tags(): array
    {
        return ['ai', 'post-conference', "observation:{$this->observation->id}"];
    }
}
