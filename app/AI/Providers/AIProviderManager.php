<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\Models\AiUsageLog;
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
     * Create a concrete provider instance by name.
     */
    public function createProvider(string $provider, string $model = ''): AIServiceInterface
    {
        $model = $model ?: '';

        return match ($provider) {
            'openai' => new OpenAIProvider(model: $model ?: null),
            'claude' => new ClaudeProvider(model: $model ?: null),
            'deepseek' => new DeepSeekProvider(model: $model ?: null),
            'openrouter' => new OpenRouterProvider(model: $model ?: null),
            'ollama' => new OllamaProvider(model: $model ?: null),
            default => new GeminiProvider,
        };
    }

    /**
     * Resolve a provider instance for a task, honouring per-task
     * provider/model overrides from config.
     */
    public function resolveForTask(string $task): AIServiceInterface
    {
        [$provider, $model] = $this->resolveProviderAndModel($task);

        return $this->instance($provider, $model);
    }

    /**
     * Resolve [provider, model] for a task from configuration.
     *
     * Order: ai.tasks.{task}.provider/model -> ai.models.{task} -> ai.provider/ai.models.default
     *
     * @return array{0: string, 1: string}
     */
    public function resolveProviderAndModel(string $task): array
    {
        $defaultProvider = config('ai.provider', 'gemini');
        $defaultModel = config('ai.models.default', 'gemini-3.6-flash');

        $taskConfig = config("ai.tasks.{$task}");
        if (is_array($taskConfig) && (! empty($taskConfig['provider']) || ! empty($taskConfig['model']))) {
            return [
                $taskConfig['provider'] ?: $defaultProvider,
                $taskConfig['model'] ?: $defaultModel,
            ];
        }

        $stageConfig = config("ai.models.{$task}");
        if ($stageConfig !== null) {
            if (is_array($stageConfig)) {
                return [
                    ($stageConfig['provider'] ?: $defaultProvider),
                    ($stageConfig['model'] ?: $defaultModel),
                ];
            }

            return [$defaultProvider, (string) $stageConfig];
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
     *     duration_ms: int, error: ?string
     * }
     */
    public function run(
        string $task,
        string $prompt,
        array $options = [],
        bool $json = false,
        ?int $observationId = null,
    ): array {
        $chain = $this->fallbackChain($task);

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
