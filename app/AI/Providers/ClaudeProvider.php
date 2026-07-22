<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AIServiceInterface
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl = 'https://api.anthropic.com/v1';

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? config('services.claude.api_key', '');
        $this->model = $model ?? config('services.claude.model', 'claude-sonnet-4-20250514');
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'claude';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        if (! $this->isAvailable()) {
            Log::warning('ClaudeProvider: API key not configured');

            return null;
        }

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);
        $timeout = $options['timeout'] ?? config('ai.generation.timeout', 30);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout($timeout)->post("{$this->baseUrl}/messages", [
                'model' => $this->model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                Log::error("Claude API error (status {$status})", ['body' => $body]);

                if ($status === 429) {
                    Log::warning('Claude API rate limit exceeded.');
                }

                return null;
            }

            $data = $response->json();
            $text = $data['content'][0]['text'] ?? null;

            if (! $text) {
                Log::warning('Claude API: empty response', ['response' => $data]);

                return null;
            }

            return trim($text);
        } catch (\Exception $e) {
            Log::error('ClaudeProvider exception: '.$e->getMessage());

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

        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $result);

        $decoded = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('ClaudeProvider: failed to parse JSON response', [
                'error' => json_last_error_msg(),
                'raw' => $result,
            ]);

            return null;
        }

        return $decoded;
    }
}
