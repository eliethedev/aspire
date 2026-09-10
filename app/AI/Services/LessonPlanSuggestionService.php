<?php

namespace App\AI\Services;

use App\AI\Prompts\LessonPlanSuggestionPrompt;
use App\AI\Routing\LessonPlanModelRouter;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Task: lesson_plan_suggestion
 *
 * Identifies weaknesses/gaps in a teacher's lesson plan and suggests
 * practical improvements aligned with applicable PPST/COT indicators.
 */
class LessonPlanSuggestionService extends AIService
{
    protected string $stage = 'lesson_plan_suggestion';

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected DocumentExtractorService $documentExtractor,
        protected \App\AI\RAG\CotIndicatorRepository $indicators,
        protected TeacherContextBuilder $teacherContextBuilder,
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    /**
     * Routing metadata for the most recent suggest() call: generation
     * mode, resolved provider/model, manual override flag, reasons and
     * whether failover served the response. Surfaced to the UI so users
     * can see which engine powered the output.
     *
     * @var array{mode: string, label: string, provider: ?string, model: ?string, manual: bool, fallback_used: bool, reasons: string[]}|null
     */
    protected ?array $lastRouting = null;

    /**
     * @return array|null Validated suggestions payload, or null on failure
     */
    public function suggest(Observation $observation, ?string $manualMode = null): ?array
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
            // Cap extracted content to the task's input budget before prompting.
            $lessonPlanContent = $this->capLessonPlanText(
                $this->documentExtractor->extractText($fullPath)
            );
        }

        // Targeted RAG: only indicators relevant to this lesson's content.
        $relevant = $this->rubrics->getIndicatorsContext(
            collect($this->indicators->findRelevantIndicators(
                ($lessonPlanContent ?: '').' '.$planning?->objective.' '.$planning?->teaching_strategies,
                limit: 8,
            ))->pluck('code')->all()
        );

        $teacherContext = $this->teacherContextBuilder->fromObservation($observation);
        $teacherContextBlock = $this->teacherContextBuilder->formatForPrompt($teacherContext);

        // Dynamic goal parsing: inspect the submitted lesson targets and
        // strategies, then resolve the specialized generation mode BEFORE
        // the API call.
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
            'objective' => $planning?->objective ?? 'Not specified',
            'strategies' => $planning?->teaching_strategies ?? 'Not specified',
            'materials' => $planning?->materials ?? 'Not specified',
            'assessment' => $planning?->assessment_methods ?? 'Not specified',
            'lesson_plan_content' => $lessonPlanContent,
            'rubrics' => $relevant !== '' ? $relevant : $this->rubrics->getSystemContext(),
            'teacher_context' => $teacherContextBlock,
        ];

        $data = $this->generateJson(
            LessonPlanSuggestionPrompt::build($baseContext + [
                'mode_label' => $route['label'],
                'mode_directives' => LessonPlanModelRouter::directivesFor($route['mode']),
            ]),
            ['temperature' => $route['temperature']],
            ['provider' => $route['provider'], 'model' => $route['model']],
            $observation->id,
        );

        if ($this->validateJsonResponse($data, ['gaps', 'suggestions'])) {
            $this->lastRouting = $this->routingMeta($route);

            return $data;
        }

        // Failover: the routed model failed (rate limit, downtime or
        // validation error). Retry once against the stable baseline
        // before giving up — unless we already ran on the baseline.
        if ($route['mode'] === LessonPlanModelRouter::MODE_BALANCED) {
            $this->lastRouting = $this->routingMeta($route);

            return null;
        }

        $fallback = $router->baselineRoute($this->stage);
        Log::channel(config('ai.logging.channel', 'stack'))->warning('AI.lesson_plan_suggestion: routed model failed; failing over to baseline', [
            'observation_id' => $observation->id,
            'routed_mode' => $route['mode'],
            'routed_model' => $route['provider'].'/'.$route['model'],
        ]);

        $data = $this->generateJson(
            LessonPlanSuggestionPrompt::build($baseContext + [
                'mode_label' => $fallback['label'],
                'mode_directives' => LessonPlanModelRouter::directivesFor($fallback['mode']),
            ]),
            ['temperature' => $fallback['temperature']],
            ['provider' => $fallback['provider'], 'model' => $fallback['model']],
            $observation->id,
        );

        if (! $this->validateJsonResponse($data, ['gaps', 'suggestions'])) {
            $this->lastRouting = $this->routingMeta($route, true);

            return null;
        }

        $this->lastRouting = $this->routingMeta($route, true);

        return $data;
    }

    /**
     * Routing metadata for the most recent suggest() call.
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
        $maxChars = max(2000, $maxInputTokens * 4 - 2500); // reserve budget for instructions + rubric context

        if (strlen($text) <= $maxChars) {
            return $text;
        }

        Log::channel(config('ai.logging.channel', 'stack'))->info("AI.{$this->stage}: lesson plan truncated to fit task token budget");

        return mb_substr($text, 0, $maxChars)."\n[Lesson plan truncated due to size limits]";
    }
}
