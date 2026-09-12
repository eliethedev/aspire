<?php

namespace App\AI\Services;

use App\AI\Prompts\PreObservationPrompt;
use App\AI\Routing\LessonPlanModelRouter;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PreObservationService extends AIService
{
    protected string $stage = 'pre_observation';

    /**
     * Routing metadata for the most recent generateInsights() call.
     *
     * @var array{mode: string, label: string, provider: ?string, model: ?string, manual: bool, fallback_used: bool, reasons: string[]}|null
     */
    protected ?array $lastRouting = null;

    public function __construct(
        \App\AI\Contracts\AIServiceInterface $provider,
        \App\AI\RAG\PPSTRubricRepository $rubrics,
        protected DocumentExtractorService $documentExtractor
    ) {
        parent::__construct($provider, $rubrics);
    }

    public function generateInsights(Observation $observation, bool $templateFallback = true, ?string $manualMode = null): ?string
    {
        $this->lastRouting = null;

        $observation->loadMissing(['observee.user', 'preObservationPlanning']);
        $teacherName = $observation->observee?->user?->name ?? 'Unknown';
        $planning = $observation->preObservationPlanning;

        $lessonPlanContent = '';
        $lessonPlanFile = $planning?->lesson_plan_file;
        if ($lessonPlanFile && Storage::disk('public')->exists($lessonPlanFile)) {
            $fullPath = Storage::disk('public')->path($lessonPlanFile);
            $lessonPlanContent = $this->documentExtractor->extractText($fullPath);
        }

        $rubricContext = $this->rubrics->getRelevantForLessonPlan(
            $observation->subject ?? '',
            $observation->grade_level ?? ''
        );

        // Dynamic goal parsing: route to the specialized generation mode
        // matching the teacher's subject and lesson goals before the call.
        $router = app(LessonPlanModelRouter::class);
        $route = $router->resolve([
            'subject' => $observation->subject,
            'objective' => $planning?->objective,
            'strategies' => $planning?->teaching_strategies,
            'materials' => $planning?->materials,
            'assessment' => $planning?->assessment_methods,
        ], $this->stage, $manualMode);

        $prompt = PreObservationPrompt::build([
            'teacher_name' => $teacherName,
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'school_year' => $observation->school_year ?? 'N/A',
            'observation_type' => $observation->observation_type ?? 'N/A',
            'stage' => $observation->stage ?? 'N/A',
            'objective' => $planning?->objective ?? 'Not specified',
            'strategies' => $planning?->teaching_strategies ?? 'Not specified',
            'materials' => $planning?->materials ?? 'Not specified',
            'assessment' => $planning?->assessment_methods ?? 'Not specified',
            'lesson_plan_content' => $lessonPlanContent,
            'lesson_plan_file' => $lessonPlanFile,
            'rubrics' => $rubricContext,
            'mode_label' => $route['label'],
            'mode_directives' => LessonPlanModelRouter::directivesFor($route['mode']),
        ]);

        $result = $this->generate(
            $prompt,
            ['temperature' => $route['temperature']],
            ['provider' => $route['provider'], 'model' => $route['model']],
            $observation->id,
        );

        $this->lastRouting = null;

        if ($result) {
            $this->lastRouting = $this->routingMeta($route);

            return $result;
        }

        if ($templateFallback && config('ai.fallback', true)) {
            return $this->buildFallbackInsights(
                $teacherName, $planning, $lessonPlanContent,
                $observation->subject ?? '', $observation->grade_level ?? ''
            );
        }

        return null;
    }

    /**
     * Routing metadata for the most recent generateInsights() call.
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
    protected function routingMeta(array $route): array
    {
        $run = $this->getLastRunMeta();

        return [
            'mode' => $route['mode'],
            'label' => $route['label'],
            'provider' => $run['provider'] ?? $route['provider'],
            'model' => $run['model'] ?? $route['model'],
            'manual' => $route['manual'],
            'fallback_used' => (bool) ($run['fallback_used'] ?? false),
            'reasons' => $route['reasons'],
        ];
    }

    public function generatePreConferenceSuggestions(Observation $observation, bool $templateFallback = true): ?array
    {
        $observation->loadMissing(['observee.user', 'preObservationPlanning']);
        $planning = $observation->preObservationPlanning;
        $teacherName = $observation->observee?->user?->name ?? 'Unknown';
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $objective = $planning?->objective ?? 'Not specified';
        $strategies = $planning?->teaching_strategies ?? 'Not specified';
        $materials = $planning?->materials ?? 'Not specified';
        $assessment = $planning?->assessment_methods ?? 'Not specified';

        if ($this->isAvailable()) {
            $lessonPlanContent = '';
            $lessonPlanFile = $planning?->lesson_plan_file;
            if ($lessonPlanFile && Storage::disk('public')->exists($lessonPlanFile)) {
                $fullPath = Storage::disk('public')->path($lessonPlanFile);
                $lessonPlanContent = $this->documentExtractor->extractText($fullPath);
            }

            $lessonPlanSection = $lessonPlanContent
                ? "--- Lesson Plan Content ---\n{$lessonPlanContent}\n\n"
                : '';

            $prompt = <<<PROMPT
You are an expert instructional coach. Based on the pre-observation data below, generate two concise text blocks for a pre-conference form.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Objective: {$objective}
Teaching Strategies: {$strategies}
Materials: {$materials}
Assessment Methods: {$assessment}

{$lessonPlanSection}Return JSON with exactly two keys:

1. "discussion_notes" — 3-4 bullet points covering key discussion topics: teaching strategies, learner diversity, assessment methods, and any support the teacher may need.

2. "finalized_focus" — 2-3 concise focus areas agreed upon for the classroom observation, based on the objective and strategies.

Keep both concise and actionable. No preamble. Write as plain text only: no markdown symbols (no **, no *, no backticks).
PROMPT;

            $result = $this->generateJson($prompt, [
                'temperature' => 0.3,
                'max_output_tokens' => 4096,
            ]);

            if ($result) {
                // Normalize model output (arrays or strings) to consistent,
                // textarea-friendly bullet strings.
                $asText = static function ($value) {
                    if (is_array($value)) {
                        return implode("\n", array_map(
                            fn ($item) => '- '.trim((string) $item),
                            $value
                        ));
                    }

                    return is_string($value) ? trim($value) : (string) $value;
                };

                return [
                    'discussion_notes' => $asText($result['discussion_notes'] ?? ''),
                    'finalized_focus' => $asText($result['finalized_focus'] ?? ''),
                ];
            }
        }

        if ($templateFallback && config('ai.fallback', true)) {
            return $this->buildFallbackPreConferenceSuggestions(
                $teacherName, $subject, $gradeLevel, $objective, $strategies, $materials, $assessment
            );
        }

        return null;
    }

    protected function buildFallbackPreConferenceSuggestions(
        string $teacherName,
        string $subject,
        string $gradeLevel,
        string $objective,
        string $strategies,
        string $materials,
        string $assessment
    ): array {
        $bullets = [];

        if ($objective && $objective !== 'Not specified') {
            $bullets[] = "Review the lesson objective: \"{$objective}\". Discuss how the teacher will communicate this objective to students and check for understanding throughout the lesson.";
        } else {
            $bullets[] = "Clarify the central lesson objective and how it aligns with the curriculum standards for {$subject}.";
        }

        if ($strategies && $strategies !== 'Not specified') {
            $bullets[] = "Discuss the planned teaching strategies: {$strategies}. Explore how these strategies address different learner needs and promote active engagement.";
        } else {
            $bullets[] = "Discuss the instructional strategies to be used and how they will engage students at varying ability levels.";
        }

        if ($materials && $materials !== 'Not specified') {
            $bullets[] = "Review the materials: {$materials}. Confirm they are ready and appropriate for the grade level and learning objectives.";
        } else {
            $bullets[] = "Check that all necessary instructional materials are prepared and accessible.";
        }

        if ($assessment && $assessment !== 'Not specified') {
            $bullets[] = "Examine the assessment methods: {$assessment}. Discuss how the teacher will use formative assessment to adjust instruction in real-time.";
        } else {
            $bullets[] = "Discuss how student learning will be assessed during and after the lesson.";
        }

        $bullets[] = "Identify any support the teacher may need to deliver this lesson effectively.";
        $bullets[] = "Agree on specific observation focus areas and what the supervisor should look for.";

        $focusAreas = [];
        if ($objective && $objective !== 'Not specified') {
            $focusAreas[] = "Alignment of lesson activities with the stated objective";
        }
        if ($strategies && $strategies !== 'Not specified') {
            $focusAreas[] = "Effectiveness of {$strategies} in engaging students";
        }
        $focusAreas[] = "Student participation and responsiveness to instruction";
        $focusAreas[] = "Classroom management and lesson pacing";

        return [
            'discussion_notes' => implode("\n", array_map(fn($b) => "- {$b}", $bullets)),
            'finalized_focus' => implode("\n", array_map(fn($f, $i) => ($i + 1) . ". {$f}", $focusAreas, array_keys($focusAreas))),
        ];
    }

    protected function buildFallbackInsights(
        string $teacherName,
        $planning,
        string $lessonPlanContent,
        string $subject,
        string $gradeLevel
    ): string {
        $insights = [];
        $insights[] = "**Pre-Observation Insights for {$teacherName}**";
        $insights[] = "";

        $objective = $planning?->objective ?? 'Not specified';
        $strategies = $planning?->teaching_strategies ?? 'Not specified';
        $materials = $planning?->materials ?? 'Not specified';
        $assessment = $planning?->assessment_methods ?? 'Not specified';

        $goals = [];
        if ($objective && $objective !== 'Not specified') {
            $goals[] = "The lesson centers on the objective: \"{$objective}\".";
        }
        if ($strategies && $strategies !== 'Not specified') {
            $goals[] = "Instructional focus employs {$strategies}.";
        }
        if ($materials && $materials !== 'Not specified') {
            $goals[] = "Learning resources include {$materials}.";
        }
        if ($assessment && $assessment !== 'Not specified') {
            $goals[] = "Student understanding will be measured through {$assessment}.";
        }
        if ($goals !== []) {
            $insights[] = "**Lesson Plan Goals & Focus:** " . implode(' ', $goals) . " The supervisor should be ready to observe how these elements come together during the lesson.";
            $insights[] = "";
        }

        if ($objective && $objective !== 'Not specified') {
            $insights[] = "**Key Focus Areas:** The lesson objective \"{$objective}\" should be the primary focus during observation. Pay attention to how the teacher communicates this objective to students and whether lesson activities align with achieving it.";
        } else {
            $insights[] = "**Key Focus Areas:** Review the lesson plan to identify the central learning objectives and observe how well the lesson activities support student mastery of those objectives.";
        }

        if ($strategies && $strategies !== 'Not specified') {
            $insights[] = "**Teaching Strategies to Observe:** The teacher plans to use: {$strategies}. Watch for the effectiveness of these strategies in engaging students and promoting understanding.";
        } else {
            $insights[] = "**Teaching Strategies:** Observe the instructional methods used, noting student engagement levels and the variety of techniques employed.";
        }

        if ($materials && $materials !== 'Not specified') {
            $insights[] = "**Materials & Resources:** Prepare to evaluate how effectively the teacher uses: {$materials}. Check if materials are appropriate for the grade level and learning objectives.";
        }

        if ($assessment && $assessment !== 'Not specified') {
            $insights[] = "**Assessment Methods:** The teacher plans to assess learning through: {$assessment}. Observe whether assessment is integrated throughout the lesson and provides timely feedback.";
        }

        $insights[] = "**Pre-Conference Discussion Points:**";
        $insights[] = "- Ask the teacher how they will differentiate instruction for diverse learners.";
        $insights[] = "- Discuss how success will be measured during this lesson.";
        $insights[] = "- Explore what support the teacher feels they need to deliver this lesson effectively.";
        $insights[] = "";

        if ($lessonPlanContent) {
            $hasObjectives = stripos($lessonPlanContent, 'objective') !== false;
            $hasActivities = stripos($lessonPlanContent, 'activity') !== false || stripos($lessonPlanContent, 'procedure') !== false;
            $hasAssessment = stripos($lessonPlanContent, 'assessment') !== false || stripos($lessonPlanContent, 'evaluation') !== false;

            if ($hasObjectives && $hasActivities && $hasAssessment) {
                $insights[] = "**Lesson Plan Completeness:** The uploaded lesson plan appears comprehensive with clear objectives, activities, and assessment components.";
            } elseif (str_word_count($lessonPlanContent) < 100) {
                $insights[] = "**Lesson Plan Note:** The uploaded lesson plan is brief. Consider discussing with the teacher whether additional details are available to better understand the lesson flow.";
            }
        }

        $insights[] = "**Potential Challenges:** Monitor time management, student comprehension checks, and the transition between lesson phases. These are common areas where teachers may need support during {$subject} for Grade {$gradeLevel}.";

        return implode("\n", $insights);
    }
}
