<?php

namespace App\AI\Contracts;

interface ReportsLastError
{
    /**
     * Human-readable reason for the most recent failed generate() call,
     * or null when the last call did not fail.
     */
    public function getLastError(): ?string;
}