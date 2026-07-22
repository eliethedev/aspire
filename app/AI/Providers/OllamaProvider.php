<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AIServiceInterface
{
    protected string $model;

    protected string $baseUrl;

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

    public function generate(string $prompt, array $options = []): ?string
    {
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

        $result = trim($result);
        $result = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $result);

        $decoded = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('OllamaProvider: failed to parse JSON response', [
                'error' => json_last_error_msg(),
                'raw' => $result,
            ]);

            return null;
        }

        return $decoded;
    }
}
