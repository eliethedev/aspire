<?php

namespace Tests\Feature;

use App\AI\Services\LessonPlanSuggestionService;
use App\AI\Services\LessonPlanSummaryService;
use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AITaskRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // These tests target AI guard/rate-limit logic, not profile
        // completeness (same precedent as OfflineSyncTest/NotificationLinksTest).
        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);
    }

    public function test_guest_cannot_access_goal_specific_ai_tasks(): void
    {
        $observation = Observation::factory()->create();

        $this->post(route('supervisor.ai-tasks.lesson-plan-suggestions', $observation))
            ->assertRedirect(route('login'));

        $this->post(route('supervisor.ai-tasks.overall-recommendation', $observation))
            ->assertRedirect(route('login'));
    }

    public function test_observer_from_another_school_is_forbidden(): void
    {
        $teacherSchool = School::factory()->create();
        $observerSchool = School::factory()->create();

        $teacherUser = User::factory()->create(['role' => 'teacher', 'school_id' => $teacherSchool->id]);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $observer = User::factory()->create(['role' => 'supervisor', 'school_id' => $observerSchool->id]);

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create();

        config(['ai.enabled' => true]);

        $this->actingAs($observer)
            ->postJson(route('supervisor.ai-tasks.overall-recommendation', $observation))
            ->assertStatus(403);
    }

    public function test_global_per_user_rate_limit_returns_429_with_retry_after(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $observer = User::factory()->create(['role' => 'supervisor']);

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create();

        // Tight global limit so the test stays fast; task limits are separate keys.
        config([
            'ai.enabled' => true,
            'ai.rate_limits.per_minute' => 2,
            'ai.rate_limits.per_hour' => 200,
        ]);

        // Stub the AI services so requests complete instantly. Real provider
        // calls take ~100s each (timeouts), which would outlast the 60s rate
        // window and make the third request pass instead of 429.
        $this->mock(LessonPlanSuggestionService::class, function ($mock) {
            $mock->shouldReceive('suggest')->andReturn(['suggestions' => ['Keep it up']]);
            $mock->shouldReceive('getLastRouting')->andReturn(null);
        });
        $this->mock(LessonPlanSummaryService::class, function ($mock) {
            $mock->shouldReceive('summarize')->andReturn(['summary' => 'Good plan']);
            $mock->shouldReceive('getLastRouting')->andReturn(null);
        });

        RateLimiter::clear("ai:global:{$observer->id}");
        RateLimiter::clear("ai:global-hourly:{$observer->id}");

        foreach ([1, 2] as $i) {
            $response = $this->actingAs($observer)->postJson(
                route('supervisor.ai-tasks.lesson-plan-suggestions', $observation)
            );
            $this->assertNotSame(429, $response->status(), "Request {$i} should not be limited");
        }

        $response = $this->actingAs($observer)->postJson(
            route('supervisor.ai-tasks.lesson-plan-summary', $observation)
        );

        $response->assertStatus(429);
        $response->assertJsonPath('retry_after', fn ($value) => (int) $value > 0);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    public function test_ai_disabled_rejects_requests_without_calling_providers(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $observer = User::factory()->create(['role' => 'supervisor']);

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create();

        config(['ai.enabled' => false, 'ai.rate_limits.per_minute' => 30]);

        $this->actingAs($observer)
            ->postJson(route('supervisor.ai-tasks.overall-recommendation', $observation))
            ->assertStatus(403);
    }
}
