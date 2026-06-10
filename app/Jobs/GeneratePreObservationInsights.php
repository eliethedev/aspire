<?php

namespace App\Jobs;

use App\AI\Services\PreObservationService;
use App\Models\Observation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GeneratePreObservationInsights implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected Observation $observation;

    public function __construct(Observation $observation)
    {
        $this->observation = $observation;
    }

    public function handle(PreObservationService $preObservationService): void
    {
        Log::info("Generating pre-observation insights for observation {$this->observation->id}");

        $insights = $preObservationService->generateInsights($this->observation);

        if ($insights) {
            $this->observation->preObservationPlanning()->updateOrCreate(
                ['observation_id' => $this->observation->id],
                ['ai_insights' => $insights]
            );
            Log::info("Pre-observation insights saved for observation {$this->observation->id}");
        } else {
            Log::warning("Failed to generate pre-observation insights for observation {$this->observation->id}");
        }
    }

    public function tags(): array
    {
        return ['ai', 'pre_observation', "observation:{$this->observation->id}"];
    }
}
