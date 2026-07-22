<?php

namespace App\AI\Contracts;

interface AIServiceInterface
{
    public function generate(string $prompt, array $options = []): ?string;

    public function generateJson(string $prompt, array $options = []): ?array;

    public function isAvailable(): bool;

    public function getProviderName(): string;

    public function getModelName(): string;

    public function setModel(string $model): void;
}
