<?php

namespace App\AI\Services;

use App\AI\Prompts\LessonPlanSummaryPrompt;
use App\Models\Observation;
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
        ?\App\AI\Providers\AIProviderManager $manager = null,
    ) {
        parent::__construct($provider, $rubrics, $manager);
    }

    public function summarize(Observation $observation): ?array
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
            $lessonPlanContent = $this->capLessonPlanText(
                $this->documentExtractor->extractText($fullPath)
            );
        }

        if (trim($lessonPlanContent) === '') {
            return null;
        }

        // No rubric context required for this task — summary stays focused
        // on the lesson plan itself.
        $prompt = LessonPlanSummaryPrompt::build([
            'teacher_name' => $observation->observee?->user?->name ?? 'Unknown',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'lesson_plan_content' => $lessonPlanContent,
        ]);

        $data = $this->generateJson($prompt);

        if (! $this->validateJsonResponse($data, ['summary'])) {
            return null;
        }

        return $data;
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
