<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Contracts\TracksTruncation;
use App\AI\Contracts\ReportsLastError;
use App\AI\Support\JsonRecovery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIServiceInterface, TracksTokenUsage, TracksTruncation, ReportsLastError
{
    protected string $apiKey;

    protected string $model;

    protected bool $verifySsl;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    protected array $lastUsage = ['input' => 0, 'output' => 0];

    protected ?string $lastFinishReason = null;

    protected ?string $lastError = null;

    public function __construct(?string $model = null)
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = $model ?: config('services.gemini.model', 'gemini-3.6-flash');
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

                $this->lastError = $this->describeApiFailure($status, $body);

                if ($status === 429) {
                    Log::warning('Gemini API quota exceeded. The free tier daily limit has been reached.');
                }

                return null;
            }

            $data = $response->json();
            $parts = $data['candidates'][0]['content']['parts'] ?? [];

            // With thinking enabled the API can return the reasoning as a part
            // flagged "thought": true followed by the visible answer. Assemble
            // only the visible (non-thought) text so coaching responses are not
            // polluted by internal reasoning and so JSON stays decodable.
            $textParts = [];
            foreach ($parts as $part) {
                if (! empty($part['thought'])) {
                    continue;
                }

                if (! empty($part['text'])) {
                    $textParts[] = $part['text'];
                }
            }

            if ($textParts === [] && isset($parts[0]['text'])) {
                $textParts[] = $parts[0]['text'];
            }

            $text = $textParts === [] ? null : trim(implode("\n", $textParts));

            $finishReason = $data['candidates'][0]['finishReason'] ?? null;
            $this->lastFinishReason = $finishReason === 'MAX_TOKENS' ? 'MAX_TOKENS' : null;
            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('Gemini API: response truncated at maxOutputTokens', [
                    'model' => $this->model,
                    'max_output_tokens' => $maxOutputTokens,
                    'partial' => $text !== null,
                ]);
            }

            if (! $text) {
                $this->lastError = 'API returned an empty response';
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
            $this->lastError = $e->getMessage();
            Log::error('GeminiProvider exception: '.$e->getMessage());

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
            return 'rate limited (429, free-tier quota exhausted)'
                .($detail !== null ? ': '.$detail : '')
                .' — wait for the quota window to reset or upgrade the plan';
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

        return JsonRecovery::decode($result, 'GeminiProvider');
    }
}
