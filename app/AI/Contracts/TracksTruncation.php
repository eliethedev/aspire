<?php

namespace App\AI\Contracts;

/**
 * Marks AI providers that can report why generation stopped. When a provider
 * hits its output token ceiling (finish reason is length-limited), callers
 * can retry once with a larger budget to produce a complete response.
 */
interface TracksTruncation
{
    /**
     * Last generation's finish reason, normalized:
     *   - "MAX_TOKENS"  Gemini API
     *   - "length"      OpenAI-compatible APIs, Claude, Ollama
     *   - null          completed normally (STOP) or nothing generated yet
     */
    public function getLastFinishReason(): ?string;
}