<?php

namespace App\AI\Exceptions;

use Exception;

class AIRateLimitException extends Exception
{
    public int $retryAfter;

    public string $operation;

    public function __construct(string $operation = '', int $retryAfter = 0)
    {
        $this->operation = $operation;
        $this->retryAfter = $retryAfter;

        $message = 'AI rate limit exceeded.';
        if ($operation) {
            $message .= " Operation: {$operation}.";
        }
        if ($retryAfter > 0) {
            $message .= " Retry after {$retryAfter} seconds.";
        }
        parent::__construct($message, 429);
    }
}
