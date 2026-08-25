<?php

namespace App\AI\Support;

use Illuminate\Support\Facades\Log;

/**
 * Safe JSON parsing/recovery for AI provider responses.
 *
 * Handles common LLM response quirks: markdown code fences,
 * prose wrapping around a JSON object/array, and trailing text.
 */
class JsonRecovery
{
    /**
     * Attempt to decode a JSON structure from raw model output.
     *
     * @return array|null decoded data, or null when unrecoverable
     */
    public static function decode(string $raw, string $context = 'AI'): ?array
    {
        $text = trim($raw);

        // Strip surrounding markdown code fences.
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Direct decode.
        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Recovery: extract the outermost JSON object or array embedded in prose.
        foreach (['{', '['] as $open) {
            $close = $open === '{' ? '}' : ']';
            $start = strpos($text, $open);
            if ($start === false) {
                continue;
            }

            $depth = 0;
            $inString = false;
            $escape = false;
            $end = -1;

            for ($i = $start, $len = strlen($text); $i < $len; $i++) {
                $char = $text[$i];

                if ($inString) {
                    if ($escape) {
                        $escape = false;
                    } elseif ($char === '\\') {
                        $escape = true;
                    } elseif ($char === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($char === '"') {
                    $inString = true;

                    continue;
                }

                if ($char === $open) {
                    $depth++;

                    continue;
                }

                if ($char === $close) {
                    $depth--;
                    if ($depth === 0) {
                        $end = $i;

                        break;
                    }
                }
            }

            if ($end !== -1) {
                $candidate = substr($text, $start, $end - $start + 1);
                $decoded = json_decode($candidate, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        Log::warning("{$context}: failed to parse JSON response", [
            'error' => json_last_error_msg(),
        ]);

        return null;
    }
}
