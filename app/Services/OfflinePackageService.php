<?php

namespace App\Services;

use App\AI\Services\DocumentExtractorService;
use App\AI\Services\LessonPlanSummaryService;
use App\AI\Services\PreObservationService;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Offline clinical-supervision package (IT adviser mandate).
 *
 * Builds the single JSON payload the tablet caches before a zero-connectivity
 * school visit: observation metadata, the PINNED COT version (indicators +
 * scoring scale), extracted lesson-plan content, and pre-generated AI
 * teaching-strategy / evidence prompts.
 *
 * When AI is unavailable the deterministic fallback still produces usable
 * prompts from the rubric itself, so a failed AI call can never block a
 * field visit. The payload always carries `ai_ready` + `fallback` flags so
 * the tablet UI can label AI content honestly.
 */
class OfflinePackageService
{
    public function __construct(
        protected LessonPlanSummaryService $summaries,
        protected PreObservationService $preObservation,
        protected DocumentExtractorService $extractor,
    ) {}

    /**
     * Generate + persist pre-observation AI prompts for an observation.
     * Returns the stored payload. Never throws: AI failures degrade to the
     * deterministic fallback (and are logged for the admin AI-usage screens).
     */
    public function generatePrompts(Observation $observation): array
    {
        $observation->loadMissing(['observee.user', 'preObservationPlanning', 'cotIndicatorVersion']);

        $lessonText = $this->lessonPlanText($observation);
        $summary = $this->summarize($observation, $lessonText);
        $insights = $this->insights($observation);

        if ($insights === null) {
            Log::warning('Offline package: AI insights unavailable, using rubric fallback.', [
                'observation_id' => $observation->id,
            ]);
            $payload = $this->fallbackPrompts($observation, $summary);
        } else {
            $payload = [
                'strategies' => $this->asList($insights['strategies'] ?? $insights),
                'evidence_prompts' => $this->asList($insights['evidence_prompts'] ?? []),
                'coaching_prompts' => $this->asList($insights['coaching_prompts'] ?? []),
                'summary' => $summary,
                'generated_at' => now()->toIso8601String(),
                'provider' => $insights['provider'] ?? 'configured-chain',
                'fallback' => false,
            ];
        }

        $observation->update([
            'lesson_plan_summary' => is_string($summary) ? $summary : json_encode($summary),
            'pre_observation_ai_prompts' => $payload,
        ]);

        return $payload;
    }

    /**
     * Assemble the complete offline bundle. Requires a confirmed observation;
     * callers must gate with Observation::isReadyForDownload().
     */
    public function buildPackage(Observation $observation): array
    {
        $observation->loadMissing([
            'observee.user',
            'observer',
            'school',
            'preObservationPlanning',
            'cotIndicatorVersion.indicators',
        ]);

        $version = $observation->cotIndicatorVersion;
        $indicators = $version
            ? $version->indicators()->active()->orderBy('sort_order')->get()
            : collect();

        $observee = $observation->observee;
        $prompts = $observation->pre_observation_ai_prompts ?? [];

        return [
            'package_version' => 1,
            'server_id' => $observation->id,
            'generated_at' => now()->toIso8601String(),
            'ai_ready' => $observation->hasPreObservationPrompts(),
            'observation' => [
                'id' => $observation->id,
                'subject' => $observation->subject,
                'grade_level' => $observation->grade_level,
                'grade_level_label' => $observation->grade_level_label,
                'observation_date' => $observation->observation_date?->format('Y-m-d'),
                'observation_mode' => $observation->observation_mode,
                'stage' => $observation->stage,
                'status' => $observation->status,
                'teacher' => [
                    'name' => $observee?->user?->name ?? 'Unknown',
                    'position' => $observee?->position_label ?? $observee?->position ?? null,
                ],
                'school' => $observation->school?->name,
            ],
            'rubric' => [
                'version_id' => $version?->id,
                'label' => $version?->label,
                'scale' => $version ? $version->ratingScale() : [],
                'scale_max' => $observation->ratingScaleMax(),
                'scale_min' => $observation->ratingScaleMin(),
                'indicators' => $indicators->map(fn ($i) => [
                    'code' => $i->code,
                    'domain' => $i->domain,
                    'description' => $i->description,
                ])->values()->all(),
            ],
            'lesson_plan' => [
                'objectives' => $observation->preObservationPlanning?->objective
                    ?? $observation->preObservationPlanning?->form_responses['objectives'] ?? null,
                'text' => mb_substr($this->lessonPlanText($observation), 0, 8000),
                'summary' => $observation->lesson_plan_summary,
            ],
            'ai_prompts' => $prompts,
        ];
    }

    /** Extracted lesson-plan text (PDF/DOCX via the shared extractor). */
    public function lessonPlanText(Observation $observation): string
    {
        $path = $observation->lesson_plan_path
            ?? $observation->preObservationPlanning?->lesson_plan_file;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return '';
        }

        try {
            return trim($this->extractor->extractText(Storage::disk('public')->path($path)));
        } catch (\Throwable $e) {
            Log::warning('Offline package: lesson-plan extraction failed.', [
                'observation_id' => $observation->id,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    protected function summarize(Observation $observation, string $lessonText): mixed
    {
        try {
            return $this->summaries->summarize($observation);
        } catch (\Throwable $e) {
            Log::warning('Offline package: AI summary failed, continuing without it.', [
                'observation_id' => $observation->id,
                'error' => $e->getMessage(),
            ]);

            // Deterministic mini-summary so the bundle is never empty.
            $objectives = $observation->preObservationPlanning?->objective;
            $head = $lessonText !== '' ? mb_substr(preg_replace('/\s+/', ' ', $lessonText), 0, 600) : null;

            return $objectives ?: $head;
        }
    }

    protected function insights(Observation $observation): ?array
    {
        try {
            $raw = $this->preObservation->generateInsights($observation);
        } catch (\Throwable $e) {
            Log::warning('Offline package: AI insights call failed.', [
                'observation_id' => $observation->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        // The service returns markdown text; keep it verbatim as strategies
        // and let the tablet render it beside the encoding form.
        return is_array($raw) ? $raw : ['strategies' => [$raw]];
    }

    /**
     * Deterministic fallback prompts derived from the pinned rubric, so the
     * tablet always has observable-evidence guidance even with zero AI.
     */
    public function fallbackPrompts(Observation $observation, mixed $summary = null): array
    {
        $observation->loadMissing(['cotIndicatorVersion.indicators']);
        $version = $observation->cotIndicatorVersion;
        $indicators = $version
            ? $version->indicators()->active()->orderBy('sort_order')->take(8)->get()
            : collect();

        $strategies = [];
        $evidence = [];
        foreach ($indicators as $i) {
            $strategies[] = "Plan explicit teacher moves for {$i->code}: {$i->description}";
            $evidence[] = "Observable evidence for {$i->code} ({$i->domain}): note what the teacher says/does and how learners respond (STAR).";
        }

        if ($strategies === []) {
            $strategies[] = 'Align lesson delivery with the posted learning objectives and check for understanding throughout.';
            $evidence[] = 'Record observable teacher/learner behaviour per indicator using STAR notes (Situation, Task, Action, Result).';
        }

        $payload = [
            'strategies' => $strategies,
            'evidence_prompts' => $evidence,
            'coaching_prompts' => [
                'Which part of the lesson best showed the learning objectives, and what is the evidence?',
                'Which indicator needs the most support next visit, and what concrete step will the teacher try?',
            ],
            'summary' => $summary,
            'generated_at' => now()->toIso8601String(),
            'provider' => 'rule-based-fallback',
            'fallback' => true,
        ];

        return $payload;
    }

    /** Normalize mixed AI output into a plain string list. */
    protected function asList(mixed $value): array
    {
        if (is_string($value)) {
            $lines = preg_split('/\r?\n/', $value) ?: [];
            $lines = array_map(fn ($l) => trim($l, " \t-•*0123456789.)"), $lines);

            return array_values(array_filter($lines));
        }

        if (is_array($value)) {
            $flat = [];
            array_walk_recursive($value, function ($v) use (&$flat) {
                if (is_string($v) && trim($v) !== '') {
                    $flat[] = trim($v);
                }
            });

            return array_values($flat);
        }

        return [];
    }
}
