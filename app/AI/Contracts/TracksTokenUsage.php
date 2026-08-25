<?php

namespace App\AI\Contracts;

/**
 * Optional capability contract for providers that can report
 * token usage of their most recent generation call.
 *
 * Kept separate from AIServiceInterface so existing provider
 * implementations and mocks remain backward compatible.
 */
interface TracksTokenUsage
{
    /**
     * Token usage from the most recent generate() call.
     *
     * @return array{input: int, output: int}
     */
    public function getLastUsage(): array;
}
