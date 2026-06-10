<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Exceptions\AIException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIServiceInterface
{
    protected string $apiKey;

    protected string $model;

    protected bool $verifySsl;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
        $this->verifySsl = config('services.gemini.verify_ssl', true);
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function generate(string $prompt, array $options = []): ?string
    {
        if (!$this->isAvailable()) {
            Log::warning('GeminiProvider: API key not configured');
            return null;
        }

        $temperature = $options['temperature'] ?? config('ai.generation.temperature', 0.5);
        $maxOutputTokens = $options['max_output_tokens'] ?? config('ai.generation.max_output_tokens', 1024);
        $timeout = $options['timeout'] ?? config('ai.generation.timeout', 30);

        try {
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $http = Http::timeout($timeout);

            if (!$this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($url, [
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
            ]);

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

            if (!$text) {
                Log::warning('Gemini API: empty response', ['response' => $data]);
                return null;
            }

            return trim($text);
        } catch (\Exception $e) {
            Log::error('GeminiProvider exception: ' . $e->getMessage());
            return null;
        }
    }

    public function generateJson(string $prompt, array $options = []): ?array
    {
        $jsonPrompt = $prompt . "\n\nRespond with valid JSON only, no markdown formatting, no code blocks.";
        $result = $this->generate($jsonPrompt, $options);

        if (!$result) {
            return null;
        }

        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $result);

        $decoded = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('GeminiProvider: failed to parse JSON response', [
                'error' => json_last_error_msg(),
                'raw' => $result,
            ]);
            return null;
        }

        return $decoded;
    }
}
