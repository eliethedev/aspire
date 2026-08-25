<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIServiceInterface, TracksTokenUsage
{
    protected string $apiKey;

    protected string $model;

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? config('services.openai.api_key', '');
        $this->model = $model ?? config('services.openai.model', 'gpt-4o');
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'openai';
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
            Log::warning('OpenAIProvider: API key not configured');

            return null;
        }

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);
        $timeout = $options['timeout'] ?? config('ai.generation.timeout', 30);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($timeout)->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                Log::error("OpenAI API error (status {$status})", ['body' => $body]);

                if ($status === 429) {
                    Log::warning('OpenAI API rate limit exceeded.');
                }

                return null;
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? null;

            if (! $text) {
                Log::warning('OpenAI API: empty response', ['response' => $data]);

                return null;
            }

            if (isset($data['usage'])) {
                $this->lastUsage = [
                    'input' => (int) ($data['usage']['prompt_tokens'] ?? 0),
                    'output' => (int) ($data['usage']['completion_tokens'] ?? 0),
                ];
            }

            return trim($text);
        } catch (\Exception $e) {
            Log::error('OpenAIProvider exception: '.$e->getMessage());

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

        return JsonRecovery::decode($result, 'OpenAIProvider');
    }
}
