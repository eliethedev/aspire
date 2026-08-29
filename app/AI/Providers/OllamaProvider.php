<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Contracts\TracksTruncation;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AIServiceInterface, TracksTokenUsage, TracksTruncation
{
    protected string $model;

    protected string $baseUrl;

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    protected ?string $lastFinishReason = null;

    public function __construct(?string $model = null, ?string $baseUrl = null)
    {
        $this->model = $model ?? config('services.ollama.model', 'llama3.1');
        $this->baseUrl = $baseUrl ?? config('services.ollama.url', 'http://localhost:11434');
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/api/tags");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getProviderName(): string
    {
        return 'ollama';
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

    public function getLastFinishReason(): ?string
    {
        return $this->lastFinishReason;
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        $this->lastFinishReason = null;

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);
        $timeout = $options['timeout'] ?? config('ai.generation.timeout', 60);

        try {
            $response = Http::timeout($timeout)->post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => $temperature,
                    'num_predict' => $maxTokens,
                ],
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                Log::error("Ollama API error (status {$status})", ['body' => $body]);

                return null;
            }

            $data = $response->json();
            $text = $data['response'] ?? null;

            if (! $text) {
                Log::warning('Ollama API: empty response', ['response' => $data]);

                return null;
            }

            $this->lastFinishReason = ($data['done_reason'] ?? null) === 'length' ? 'length' : null;

            $this->lastUsage = [
                'input' => (int) ($data['prompt_eval_count'] ?? 0),
                'output' => (int) ($data['eval_count'] ?? 0),
            ];

            return trim($text);
        } catch (\Exception $e) {
            Log::error('OllamaProvider exception: '.$e->getMessage());

            return null;
        }
    }

    public function generateJson(string $prompt, array $options = []): ?array
    {
        $jsonPrompt = $prompt."\n\nRespond with valid JSON only, no markdown formatting, no code blocks.";
        $result = $this->generate($jsonPrompt, $options);

        if (! $result) {
            return null;
        }

        return JsonRecovery::decode($result, 'OllamaProvider');
    }
}
