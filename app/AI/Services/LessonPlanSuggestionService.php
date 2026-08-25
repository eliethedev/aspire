<?php

namespace App\AI\Services;

use App\AI\Prompts\LessonPlanSuggestionPrompt;
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
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    /**
     * @return array|null Validated suggestions payload, or null on failure
     */
    public function suggest(Observation $observation): ?array
    {
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

        $prompt = LessonPlanSuggestionPrompt::build([
            'teacher_name' => $observation->observee?->user?->name ?? 'Unknown',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'objective' => $planning?->objective ?? 'Not specified',
            'strategies' => $planning?->teaching_strategies ?? 'Not specified',
            'materials' => $planning?->materials ?? 'Not specified',
            'assessment' => $planning?->assessment_methods ?? 'Not specified',
            'lesson_plan_content' => $lessonPlanContent,
            'rubrics' => $relevant !== '' ? $relevant : $this->rubrics->getSystemContext(),
        ]);

        $data = $this->generateJson($prompt);

        if (! $this->validateJsonResponse($data, ['gaps', 'suggestions'])) {
            return null;
        }

        return $data;
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
