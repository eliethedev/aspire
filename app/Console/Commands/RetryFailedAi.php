<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Services\AiRetryService;
use Illuminate\Console\Command;

class RetryFailedAi extends Command
{
    protected $signature = 'ai:retry-failed
                            {--limit=50 : Max failed observations to requeue in one run}';

    protected $description = 'Requeue deferred AI generation for observations stuck at ai_status=failed';

    public function handle(AiRetryService $retry): int
    {
        $observations = Observation::where('ai_status', 'failed')
            ->limit(max(1, (int) $this->option('limit')))
            ->get(['id']);

        $obsCount = 0;
        $jobCount = 0;

        foreach ($observations as $stub) {
            $observation = Observation::find($stub->id);
            if (! $observation) {
                continue;
            }
            $jobCount += $retry->retry($observation);
            $obsCount++;
        }

        $this->info("Requeued AI for {$jobCount} rating(s) across {$obsCount} observation(s).");

        return self::SUCCESS;
    }
}
