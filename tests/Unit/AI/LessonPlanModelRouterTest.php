<?php

namespace Tests\Unit\AI;

use App\AI\Routing\LessonPlanModelRouter;
use Tests\TestCase;

class LessonPlanModelRouterTest extends TestCase
{
    protected LessonPlanModelRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new LessonPlanModelRouter;
    }

    public function test_math_subject_routes_to_reasoning(): void
    {
        $route = $this->router->resolve(['subject' => 'Mathematics']);

        $this->assertSame(LessonPlanModelRouter::MODE_REASONING, $route['mode']);
        $this->assertFalse($route['manual']);
        $this->assertNotEmpty($route['reasons']);
        $this->assertNotEmpty($route['provider']);
        $this->assertNotEmpty($route['model']);
    }

    public function test_science_inquiry_goals_route_to_reasoning(): void
    {
        $route = $this->router->resolve([
            'subject' => 'General Science',
            'objective' => 'Students conduct an experiment to test their hypothesis',
            'strategies' => 'inquiry-based investigation and data analysis',
        ]);

        $this->assertSame(LessonPlanModelRouter::MODE_REASONING, $route['mode']);
    }

    public function test_literature_creative_writing_routes_to_expressive(): void
    {
        $route = $this->router->resolve([
            'subject' => 'English',
            'objective' => 'Write an original short story using narrative techniques',
            'strategies' => 'storytelling and creative writing workshop',
        ]);

        $this->assertSame(LessonPlanModelRouter::MODE_EXPRESSIVE, $route['mode']);
    }

    public function test_remedial_goals_route_to_structured(): void
    {
        $route = $this->router->resolve([
            'subject' => 'Mathematics',
            'objective' => 'Remedial drill on basic addition facts for struggling learners',
            'strategies' => 'guided practice with step-by-step scaffolding',
        ]);

        // Explicit remedial signals win ties via priority ordering.
        $this->assertSame(LessonPlanModelRouter::MODE_STRUCTURED, $route['mode']);
    }

    public function test_generic_lesson_routes_to_balanced_baseline(): void
    {
        $route = $this->router->resolve([
            'subject' => 'Edukasyon sa Pagpapakatao',
            'objective' => 'Discuss the value of honesty',
        ]);

        $this->assertSame(LessonPlanModelRouter::MODE_BALANCED, $route['mode']);
    }

    public function test_manual_mode_override_wins_over_signals(): void
    {
        $route = $this->router->resolve(
            ['subject' => 'Mathematics', 'objective' => 'Solve quadratic equations'],
            'lesson_plan_suggestion',
            LessonPlanModelRouter::MODE_EXPRESSIVE,
        );

        $this->assertSame(LessonPlanModelRouter::MODE_EXPRESSIVE, $route['mode']);
        $this->assertTrue($route['manual']);
    }

    public function test_invalid_manual_mode_falls_back_to_auto_routing(): void
    {
        $route = $this->router->resolve(
            ['subject' => 'Science'],
            'lesson_plan_suggestion',
            'not-a-mode',
        );

        $this->assertSame(LessonPlanModelRouter::MODE_REASONING, $route['mode']);
        $this->assertFalse($route['manual']);
    }

    public function test_modes_lists_all_four_modes_with_labels(): void
    {
        $modes = $this->router->modes();

        $this->assertSame(
            ['structured', 'reasoning', 'expressive', 'balanced'],
            array_keys($modes),
        );
        foreach ($modes as $mode) {
            $this->assertNotEmpty($mode['label']);
        }
    }

    public function test_baseline_route_is_balanced(): void
    {
        $route = $this->router->baselineRoute();

        $this->assertSame(LessonPlanModelRouter::MODE_BALANCED, $route['mode']);
        $this->assertFalse($route['manual']);
    }

    public function test_per_mode_model_config_is_honoured(): void
    {
        config([
            'ai.lesson_plan_modes.reasoning.provider' => 'deepseek',
            'ai.lesson_plan_modes.reasoning.model' => 'deepseek-reasoner',
        ]);

        $route = $this->router->resolve(['subject' => 'Physics']);

        $this->assertSame('deepseek', $route['provider']);
        $this->assertSame('deepseek-reasoner', $route['model']);
    }

    public function test_mode_inherits_task_then_default_model(): void
    {
        config([
            'ai.lesson_plan_modes.reasoning.provider' => '',
            'ai.lesson_plan_modes.reasoning.model' => '',
            'ai.tasks.lesson_plan_suggestion' => ['provider' => 'openai', 'model' => 'gpt-4o-mini'],
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
        ]);

        $route = $this->router->resolve(['subject' => 'Chemistry']);

        $this->assertSame('openai', $route['provider']);
        $this->assertSame('gpt-4o-mini', $route['model']);
    }

    public function test_directives_exist_for_specialized_modes_only(): void
    {
        $this->assertNotEmpty(LessonPlanModelRouter::directivesFor('reasoning'));
        $this->assertNotEmpty(LessonPlanModelRouter::directivesFor('expressive'));
        $this->assertNotEmpty(LessonPlanModelRouter::directivesFor('structured'));
        $this->assertSame('', LessonPlanModelRouter::directivesFor('balanced'));
    }
}
