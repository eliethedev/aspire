<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAiUsageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function log(array $overrides = []): AiUsageLog
    {
        $attributes = array_merge([
            'stage' => 'feedback',
            'provider' => 'gemini',
            'model' => 'gemini-3.6-flash',
            'prompt_tokens' => 1000,
            'response_tokens' => 500,
            'total_tokens' => 1500,
            'response_time_ms' => 800,
            'success' => true,
            'fallback_used' => false,
            'user_id' => User::factory()->create()->id,
        ], $overrides);

        $log = new AiUsageLog($attributes);
        if (array_key_exists('created_at', $overrides)) {
            $log->created_at = $overrides['created_at'];
        }
        $log->save();

        return $log;
    }

    public function test_non_admin_is_blocked(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('admin.ai-usage.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_dashboard(): void
    {
        $this->log();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.index'));

        $response->assertOk();
        $response->assertSee('Estimated Spend');
        $response->assertSee('Detailed Call Log');
    }

    public function test_empty_state_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.index'))
            ->assertOk()
            ->assertSee('No AI usage recorded yet');
    }

    public function test_user_filter_scopes_call_log(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $this->log(['user_id' => $userA->id, 'provider' => 'gemini', 'model' => 'gemini-3.6-flash']);
        $this->log(['user_id' => $userB->id, 'provider' => 'openai', 'model' => 'gpt-4o']);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.index', ['user_id' => $userA->id]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match_all('/data-user-id="'.$userA->id.'"/', $html));
        $this->assertSame(0, preg_match_all('/data-user-id="'.$userB->id.'"/', $html));
    }

    public function test_success_filter_excludes_failed_calls(): void
    {
        $userA = User::factory()->create();
        $this->log(['user_id' => $userA->id, 'success' => true]);
        $this->log(['user_id' => $userA->id, 'success' => false, 'error_message' => 'quota exhausted']);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.index', ['success' => '1']))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match_all('/data-user-id="'.$userA->id.'"/', $html));
        $this->assertStringNotContainsString('quota exhausted', $html);
    }

    public function test_date_range_filter_applies(): void
    {
        $userA = User::factory()->create();
        $this->log(['user_id' => $userA->id, 'created_at' => now()->subDays(10)]);
        $this->log(['user_id' => $userA->id, 'created_at' => now()->subDays(60)]);

        $from = now()->subDays(20)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.index', ['date_from' => $from, 'date_to' => $to]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match_all('/data-user-id="'.$userA->id.'"/', $html));
    }

    public function test_user_drilldown_is_scoped_to_that_user(): void
    {
        $userA = User::factory()->create(['name' => 'Alpha Teacher']);
        $userB = User::factory()->create(['name' => 'Beta Supervisor']);
        $this->log(['user_id' => $userA->id]);
        $this->log(['user_id' => $userB->id]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.ai-usage.users.show', $userA))
            ->assertOk()
            ->assertSee('Alpha Teacher')
            ->getContent();

        $this->assertStringNotContainsString('Beta Supervisor', $html);
        $this->assertSame(1, preg_match_all('/data-user-id="'.$userA->id.'"/', $html));
    }
}