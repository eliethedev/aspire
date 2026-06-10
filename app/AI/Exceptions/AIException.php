<?php

namespace App\AI\Exceptions;

use Exception;

class AIException extends Exception
{
    public static function notConfigured(): self
    {
        return new self('AI service is not configured. Set GEMINI_API_KEY in .env or set AI_PROVIDER.');
    }

    public static function quotaExceeded(): self
    {
        return new self('AI API quota exceeded. The free tier daily limit has been reached. Get a new key at https://aistudio.google.com/apikey');
    }

    public static function generationFailed(string $reason = ''): self
    {
        $message = 'AI generation failed';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function invalidResponse(): self
    {
        return new self('AI returned an invalid or empty response');
    }

    public static function unsupportedProvider(string $provider): self
    {
        return new self("Unsupported AI provider: {$provider}");
    }
}
