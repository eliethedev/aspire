<?php

namespace App\AI\Routing;

/**
 * Goal-driven model router for the lesson-plan generation workflow.
 *
 * Inspects a teacher's lesson context (subject, objectives, instructional
 * strategies, materials, assessment) and maps it to the generation mode
 * whose strengths best fit the pedagogical goal:
 *
 * - reasoning   → Math/Science inquiry, problem solving, data reasoning.
 * - expressive  → Language, literature, creative writing and the arts.
 * - structured  → Remedial, foundational and highly scaffolded instruction.
 * - balanced    → General baseline; also the automatic failover target.
 *
 * Each mode resolves to a [provider, model] pair. Empty per-mode config
 * inherits the task-level override (ai.tasks.*) and then the global
 * default, so routing works out of the box while administrators can
 * assign specialized models per mode via ai.lesson_plan_modes.
 */
class LessonPlanModelRouter
{
    public const MODE_REASONING = 'reasoning';

    public const MODE_EXPRESSIVE = 'expressive';

    public const MODE_STRUCTURED = 'structured';

    public const MODE_BALANCED = 'balanced';

    /**
     * Subject keywords per mode. A subject hit counts double — the
     * subject is the strongest single routing signal.
     *
     * @var array<string, string[]>
     */
    protected const SUBJECT_KEYWORDS = [
        self::MODE_REASONING => [
            'math', 'mathematics', 'algebra', 'geometry', 'trigonometry',
            'statistics', 'probability', 'calculus',
            'science', 'physics', 'chemistry', 'biology',
            'general science', 'integrated science', 'investigatory',
            'research', 'robotics', 'ict', 'computer', 'programming',
            'coding', 'technology', 'engineering',
        ],
        self::MODE_EXPRESSIVE => [
            'english', 'filipino', 'literature', 'language', 'reading',
            'writing', 'creative writing', 'journalism', 'speech',
            'theater', 'theatre', 'drama', 'arts', 'music', 'humanities',
            'malikhaing pagsulat', 'panitikan',
        ],
        self::MODE_STRUCTURED => [
            'mother tongue', 'mtb', 'phonics', 'reading readiness',
            'remedial', 'remediation', 'sped', 'special education',
            'als', 'literacy', 'numeracy',
        ],
        self::MODE_BALANCED => [],
    ];

    /**
     * Pedagogical-goal keywords per mode, matched against the lesson
     * objective, strategies, materials and assessment text.
     *
     * @var array<string, string[]>
     */
    protected const GOAL_KEYWORDS = [
        self::MODE_REASONING => [
            'inquiry', 'inquire', 'experiment', 'investigation', 'investigatory',
            'problem solving', 'problem-solving', 'solve', 'hypothesis',
            'scientific method', 'data analysis', 'critical thinking',
            'reasoning', 'discovery', 'laboratory', 'lab activity',
            'computation', 'prove', 'derive',
        ],
        self::MODE_EXPRESSIVE => [
            'creative writing', 'story', 'storytelling', 'poem', 'poetry',
            'essay', 'narrative', 'role play', 'role-play', 'drama',
            'debate', 'speech', 'declamation', 'presentation',
            'expressive', 'art ', 'draw', 'interpretive', 'journal',
            'storybook', 'read-aloud',
        ],
        self::MODE_STRUCTURED => [
            'drill', 'practice', 'repetition', 'remediation', 'remedial',
            'foundational', 'basics', 'basic skills',
            'step-by-step', 'step by step', 'scaffold', 'guided practice',
            'mastery', 'intervention', 'catch-up', 'catch up',
            'fluency', 'decoding', 'phonemic', 'differentiated instruction',
            'least mastered', 'least-learned',
        ],
        self::MODE_BALANCED => [],
    ];

    /**
     * Tie-break priority when two modes score equally. Remedial safety
     * first: an explicit structured signal should never lose to style.
     *
     * @var string[]
     */
    protected const PRIORITY = [
        self::MODE_STRUCTURED,
        self::MODE_REASONING,
        self::MODE_EXPRESSIVE,
        self::MODE_BALANCED,
    ];

    /**
     * Formatting strengths each mode's system prompt should exploit.
     *
     * @var array<string, string>
     */
    protected const MODE_DIRECTIVES = [
        self::MODE_REASONING => 'Reasoning focus: structure gaps and suggestions as numbered multi-step chains; '
            .'use inquiry scaffolds (Claim → Evidence → Reasoning) and name the thinking step each suggestion strengthens.',
        self::MODE_EXPRESSIVE => 'Expressive focus: use rich, specific language in every suggestion; '
            .'favor expressive outputs (narratives, performances, writer’s workshop, speech) where pedagogically sound.',
        self::MODE_STRUCTURED => 'Structured focus: break every suggestion into small sequential steps with a check for understanding after each; '
            .'prefer highly scaffolded activities and state explicit mastery criteria.',
        self::MODE_BALANCED => '',
    ];

    /**
     * Resolve the generation route for a lesson context.
     *
     * @param  array{subject?: ?string, objective?: ?string, strategies?: ?string, materials?: ?string, assessment?: ?string}  $signals
     * @param  string  $stage  AI task stage (for model inheritance lookups)
     * @param  string|null  $manualMode  Validated manual override; wins over auto-routing
     * @return array{mode: string, label: string, description: string, provider: string, model: string, temperature: float, reasons: string[], manual: bool}
     */
    public function resolve(array $signals, string $stage = 'lesson_plan_suggestion', ?string $manualMode = null): array
    {
        if ($manualMode !== null && $this->isValidMode($manualMode)) {
            $route = $this->buildRoute($manualMode, $stage);
            $route['manual'] = true;
            $route['reasons'] = ['Manually selected by the user.'];

            return $route;
        }

        $subject = mb_strtolower((string) ($signals['subject'] ?? ''));
        $goals = mb_strtolower(implode("\n", [
            (string) ($signals['objective'] ?? ''),
            (string) ($signals['strategies'] ?? ''),
            (string) ($signals['materials'] ?? ''),
            (string) ($signals['assessment'] ?? ''),
        ]));

        $scores = [];
        $hits = [];
        foreach (self::PRIORITY as $mode) {
            if ($mode === self::MODE_BALANCED) {
                $scores[$mode] = 0;
                $hits[$mode] = [];
                continue;
            }

            $modeHits = [];
            foreach (self::SUBJECT_KEYWORDS[$mode] as $keyword) {
                if ($subject !== '' && str_contains($subject, $keyword)) {
                    $modeHits[] = "subject matches '{$keyword}'";
                }
            }
            $subjectHits = count(array_filter(
                $modeHits,
                fn (string $hit): bool => str_starts_with($hit, 'subject')
            ));

            foreach (self::GOAL_KEYWORDS[$mode] as $keyword) {
                if ($goals !== '' && str_contains($goals, $keyword)) {
                    $modeHits[] = "lesson goals mention '{$keyword}'";
                }
            }
            $goalHits = count($modeHits) - $subjectHits;

            // Subject is the strongest single signal: count it double.
            $scores[$mode] = $subjectHits * 2 + $goalHits;
            $hits[$mode] = $modeHits;
        }

        $best = self::MODE_BALANCED;
        $bestScore = 0;
        foreach (self::PRIORITY as $mode) {
            if ($scores[$mode] > $bestScore) {
                $best = $mode;
                $bestScore = $scores[$mode];
            }
        }

        $route = $this->buildRoute($best, $stage);
        $route['manual'] = false;
        $route['reasons'] = $best === self::MODE_BALANCED
            ? ['No specialized subject or goal signals detected; using the general baseline.']
            : array_slice($hits[$best], 0, 3);

        return $route;
    }

    /**
     * Stable baseline route used for failover and generic lessons.
     *
     * @return array{mode: string, label: string, description: string, provider: string, model: string, temperature: float, reasons: string[], manual: bool}
     */
    public function baselineRoute(string $stage = 'lesson_plan_suggestion'): array
    {
        $route = $this->buildRoute(self::MODE_BALANCED, $stage);
        $route['manual'] = false;
        $route['reasons'] = ['Baseline model (automatic failover target).'];

        return $route;
    }

    /**
     * Modes available for display and manual selection.
     *
     * @return array<string, array{label: string, description: string}>
     */
    public function modes(): array
    {
        $modes = [];
        foreach (self::PRIORITY as $mode) {
            $config = config("ai.lesson_plan_modes.{$mode}", []);
            $modes[$mode] = [
                'label' => (string) ($config['label'] ?? ucfirst($mode)),
                'description' => (string) ($config['description'] ?? ''),
            ];
        }

        return $modes;
    }

    public function isValidMode(?string $mode): bool
    {
        return is_string($mode) && in_array($mode, self::PRIORITY, true);
    }

    /**
     * @return array{mode: string, label: string, description: string, provider: string, model: string, temperature: float, reasons: string[], manual: bool}
     */
    protected function buildRoute(string $mode, string $stage): array
    {
        $modeConfig = config("ai.lesson_plan_modes.{$mode}", []);
        $taskConfig = config("ai.tasks.{$stage}", []);

        $provider = trim((string) ($modeConfig['provider'] ?? ''));
        if ($provider === '') {
            $provider = trim((string) (is_array($taskConfig) ? ($taskConfig['provider'] ?? '') : ''));
        }
        if ($provider === '') {
            $provider = (string) config('ai.provider', 'gemini');
        }

        $model = trim((string) ($modeConfig['model'] ?? ''));
        if ($model === '') {
            $model = trim((string) (is_array($taskConfig) ? ($taskConfig['model'] ?? '') : ''));
        }
        if ($model === '') {
            $model = (string) config('ai.models.default', 'gemini-3.6-flash');
        }

        $temperature = $modeConfig['temperature']
            ?? (is_array($taskConfig) ? ($taskConfig['temperature'] ?? null) : null)
            ?? config('ai.generation.temperature', 0.5);

        return [
            'mode' => $mode,
            'label' => (string) ($modeConfig['label'] ?? ucfirst($mode)),
            'description' => (string) ($modeConfig['description'] ?? ''),
            'provider' => $provider,
            'model' => $model,
            'temperature' => (float) $temperature,
            'reasons' => [],
            'manual' => false,
        ];
    }

    /**
     * Prompt-engineering directives exploiting the active mode's
     * formatting strengths. Empty for the balanced baseline.
     */
    public static function directivesFor(string $mode): string
    {
        return self::MODE_DIRECTIVES[$mode] ?? '';
    }
}
