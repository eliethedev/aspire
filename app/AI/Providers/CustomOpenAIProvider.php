<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIServiceInterface;
use App\AI\Contracts\TracksTokenUsage;
use App\AI\Contracts\TracksTruncation;

/**
 * Reusable OpenAI-compatible ("chat/completions") provider for admin-created
 * custom providers. Delegates the actual HTTP conversation, JSON recovery and
 * token tracking to OpenAIProvider; only the endpoint, key, name and model are
 * customised. Suits Groq, Together AI, Fireworks, LM Studio, vLLM and any
 * other vendor that speaks the OpenAI chat API.
 */
class CustomOpenAIProvider extends OpenAIProvider implements AIServiceInterface, TracksTokenUsage, TracksTruncation
{
    protected string $providerName;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $providerName = null,
        ?string $model = null,
    ) {
        $this->apiKey = (string) ($apiKey ?? '');
        $this->baseUrl = rtrim((string) ($baseUrl ?? 'https://api.openai.com/v1'), '/');
        $this->providerName = (string) ($providerName ?? 'custom');
        $this->model = (string) ($model ?? '');
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }
}