<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Contracts\TracksTruncation;
use App\AI\Contracts\ReportsLastError;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements AIServiceInterface, TracksTokenUsage, TracksTruncation, ReportsLastError
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl = 'https://openrouter.ai/api/v1';

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    protected ?string $lastFinishReason = null;

    protected ?string $lastError = null;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = (string) ($apiKey ?? config('services.openrouter.api_key', ''));
        $this->model = (string) ($model ?? config('services.openrouter.model', 'nvidia/nemotron-3.5-lightning:free'));
        $this->baseUrl = (string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'openrouter';
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

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        $this->lastError = null;

        if (! $this->isAvailable()) {
            $this->lastError = 'API key not configured';
            Log::warning('OpenRouterProvider: API key not configured');

            return null;
        }

        $this->lastFinishReason = null;

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);
        $timeout = $options['timeout'] ?? config('ai.generation.timeout', 60);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => config('app.name', 'ASPIRE'),
            ])->withoutVerifying()->timeout($timeout)->connectTimeout(10)->post("{$this->baseUrl}/chat/completions", [
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
                Log::error("OpenRouter API error (status {$status})", ['body' => $body]);

                $this->lastError = $this->describeApiFailure($status, $body);

                if ($status === 429) {
                    Log::warning('OpenRouter API rate limit exceeded.');
                }

                return null;
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? null;

            if (! $text) {
                $this->lastError = 'API returned an empty response';
                Log::warning('OpenRouter API: empty response');

                return null;
            }

            $this->lastFinishReason = ($data['choices'][0]['finish_reason'] ?? null) === 'length' ? 'length' : null;

            if (isset($data['usage'])) {
                $this->lastUsage = [
                    'input' => (int) ($data['usage']['prompt_tokens'] ?? 0),
                    'output' => (int) ($data['usage']['completion_tokens'] ?? 0),
                ];
            }

            return trim($text);
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error('OpenRouterProvider exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Build a short, actionable description from an API error response so the
     * lastError surface tells the operator why the request failed.
     */
    protected function describeApiFailure(int $status, string $body): string
    {
        $detail = null;
        if (str_starts_with(trim($body), '{')) {
            $decoded = json_decode($body, true);
            $detail = $decoded['error']['message'] ?? null;
        }

        if ($status === 429) {
            return 'rate limited (429, free-tier daily quota exhausted)'
                .($detail !== null ? ': '.$detail : '')
                .' — wait for the daily reset or add credits to raise the limit';
        }

        return 'request failed (HTTP '.$status.')'
            .($detail !== null ? ': '.$detail : '');
    }

    public function generateJson(string $prompt, array $options = []): ?array
    {
        $jsonPrompt = $prompt."\n\nRespond with valid JSON only, no markdown formatting, no code blocks.";
        $result = $this->generate($jsonPrompt, $options);

        if (! $result) {
            return null;
        }

        return JsonRecovery::decode($result, 'OpenRouterProvider');
    }
}
