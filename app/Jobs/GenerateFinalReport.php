<?php

namespace App\Jobs;

use App\AI\Services\FinalReportService;
use App\Models\Observation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateFinalReport implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected Observation $observation;

    public function __construct(Observation $observation)
    {
        $this->observation = $observation;
    }

    public function handle(FinalReportService $reportService): void
    {
        Log::info("Generating final report summary for observation {$this->observation->id}");

        $summary = $reportService->generateReportSummary($this->observation);

        if ($summary) {
            $this->observation->postConference()->updateOrCreate(
                ['observation_id' => $this->observation->id],
                ['ai_report_summary' => $summary]
            );
            Log::info("Final report summary saved for observation {$this->observation->id}");
        } else {
            Log::warning("Failed to generate final report summary for observation {$this->observation->id}");
        }
    }

    public function tags(): array
    {
        return ['ai', 'final_report', "observation:{$this->observation->id}"];
    }
}
