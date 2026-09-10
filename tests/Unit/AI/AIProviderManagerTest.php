<?php

namespace Tests\Unit\AI;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Providers\AIProviderManager;
use App\Models\AiUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeAIProvider implements AIServiceInterface, TracksTokenUsage
{
    public int $generateCalls = 0;

    public function __construct(
        protected string $name,
        protected bool $available = true,
        protected bool $succeeds = true,
        protected array $usage = ['input' => 10, 'output' => 20],
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getProviderName(): string
    {
        return $this->name;
    }

    public function getModelName(): string
    {
        return $this->name.'-model';
    }

    public function setModel(string $model): void
    {
        //
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        $this->generateCalls++;

        return $this->succeeds ? 'ok-text' : null;
    }

    public function generateJson(string $prompt, array $options = []): ?array
    {
        $this->generateCalls++;

        return $this->succeeds ? ['ok' => true] : null;
    }

    public function getLastUsage(): array
    {
        return $this->usage;
    }
}

class StubAIProviderManager extends AIProviderManager
{
    /** @var array<string, AIServiceInterface> */
    public array $fakes;

    public function __construct(array $fakes)
    {
        $this->fakes = $fakes;
    }

    public function createProvider(string $provider, string $model = ''): AIServiceInterface
    {
        if (isset($this->fakes[$provider])) {
            return $this->fakes[$provider];
        }

        return new FakeAIProvider($provider, available: false);
    }
}

class AIProviderManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function manager(array $fakes): StubAIProviderManager
    {
        // Tests boot with AI_ENABLED=false; routing/fallback behaviour under
        // test assumes the global kill-switch is open.
        config(['ai.enabled' => true]);

        return new StubAIProviderManager($fakes);
    }

    public function test_primary_provider_success_does_not_trigger_fallback(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $gemini = new FakeAIProvider('gemini', available: true, succeeds: true);
        $openai = new FakeAIProvider('openai', available: true, succeeds: true);

        $result = $this->manager(['gemini' => $gemini, 'openai' => $openai])
            ->run('feedback', 'prompt', [], json: false);

        $this->assertTrue($result['success']);
        $this->assertSame('gemini', $result['provider']);
        $this->assertFalse($result['fallback_used']);
        $this->assertSame(1, $gemini->generateCalls);
        $this->assertSame(0, $openai->generateCalls);
    }

    public function test_fallback_provider_used_when_primary_fails(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $gemini = new FakeAIProvider('gemini', available: true, succeeds: false);
        $openai = new FakeAIProvider('openai', available: true, succeeds: true);

        $result = $this->manager(['gemini' => $gemini, 'openai' => $openai])
            ->run('feedback', 'prompt', [], json: false);

        $this->assertTrue($result['success']);
        $this->assertSame('openai', $result['provider']);
        $this->assertTrue($result['fallback_used']);
        $this->assertSame(1, $gemini->generateCalls);
        $this->assertSame(1, $openai->generateCalls);
    }

    public function test_all_providers_failing_returns_failure(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $result = $this->manager([
            'gemini' => new FakeAIProvider('gemini', succeeds: false),
            'openai' => new FakeAIProvider('openai', succeeds: false),
        ])->run('final_report', 'prompt', []);

        $this->assertFalse($result['success']);
        $this->assertNull($result['text']);
        $this->assertStringContainsString('All providers failed', $result['error']);
    }

    public function test_unconfigured_providers_are_excluded_from_chain(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.fallback_chain' => ['gemini', 'openai', 'claude', 'deepseek'],
        ]);

        // Only gemini configured/available.
        $chain = $this->manager([
            'gemini' => new FakeAIProvider('gemini', available: true),
        ])->fallbackChain('feedback');

        $this->assertCount(1, $chain);
        $this->assertSame('gemini', $chain[0]['provider']);
    }

    public function test_chain_is_empty_when_no_provider_is_available(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
        ]);

        $chain = $this->manager([])->fallbackChain('feedback');

        $this->assertSame([], $chain);
    }

    public function test_task_config_overrides_default_provider_and_model(): void
    {
        config([
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
            'ai.tasks.lesson_plan_suggestion' => [
                'provider' => 'deepseek',
                'model' => 'deepseek-chat',
            ],
        ]);

        [$provider, $model] = $this->manager([])->resolveProviderAndModel('lesson_plan_suggestion');

        $this->assertSame('deepseek', $provider);
        $this->assertSame('deepseek-chat', $model);
    }

    public function test_stage_model_config_is_honoured_when_no_task_config(): void
    {
        config([
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
            'ai.models.feedback' => ['provider' => '', 'model' => 'gpt-4o-mini'],
        ]);

        [$provider, $model] = $this->manager([])->resolveProviderAndModel('feedback');

        $this->assertSame('gemini', $provider); // empty task/stage provider falls back to default
        $this->assertSame('gpt-4o-mini', $model);
    }

    public function test_usage_log_records_provider_and_fallback_flag(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $this->manager([
            'gemini' => new FakeAIProvider('gemini', succeeds: false),
            'openai' => new FakeAIProvider('openai', succeeds: true, usage: ['input' => 11, 'output' => 22]),
        ])->run('cot_indicator_analysis', 'prompt', [], json: true);

        $log = AiUsageLog::where('stage', 'cot_indicator_analysis')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('openai', $log->provider);
        $this->assertTrue($log->success);
        $this->assertTrue($log->fallback_used);
        $this->assertSame(11, $log->prompt_tokens);
        $this->assertSame(22, $log->response_tokens);

        // The failed primary attempt should also be recorded.
        $failed = AiUsageLog::where('stage', 'cot_indicator_analysis')->where('success', false)->first();
        $this->assertNotNull($failed);
        $this->assertSame('gemini', $failed->provider);
        $this->assertFalse($failed->fallback_used);
    }

    public function test_run_with_primary_prefers_routed_model(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $deepseek = new FakeAIProvider('deepseek', available: true, succeeds: true);
        $gemini = new FakeAIProvider('gemini', available: true, succeeds: true);

        $result = $this->manager(['deepseek' => $deepseek, 'gemini' => $gemini])
            ->runWithPrimary(
                'lesson_plan_suggestion',
                'prompt',
                ['provider' => 'deepseek', 'model' => 'deepseek-reasoner'],
                [],
                json: true,
            );

        $this->assertTrue($result['success']);
        $this->assertSame('deepseek', $result['provider']);
        $this->assertFalse($result['fallback_used']);
        $this->assertSame(1, $deepseek->generateCalls);
        $this->assertSame(0, $gemini->generateCalls);
    }

    public function test_run_with_primary_falls_back_when_routed_model_unavailable(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $gemini = new FakeAIProvider('gemini', available: true, succeeds: true);

        // Routed primary is not configured → skipped, baseline chain serves.
        $result = $this->manager(['gemini' => $gemini])
            ->runWithPrimary(
                'lesson_plan_suggestion',
                'prompt',
                ['provider' => 'deepseek', 'model' => 'deepseek-reasoner'],
                [],
                json: true,
            );

        $this->assertTrue($result['success']);
        $this->assertSame('gemini', $result['provider']);
        $this->assertSame(1, $gemini->generateCalls);
    }

    public function test_run_with_primary_fails_over_on_routed_model_error(): void
    {
        config([
            'ai.fallback' => true,
            'ai.provider' => 'gemini',
            'ai.models.default' => 'gemini-3.6-flash',
            'ai.fallback_chain' => ['gemini', 'openai'],
        ]);

        $deepseek = new FakeAIProvider('deepseek', available: true, succeeds: false);
        $gemini = new FakeAIProvider('gemini', available: true, succeeds: true);

        $result = $this->manager(['deepseek' => $deepseek, 'gemini' => $gemini])
            ->runWithPrimary(
                'lesson_plan_suggestion',
                'prompt',
                ['provider' => 'deepseek', 'model' => 'deepseek-reasoner'],
                [],
                json: true,
            );

        $this->assertTrue($result['success']);
        $this->assertSame('gemini', $result['provider']);
        $this->assertTrue($result['fallback_used']);
    }
}
