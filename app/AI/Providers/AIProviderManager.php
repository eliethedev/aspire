<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Contracts\TracksTruncation;
use App\Models\AiUsageLog;
use App\Models\CustomAiProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Central AI provider router.
 *
 * Resolves the configured provider/model per AI task and executes
 * generation attempts over a controlled fallback chain. Every attempt
 * is logged (without secrets or prompt content) and recorded in the
 * ai_usage_logs table for cost/token protection.
 */
class AIProviderManager
{
    /**
     * Provider instances cached per "provider:model" pair within a request.
     *
     * @var array<string, AIServiceInterface>
     */
    protected array $instances = [];

    /**
     * Create a concrete provider instance by name. Built-in providers resolve
     * via the match below; anything else is looked up among the admin-created
     * custom providers (OpenAI-compatible endpoints) before falling back to
     * the default Gemini provider.
     */
    public function createProvider(string $provider, string $model = ''): AIServiceInterface
    {
        $model = $model ?: '';

        $builders = [
            'gemini' => fn () => new GeminiProvider(model: $model ?: null),
            'openai' => fn () => new OpenAIProvider(model: $model ?: null),
            'claude' => fn () => new ClaudeProvider(model: $model ?: null),
            'deepseek' => fn () => new DeepSeekProvider(model: $model ?: null),
            'openrouter' => fn () => new OpenRouterProvider(model: $model ?: null),
            'ollama' => fn () => new OllamaProvider(model: $model ?: null),
            'python' => fn () => new PythonBridgeProvider(model: $model ?: null),
        ];

        if (isset($builders[$provider])) {
            return $builders[$provider]();
        }

        $custom = CustomAiProvider::query()
            ->where('slug', $provider)
            ->where('enabled', true)
            ->first();

        if ($custom !== null) {
            return $custom->resolveInstance($model);
        }

        return new GeminiProvider(model: $model ?: null);
    }

    /**
     * Resolve a provider instance for a task.
     */
    public function resolveForTask(string $task): AIServiceInterface
    {
        [$provider, $model] = $this->resolveProviderAndModel($task);

        return $this->instance($provider, $model);
    }

    /**
     * Resolve [provider, model] for a task from configuration.
     *
     * Precedence: per-task override (ai.tasks.{task}.provider/model) →
     * per-task model entry (ai.models.{task}) → global default.
     * $task is the AI task/stage identifier (e.g. lesson_plan_suggestion).
     *
     * @return array{0: string, 1: string}
     */
    public function resolveProviderAndModel(string $task): array
    {
        $defaultProvider = (string) config('ai.provider', 'gemini');
        $defaultModel = (string) config('ai.models.default', 'gemini-3.6-flash');

        $taskConfig = config("ai.tasks.{$task}", []);
        if (is_array($taskConfig)) {
            $taskProvider = trim((string) ($taskConfig['provider'] ?? ''));
            $taskModel = trim((string) ($taskConfig['model'] ?? ''));
            if ($taskProvider !== '' || $taskModel !== '') {
                return [
                    $taskProvider !== '' ? $taskProvider : $defaultProvider,
                    $taskModel !== '' ? $taskModel : $defaultModel,
                ];
            }
        }

        $stageEntry = config("ai.models.{$task}");
        if (is_array($stageEntry)) {
            $stageProvider = trim((string) ($stageEntry['provider'] ?? ''));
            $stageModel = trim((string) ($stageEntry['model'] ?? ''));
            if ($stageProvider !== '' || $stageModel !== '') {
                return [
                    $stageProvider !== '' ? $stageProvider : $defaultProvider,
                    $stageModel !== '' ? $stageModel : $defaultModel,
                ];
            }
        } elseif (is_string($stageEntry) && trim($stageEntry) !== '') {
            return [$defaultProvider, trim($stageEntry)];
        }

        return [$defaultProvider, $defaultModel];
    }

    /**
     * Ordered fallback chain for a task: the task's primary provider first,
     * then remaining configured providers that are available.
     *
     * Only usable (configured/reachable) providers are included, so an
     * empty chain means "no provider can serve this task right now".
     *
     * @return array<int, array{provider: string, model: string}>
     */
    public function fallbackChain(string $task): array
    {
        [$primaryProvider, $primaryModel] = $this->resolveProviderAndModel($task);

        $chain = [];

        $primary = $this->instance($primaryProvider, $primaryModel);
        if ($primary->isAvailable()) {
            $chain[] = ['provider' => $primaryProvider, 'model' => $primary->getModelName()];
        }

        if (! config('ai.fallback', true)) {
            return $chain;
        }

        $configured = config('ai.fallback_chain', []);
        if (! is_array($configured) || $configured === []) {
            $configured = ['gemini', 'openrouter', 'openai', 'claude', 'deepseek'];
        }

        foreach ($configured as $name) {
            $name = strtolower(trim((string) $name));
            if ($name === '' || $this->hasEntry($chain, $name)) {
                continue;
            }

            // Never fall back to an unconfigured provider.
            $candidate = $this->instance($name, '');
            if (! $candidate->isAvailable()) {
                continue;
            }

            $chain[] = ['provider' => $name, 'model' => $candidate->getModelName()];
        }

        return $chain;
    }

    /**
     * Run a generation task across the fallback chain.
     *
     * @param  string  $task  Task/stage identifier used for routing, logs and usage records
     * @param  string  $prompt  Fully-built prompt (never persisted raw)
     * @param  array  $options  temperature / max_output_tokens / timeout overrides
     * @param  bool  $json  Whether structured JSON output is expected
     * @param  int|null  $observationId  Optional observation context for usage records
     * @return array{
     *     success: bool, text: ?string, json: ?array, provider: ?string, model: ?string,
     *     fallback_used: bool, input_tokens: int, output_tokens: int,
     *     duration_ms: int, finish_reason: ?string, error: ?string
     * }
     */
    public function run(
        string $task,
        string $prompt,
        array $options = [],
        bool $json = false,
        ?int $observationId = null,
    ): array {
        return $this->executeChain($task, $prompt, $options, $json, $observationId, $this->fallbackChain($task));
    }

    /**
     * Run a generation task with an explicitly routed primary model first.
     *
     * Used by goal-driven routing (e.g. lesson-plan modes): the routed
     * provider/model is attempted first, then the task's standard
     * fallback chain (minus duplicates) serves as failover — ending at
     * the stable baseline. Result shape matches run().
     *
     * @param  array{provider: string, model: string}  $primary
     * @return array{
     *     success: bool, text: ?string, json: ?array, provider: ?string, model: ?string,
     *     fallback_used: bool, input_tokens: int, output_tokens: int,
     *     duration_ms: int, finish_reason: ?string, error: ?string
     * }
     */
    public function runWithPrimary(
        string $task,
        string $prompt,
        array $primary,
        array $options = [],
        bool $json = false,
        ?int $observationId = null,
    ): array {
        $chain = [];

        $primaryProvider = strtolower(trim((string) ($primary['provider'] ?? '')));
        $primaryModel = trim((string) ($primary['model'] ?? ''));

        if ($primaryProvider !== '') {
            $candidate = $this->instance($primaryProvider, $primaryModel);
            if ($candidate->isAvailable()) {
                $chain[] = ['provider' => $primaryProvider, 'model' => $candidate->getModelName()];
            } else {
                Log::channel(config('ai.logging.channel', 'stack'))->warning('AI routed primary unavailable; using fallback chain', [
                    'task' => $task,
                    'provider' => $primaryProvider,
                ]);
            }
        }

        foreach ($this->fallbackChain($task) as $entry) {
            if (! $this->hasEntry($chain, $entry['provider'])) {
                $chain[] = $entry;
            }
        }

        return $this->executeChain($task, $prompt, $options, $json, $observationId, $chain);
    }

    /**
     * Execute generation attempts over a prepared chain.
     *
     * @param  array<int, array{provider: string, model: string}>  $chain
     * @return array{
     *     success: bool, text: ?string, json: ?array, provider: ?string, model: ?string,
     *     fallback_used: bool, input_tokens: int, output_tokens: int,
     *     duration_ms: int, finish_reason: ?string, error: ?string
     * }
     */
    protected function executeChain(
        string $task,
        string $prompt,
        array $options,
        bool $json,
        ?int $observationId,
        array $chain,
    ): array {

        $result = [
            'success' => false,
            'text' => null,
            'json' => null,
            'provider' => null,
            'model' => null,
            'fallback_used' => false,
            'input_tokens' => 0,
            'output_tokens' => 0,
            'duration_ms' => 0,
            'finish_reason' => null,
            'error' => null,
        ];

        // Global kill-switch: never contact providers when AI is disabled.
        if (! config('ai.enabled', true)) {
            $result['error'] = 'AI is disabled';

            return $result;
        }

        if ($chain === []) {
            $result['error'] = 'No AI provider configured';

            return $result;
        }

        $attemptErrors = [];

        foreach ($chain as $index => $entry) {
            $providerName = $entry['provider'];
            $provider = $this->instance($providerName, $entry['model']);
            $model = $provider->getModelName();

            $startedAt = microtime(true);
            $decodedJson = null;

            try {
                if ($json) {
                    // Providers handle JSON instruction + safe parsing/recovery
                    // themselves and return null on malformed output.
                    $decodedJson = $provider->generateJson($prompt, $options);
                    $text = $decodedJson !== null ? json_encode($decodedJson) : null;
                } else {
                    $text = $provider->generate($prompt, $options);
                }
            } catch (\Throwable $e) {
                $text = null;
                Log::error('AI provider attempt threw unexpectedly', [
                    'task' => $task,
                    'provider' => $providerName,
                ]);
            }

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $usage = $provider instanceof TracksTokenUsage
                ? $provider->getLastUsage()
                : ['input' => 0, 'output' => 0];

            $succeeded = $text !== null;

            $this->recordUsage(
                $task,
                $providerName,
                $model,
                $usage,
                $durationMs,
                $succeeded,
                $index > 0,
                $observationId,
            );

            $this->logAttempt($task, $providerName, $model, $succeeded, $durationMs, $index > 0, $usage);

            if ($succeeded) {
                $result['success'] = true;
                $result['text'] = $json ? null : $text;
                $result['json'] = $decodedJson;
                $result['provider'] = $providerName;
                $result['model'] = $model;
                $result['fallback_used'] = $index > 0;
                $result['input_tokens'] = $usage['input'] ?? 0;
                $result['output_tokens'] = $usage['output'] ?? 0;
                $result['duration_ms'] = $durationMs;
                $result['error'] = null;
                $result['finish_reason'] = $provider instanceof TracksTruncation
                    ? $provider->getLastFinishReason()
                    : null;

                return $result;
            }

            $attemptErrors[] = "{$providerName}: failed after {$durationMs}ms";
        }

        $result['error'] = 'All providers failed ('.implode('; ', $attemptErrors).')';
        Log::error('AI task exhausted all providers', [
            'task' => $task,
            'attempts' => count($chain),
        ]);

        return $result;
    }

    /**
     * Persist a usage record (best-effort; never breaks the request).
     */
    protected function recordUsage(
        string $task,
        string $provider,
        string $model,
        array $usage,
        int $durationMs,
        bool $success,
        bool $fallbackUsed,
        ?int $observationId,
    ): void {
        try {
            AiUsageLog::create([
                'stage' => $task,
                'provider' => $provider,
                'model' => $model,
                'prompt_tokens' => $usage['input'] ?? null,
                'response_tokens' => $usage['output'] ?? null,
                'total_tokens' => ($usage['input'] ?? 0) + ($usage['output'] ?? 0),
                'response_time_ms' => $durationMs,
                'success' => $success,
                'fallback_used' => $fallbackUsed,
                'observation_id' => $observationId,
                'user_id' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record AI usage log: '.$e->getMessage());
        }
    }

    protected function logAttempt(
        string $task,
        string $provider,
        string $model,
        bool $success,
        int $durationMs,
        bool $isFallback,
        array $usage,
    ): void {
        if (! config('ai.logging.enabled', true)) {
            return;
        }

        $context = [
            'task' => $task,
            'provider' => $provider,
            'model' => $model,
            'status' => $success ? 'success' : 'failure',
            'duration_ms' => $durationMs,
            'input_tokens' => $usage['input'] ?? 0,
            'output_tokens' => $usage['output'] ?? 0,
        ];

        if (! $success) {
            Log::channel(config('ai.logging.channel', 'stack'))->warning('AI attempt failed', $context);

            return;
        }

        if ($isFallback) {
            $context['note'] = 'Served by fallback provider';
        }

        Log::channel(config('ai.logging.channel', 'stack'))->info('AI task completed', $context);
    }

    protected function instance(string $provider, string $model): AIServiceInterface
    {
        $key = $provider.':'.$model;

        if (! isset($this->instances[$key])) {
            $this->instances[$key] = $this->createProvider($provider, $model);
        }

        return $this->instances[$key];
    }

    protected function hasEntry(array $chain, string $provider): bool
    {
        foreach ($chain as $entry) {
            if ($entry['provider'] === $provider) {
                return true;
            }
        }

        return false;
    }
}
