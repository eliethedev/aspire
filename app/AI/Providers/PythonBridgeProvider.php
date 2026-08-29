<?php

namespace App\AI\Providers;

use App\AI\Bridge\PythonAIBridge;
use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Log;

/**
 * AI provider that delegates generation to the Python AI Bridge.
 *
 * This provider implements the same AIServiceInterface as native PHP
 * providers, allowing tasks to be routed through the Python bridge
 * via config (ai.provider = 'python') while keeping full fallback
 * chain and usage logging compatibility.
 */
class PythonBridgeProvider implements AIServiceInterface, TracksTokenUsage
{
    protected PythonAIBridge $bridge;

    protected string $model;

    protected string $pythonProvider;

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    public function __construct(?string $model = null, ?string $pythonProvider = null)
    {
        $this->bridge = app(PythonAIBridge::class);
        $this->model = $model ?: config('ai.python_bridge.default_model', 'gemini-3.6-flash');
        $this->pythonProvider = $pythonProvider ?? $this->resolvePythonProvider();
    }

    public function isAvailable(): bool
    {
        return $this->bridge->isEnabled();
    }

    public function getProviderName(): string
    {
        return 'python';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getLastUsage(): array
    {
        return $this->lastUsage;
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        if (! $this->isAvailable()) {
            Log::warning('PythonBridgeProvider: bridge not enabled or script missing');

            return null;
        }

        $result = $this->bridge->generate(
            $this->pythonProvider,
            $this->model,
            $prompt,
            $this->buildOptions($options),
        );

        if ($result === null || empty($result['success'])) {
            $error = $result['error'] ?? 'Unknown bridge error';
            Log::error('PythonBridgeProvider failed', ['error' => $error, 'provider' => $this->pythonProvider]);

            return null;
        }

        $this->trackUsage($result);

        return $result['text'] ?? null;
    }

    public function generateJson(string $prompt, array $options = []): ?array
    {
        $json = $this->bridge->generateJson(
            $this->pythonProvider,
            $this->model,
            $prompt,
            $this->buildOptions($options),
        );

        if ($json === null) {
            // Fallback: generate text and attempt PHP-side JSON recovery.
            $text = $this->generate($prompt, $options);

            return $text !== null ? JsonRecovery::decode($text, 'PythonBridgeProvider') : null;
        }

        return $json;
    }

    protected function buildOptions(array $options): array
    {
        return [
            'temperature' => $options['temperature'] ?? config('ai.generation.temperature', 0.5),
            'max_output_tokens' => $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024),
            'api_key' => $options['api_key'] ?? '',
        ];
    }

    /**
     * Map the Python bridge "provider" field back to input/output tokens.
     *
     * The Python bridge returns a single "tokens" total. Without per-provider
     * breakdown, we attribute the full count to output_tokens and keep
     * input_tokens at 0, matching the behaviour of providers that don't
     * report granular usage.
     */
    protected function trackUsage(array $result): void
    {
        $totalTokens = (int) ($result['tokens'] ?? 0);
        $this->lastUsage = [
            'input' => 0,
            'output' => $totalTokens,
        ];
    }

    /**
     * Resolve which Python-side provider to use based on the configured
     * bridge provider or the global default.
     */
    protected function resolvePythonProvider(): string
    {
        $configured = config('ai.python_bridge.provider', '');

        if ($configured !== '') {
            return $configured;
        }

        // Mirror the global default provider to the Python bridge.
        $globalDefault = config('ai.provider', 'gemini');

        return in_array($globalDefault, ['gemini', 'openai', 'claude', 'ollama']) ? $globalDefault : 'gemini';
    }
}
