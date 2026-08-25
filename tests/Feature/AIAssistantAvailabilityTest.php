<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AIAssistantAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeObservation(): array
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $observer = User::factory()->create(['role' => 'supervisor']);

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create();

        return [$observer, $observation];
    }

    public function test_ai_insights_returns_friendly_unavailable_payload_when_disabled(): void
    {
        [$observer, $observation] = $this->makeObservation();

        config([
            'ai.enabled' => false,
            'ai.fallback' => true,
            'ai.rate_limits.per_minute' => 30,
        ]);

        $response = $this->actingAs($observer)
            ->postJson(route('supervisor.observations.generate-ai-insights', $observation));

        $response->assertStatus(503);
        $response->assertJsonPath('reason', 'disabled');
        $response->assertJsonPath('manual_available', true);
        $this->assertStringContainsString("AI isn't available", $response->json('error'));
    }

    public function test_pre_conference_suggestions_no_longer_require_gemini_specifically(): void
    {
        [$observer, $observation] = $this->makeObservation();

        config([
            'ai.enabled' => false,
            'ai.rate_limits.per_minute' => 30,
        ]);

        $response = $this->actingAs($observer)
            ->postJson(route('supervisor.observations.generate-ai-suggestions', $observation));

        // Previously a 400 "Gemini API is not configured"; now a friendly,
        // provider-agnostic unavailability response with manual option.
        $response->assertStatus(503);
        $response->assertJsonPath('reason', 'disabled');
        $response->assertJsonPath('manual_available', true);
        $this->assertStringContainsString("AI isn't available", $response->json('error'));
    }

    public function test_ai_comparison_returns_friendly_unavailable_payload_when_disabled(): void
    {
        [$observer, $observation] = $this->makeObservation();

        config([
            'ai.enabled' => false,
            'ai.rate_limits.per_minute' => 30,
        ]);

        $response = $this->actingAs($observer)
            ->postJson(route('supervisor.observations.generate-ai-comparison', $observation));

        $response->assertStatus(503);
        $response->assertJsonPath('reason', 'disabled');
        $response->assertJsonPath('manual_available', true);
    }

    public function test_rate_limited_task_returns_friendly_429_with_manual_option(): void
    {
        [$observer, $observation] = $this->makeObservation();

        config([
            'ai.enabled' => true,
            'ai.provider' => 'ollama',
            'ai.providers.gemini.enabled' => false,
            'ai.fallback_chain' => ['ollama'],
            'ai.rate_limits.per_minute' => 30,
            'ai.rate_limits.operations.pre_observation' => ['limit' => 1, 'decay' => 60],
        ]);

        // Make the availability probe succeed so the chain is non-empty.
        Http::fake(['*/api/tags' => Http::response(['models' => []], 200)]);

        // Exhaust the per-task service limiter before the request.
        RateLimiter::hit("ai:service:pre_observation:{$observer->id}", 60);

        $response = $this->actingAs($observer)
            ->postJson(route('supervisor.observations.generate-ai-insights', $observation));

        $response->assertStatus(429);
        $response->assertJsonPath('reason', 'rate_limit');
        $response->assertJsonPath('manual_available', true);
        $response->assertJsonPath('retry_after', fn ($value) => (int) $value > 0);
        $this->assertStringContainsString('usage limit', $response->json('error'));
    }

    public function test_supervisor_can_save_manual_insights_without_ai(): void
    {
        [$observer, $observation] = $this->makeObservation();

        config(['ai.enabled' => false]);

        $this->actingAs($observer)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation), [
                '_token' => csrf_token(),
                'ai_insights' => 'My own handwritten insights: focus on questioning techniques.',
            ])
            ->assertRedirect();

        // ai_insights is cast to array on the model, so a plain string is stored JSON-encoded.
        $this->assertDatabaseHas('pre_observation_plannings', [
            'observation_id' => $observation->id,
            'ai_insights' => json_encode('My own handwritten insights: focus on questioning techniques.'),
        ]);
    }
}
