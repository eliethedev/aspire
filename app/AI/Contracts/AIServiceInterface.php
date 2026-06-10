<?php

namespace App\AI\Contracts;

use App\Models\Observation;
use App\Models\CotRating;

interface AIServiceInterface
{
    public function generate(string $prompt, array $options = []): ?string;

    public function generateJson(string $prompt, array $options = []): ?array;

    public function isAvailable(): bool;

    public function getProviderName(): string;

    public function getModelName(): string;
}
