<?php

namespace Tests\Unit\AI;

use App\AI\Costs\AiCostCalculator;
use Tests\TestCase;

class AiCostCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['ai.pricing' => [
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'usd_to_php_rate' => 58.0,
            'providers' => [
                'gemini' => ['input' => 0.50, 'output' => 1.50],
                'openai' => ['input' => 2.50, 'output' => 10.00],
            ],
            'models' => [
                'gemini' => [
                    'gemini-3.6-flash' => ['input' => 0.10, 'output' => 0.40],
                ],
            ],
        ]]);
    }

    public function test_known_model_price_computes_expected_peso_cost(): void
    {
        $calculator = new AiCostCalculator;

        // 1,000 in @$0.10/M + 500 out @$0.40/M = $0.0001 + $0.0002 = $0.0003
        // × 58 PHP/USD = 0.0174
        $this->assertSame(0.0174, $calculator->estimate('gemini', 'gemini-3.6-flash', 1000, 500));
    }

    public function test_provider_fallback_is_used_for_unlisted_model(): void
    {
        $calculator = new AiCostCalculator;

        // gemini fallback: 0.50 / 1.50 → 1,000 in + 500 out = $0.0005 + $0.00075 = $0.00125 × 58 = 0.0725
        $this->assertEqualsWithDelta(0.0725, $calculator->estimate('gemini', 'custom-model-x', 1000, 500), 1.0e-9);
    }

    public function test_unknown_provider_is_unpriced(): void
    {
        $calculator = new AiCostCalculator;

        $this->assertSame(0.0, $calculator->estimate('snakeoil', 'oracle-9000', 1000, 500));
        $this->assertFalse($calculator->isPriced('snakeoil', 'oracle-9000'));
    }

    public function test_zero_tokens_cost_nothing(): void
    {
        $calculator = new AiCostCalculator;

        $this->assertSame(0.0, $calculator->estimate('openai', 'gpt-4o', 0, 0));
        $this->assertSame(0.0, $calculator->estimate('openai', 'gpt-4o', null, null));
    }

    public function test_provider_lookup_is_case_insensitive(): void
    {
        $calculator = new AiCostCalculator;

        $this->assertEqualsWithDelta(
            $calculator->estimate('gemini', 'gemini-3.6-flash', 1000, 500),
            $calculator->estimate('Gemini', 'GEMINI-3.6-FLASH', 1000, 500),
            1.0e-9,
        );
    }

    public function test_currency_helpers_reflect_config(): void
    {
        $calculator = new AiCostCalculator;

        $this->assertSame('PHP', $calculator->currency());
        $this->assertSame('₱', $calculator->currencySymbol());
        $this->assertSame(58.0, $calculator->usdToPhpRate());
    }

    public function test_can_be_constructed_with_explicit_pricing(): void
    {
        $calculator = new AiCostCalculator([
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'usd_to_php_rate' => 1.0,
            'providers' => ['openai' => ['input' => 2.00, 'output' => 4.00]],
        ]);

        // 1,000 in + 1,000 out = $0.002 + $0.004 = $0.006
        $this->assertEqualsWithDelta(0.006, $calculator->estimate('openai', 'gpt-x', 1000, 1000), 1.0e-9);
    }
}