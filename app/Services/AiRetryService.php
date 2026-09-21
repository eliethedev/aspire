<?php

namespace App\Services;

use App\Jobs\GeneratePostObservationFeedback;
use App\Models\Observation;

/**
 * Requeues deferred AI generation for observations stuck at ai_status=failed
 * (Architecture B). Only failed observations are touched — pending ones may
 * still have jobs in flight, and re-dispatching those would duplicate rows.
 */
class AiRetryService
{
    /**
     * Flip a failed observation back to pending and dispatch jobs for every
     * rating still missing AI feedback.
     *
     * @return int Number of jobs dispatched (0 when nothing needed doing).
     */
    public function retry(Observation $observation): int
    {
        if ($observation->ai_status !== 'failed') {
            return 0;
        }

        $ratings = $observation->cotRatings()->whereDoesntHave('aiFeedback')->get();

        if ($ratings->isEmpty()) {
            // Feedback arrived through another path — just clear the flag.
            $observation->update(['ai_status' => 'done']);

            return 0;
        }

        $observation->update(['ai_status' => 'pending']);

        foreach ($ratings as $rating) {
            GeneratePostObservationFeedback::dispatch($rating);
        }

        return $ratings->count();
    }
}
