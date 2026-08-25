<?php

namespace Tests\Unit\AI;

use App\AI\Support\JsonRecovery;
use Tests\TestCase;

class JsonRecoveryTest extends TestCase
{
    public function test_decodes_plain_json(): void
    {
        $this->assertSame(['a' => 1], JsonRecovery::decode('{"a": 1}'));
    }

    public function test_decodes_json_in_markdown_fence(): void
    {
        $raw = "```json\n{\"summary\": \"ok\", \"items\": [1, 2]}\n```";

        $this->assertSame(['summary' => 'ok', 'items' => [1, 2]], JsonRecovery::decode($raw));
    }

    public function test_extracts_json_wrapped_in_prose(): void
    {
        $raw = 'Here is the requested analysis: {"gaps": ["x"]} Hope this helps!';

        $this->assertSame(['gaps' => ['x']], JsonRecovery::decode($raw));
    }

    public function test_handles_braces_inside_strings(): void
    {
        $raw = '{"note": "use {curly} braces", "n": 2}';

        $this->assertSame(['note' => 'use {curly} braces', 'n' => 2], JsonRecovery::decode($raw));
    }

    public function test_returns_null_for_unrecoverable_output(): void
    {
        $this->assertNull(JsonRecovery::decode('Sorry, I cannot help with that.'));
    }
}
