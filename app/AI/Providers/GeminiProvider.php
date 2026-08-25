<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIServiceInterface, TracksTokenUsage
{
    protected string $apiKey;

    protected string $model;

    protected bool $verifySsl;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
        $this->verifySsl = config('services.gemini.verify_ssl', true);
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'gemini';
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
            Log::warning('GeminiProvider: API key not configured');

            return null;
        }

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxOutputTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);

        // Pro models need significantly more time — they run a "thinking"
        // phase before producing any visible output.  Auto-scale the
        // timeout when the caller doesn't provide an explicit override.
        $defaultTimeout = config('ai.generation.timeout', 30);
        $isProModel = (bool) preg_match('/pro/i', $this->model);
        $timeout = $options['timeout']
            ?? ($isProModel ? max($defaultTimeout, 90) : $defaultTimeout);

        try {
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            // Separate connect timeout (fail fast on DNS/TLS issues) from the
            // read timeout (allow Pro models time to "think").
            $http = Http::timeout($timeout)->connectTimeout(10);

            if (! $this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxOutputTokens,
                ],
            ];

            // Gemini 2.5+ "thinking" tokens silently consume up to half of the
            // maxOutputTokens budget before any visible text is produced,
            // causing mid-sentence truncations. Cap the thinking effort unless
            // the caller explicitly overrides it.
            $thinkingLevel = $options['thinking_level']
                ?? config('ai.generation.thinking_level');

            if ($thinkingLevel !== null && preg_match('/^gemini-/i', $this->model)) {
                $payload['generationConfig']['thinkingConfig'] = [
                    'thinkingLevel' => $thinkingLevel,
                ];
            }

            $response = $http->post($url, $payload);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                Log::error("Gemini API error (status {$status})", ['body' => $body]);

                if ($status === 429) {
                    Log::warning('Gemini API quota exceeded. The free tier daily limit has been reached.');
                }

                return null;
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            $finishReason = $data['candidates'][0]['finishReason'] ?? null;
            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('Gemini API: response truncated at maxOutputTokens', [
                    'model' => $this->model,
                    'max_output_tokens' => $maxOutputTokens,
                    'partial' => $text !== null,
                ]);
            }

            if (! $text) {
                Log::warning('Gemini API: empty response', ['response' => $data, 'finish_reason' => $finishReason]);

                return null;
            }

            if (isset($data['usageMetadata'])) {
                $this->lastUsage = [
                    'input' => (int) ($data['usageMetadata']['promptTokenCount'] ?? 0),
                    'output' => (int) ($data['usageMetadata']['candidatesTokenCount'] ?? 0),
                ];
            }

            return trim($text);
        } catch (\Exception $e) {
            Log::error('GeminiProvider exception: '.$e->getMessage());

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

        return JsonRecovery::decode($result, 'GeminiProvider');
    }
}
