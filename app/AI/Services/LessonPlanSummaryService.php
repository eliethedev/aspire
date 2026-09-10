<?php

namespace App\AI\Services;

use App\AI\Prompts\LessonPlanSummaryPrompt;
use App\AI\Routing\LessonPlanModelRouter;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Task: lesson_plan_summary
 *
 * Summarizes only the instructionally relevant content of a lesson plan,
 * preserving objectives, strategies, activities and assessment details.
 */
class LessonPlanSummaryService extends AIService
{
    protected string $stage = 'lesson_plan_summary';

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected DocumentExtractorService $documentExtractor,
        protected TeacherContextBuilder $teacherContextBuilder,
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    /**
     * Routing metadata for the most recent summarize() call.
     *
     * @var array{mode: string, label: string, provider: ?string, model: ?string, manual: bool, fallback_used: bool, reasons: string[]}|null
     */
    protected ?array $lastRouting = null;

    public function summarize(Observation $observation, ?string $manualMode = null): ?array
    {
        $this->lastRouting = null;

        if (! $this->isAvailable()) {
            return null;
        }

        $observation->loadMissing(['preObservationPlanning', 'observee.user']);
        $planning = $observation->preObservationPlanning;

        $lessonPlanContent = '';
        $lessonPlanFile = $planning?->lesson_plan_file;
        if ($lessonPlanFile && Storage::disk('public')->exists($lessonPlanFile)) {
            $fullPath = Storage::disk('public')->path($lessonPlanFile);
            $lessonPlanContent = $this->capLessonPlanText(
                $this->documentExtractor->extractText($fullPath)
            );
        }

        if (trim($lessonPlanContent) === '') {
            return null;
        }

        // No rubric context required for this task — summary stays focused
        // on the lesson plan itself.
        $teacherContext = $this->teacherContextBuilder->fromObservation($observation);
        $teacherContextBlock = $this->teacherContextBuilder->formatForPrompt($teacherContext);

        // Dynamic goal parsing: route to the specialized generation mode
        // matching the lesson's subject and goals before the API call.
        $router = app(LessonPlanModelRouter::class);
        $route = $router->resolve([
            'subject' => $observation->subject,
            'objective' => $planning?->objective,
            'strategies' => $planning?->teaching_strategies,
            'materials' => $planning?->materials,
            'assessment' => $planning?->assessment_methods,
        ], $this->stage, $manualMode);

        $baseContext = [
            'teacher_name' => $observation->observee?->user?->name ?? 'Unknown',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'lesson_plan_content' => $lessonPlanContent,
            'teacher_context' => $teacherContextBlock,
        ];

        $data = $this->generateJson(
            LessonPlanSummaryPrompt::build($baseContext + [
                'mode_label' => $route['label'],
                'mode_directives' => LessonPlanModelRouter::directivesFor($route['mode']),
            ]),
            ['temperature' => $route['temperature']],
            ['provider' => $route['provider'], 'model' => $route['model']],
            $observation->id,
        );

        if ($this->validateJsonResponse($data, ['summary'])) {
            $this->lastRouting = $this->routingMeta($route);

            return $data;
        }

        // Failover to the stable baseline when the routed model fails.
        if ($route['mode'] === LessonPlanModelRouter::MODE_BALANCED) {
            $this->lastRouting = $this->routingMeta($route);

            return null;
        }

        $fallback = $router->baselineRoute($this->stage);
        Log::channel(config('ai.logging.channel', 'stack'))->warning('AI.lesson_plan_summary: routed model failed; failing over to baseline', [
            'observation_id' => $observation->id,
            'routed_mode' => $route['mode'],
            'routed_model' => $route['provider'].'/'.$route['model'],
        ]);

        $data = $this->generateJson(
            LessonPlanSummaryPrompt::build($baseContext + [
                'mode_label' => $fallback['label'],
                'mode_directives' => LessonPlanModelRouter::directivesFor($fallback['mode']),
            ]),
            ['temperature' => $fallback['temperature']],
            ['provider' => $fallback['provider'], 'model' => $fallback['model']],
            $observation->id,
        );

        if (! $this->validateJsonResponse($data, ['summary'])) {
            $this->lastRouting = $this->routingMeta($route, true);

            return null;
        }

        $this->lastRouting = $this->routingMeta($route, true);

        return $data;
    }

    /**
     * Routing metadata for the most recent summarize() call.
     *
     * @return array{mode: string, label: string, provider: ?string, model: ?string, manual: bool, fallback_used: bool, reasons: string[]}|null
     */
    public function getLastRouting(): ?array
    {
        return $this->lastRouting;
    }

    /**
     * Merge the resolved route with the actual provider run outcome.
     */
    protected function routingMeta(array $route, bool $baselineFailover = false): array
    {
        $run = $this->getLastRunMeta();

        return [
            'mode' => $route['mode'],
            'label' => $route['label'],
            'provider' => $run['provider'] ?? $route['provider'],
            'model' => $run['model'] ?? $route['model'],
            'manual' => $route['manual'],
            'fallback_used' => $baselineFailover || (bool) ($run['fallback_used'] ?? false),
            'reasons' => $baselineFailover
                ? array_merge($route['reasons'], ['Routed model failed; served by the baseline model.'])
                : $route['reasons'],
        ];
    }

    protected function capLessonPlanText(string $text): string
    {
        $maxInputTokens = (int) config("ai.tasks.{$this->stage}.max_input_tokens", 6000);
        $maxChars = max(2000, $maxInputTokens * 4 - 1200); // reserve budget for instructions

        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars)."\n[Lesson plan truncated due to size limits]";
    }
}
