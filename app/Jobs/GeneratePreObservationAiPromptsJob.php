<?php

namespace App\Jobs;

use App\Models\Observation;
use App\Services\OfflinePackageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Generate + persist pre-observation AI teaching-strategy and evidence
 * prompts after the teacher confirms and uploads the DLL.
 *
 * Runs on the queue so the teacher's confirmation response stays fast.
 * Never fails the observation: AI outages degrade to deterministic
 * rubric-based prompts inside OfflinePackageService.
 */
class GeneratePreObservationAiPromptsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(protected int $observationId) {}

    public function handle(OfflinePackageService $packages): void
    {
        $observation = Observation::find($this->observationId);

        if (! $observation) {
            Log::warning('Pre-observation AI prompts: observation gone.', [
                'observation_id' => $this->observationId,
            ]);

            return;
        }

        $payload = $packages->generatePrompts($observation);

        Log::info('Pre-observation AI prompts stored.', [
            'observation_id' => $observation->id,
            'fallback' => $payload['fallback'] ?? true,
        ]);
    }

    public function tags(): array
    {
        return ['ai', 'pre_observation_prompts', "observation:{$this->observationId}"];
    }
}
