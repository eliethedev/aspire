<?php

namespace App\AI\Costs;

/**
 * Estimates the cost of an AI call from recorded token usage.
 *
 * Prices live in config('ai.pricing') and are stated in USD per 1,000,000
 * tokens (providers bill in USD). The estimate is converted to the display
 * currency (PHP) via the configurable usd_to_php_rate. Resolution order per
 * call: exact-model price -> provider fallback -> unpriced (0.00).
 */
final class AiCostCalculator
{
    protected array $pricing;

    public function __construct(?array $pricing = null)
    {
        $this->pricing = $pricing ?? (array) config('ai.pricing', []);
    }

    /**
     * Estimated cost in PH Peso for a single call (or aggregated token sums).
     */
    public function estimate(string $provider, string $model, ?int $inputTokens, ?int $outputTokens): float
    {
        $price = $this->priceFor($provider, $model);

        $costUsd = ((int) $inputTokens / 1_000_000) * $price['input']
            + ((int) $outputTokens / 1_000_000) * $price['output'];

        return (float) round($costUsd * $this->usdToPhpRate(), 4);
    }

    /**
     * @return array{input: float, output: float} USD per 1M tokens.
     */
    public function priceFor(string $provider, string $model): array
    {
        $providerKey = strtolower(trim((string) $provider));
        $modelKey = strtolower(trim((string) $model));

        $modelPricing = $this->pricing['models'][$providerKey][$modelKey] ?? null;
        if (is_array($modelPricing)) {
            return $this->normalize($modelPricing);
        }

        $providerPricing = $this->pricing['providers'][$providerKey] ?? null;
        if (is_array($providerPricing)) {
            return $this->normalize($providerPricing);
        }

        return ['input' => 0.0, 'output' => 0.0];
    }

    /**
     * Whether a provider/model has any configured price (used to flag
     * unpriced custom models in the UI).
     */
    public function isPriced(string $provider, string $model): bool
    {
        $price = $this->priceFor($provider, $model);

        return $price['input'] > 0.0 || $price['output'] > 0.0;
    }

    public function usdToPhpRate(): float
    {
        return (float) ($this->pricing['usd_to_php_rate'] ?? 58.0);
    }

    public function currency(): string
    {
        return (string) ($this->pricing['currency'] ?? 'PHP');
    }

    public function currencySymbol(): string
    {
        return (string) ($this->pricing['currency_symbol'] ?? '₱');
    }

    /**
     * @param  array{input?: mixed, output?: mixed}  $price
     * @return array{input: float, output: float}
     */
    protected function normalize(array $price): array
    {
        return [
            'input' => (float) ($price['input'] ?? 0),
            'output' => (float) ($price['output'] ?? 0),
        ];
    }
}