<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\ExchangeRate;
use App\Support\Concerns\ParsesExchangeSymbols;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    use ParsesExchangeSymbols;

    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ExchangeRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $base = $this->configuredBaseCurrency();

        // Pick a quote currency different from the base from supported enum cases
        $quote = Arr::random($this->getQuoteCurrencies($base));

        return [
            'date' => fake()->dateTimeBetween('-2 years', 'now'),
            'base_currency_code' => $base,
            'quote_currency_code' => $quote,
            // Reasonable multiplier range for major currencies
            'rate_multiplier' => $this->rateFor($base, $quote),
        ];
    }

    /**
     * State: specify an explicit currency pair.
     */
    public function forPair(Currency|string $base, Currency|string $quote): self
    {
        return $this->state(fn () => [
            'base_currency_code' => $this->parseCurrency($base),
            'quote_currency_code' => $this->parseCurrency($quote),
            'rate_multiplier' => $base === $quote ? 1.0 : $this->rateFor($base, $quote),
        ]);
    }

    /**
     * State: base->base rate (multiplier 1.0).
     */
    public function baseToBase(Currency|string|null $base = null): self
    {
        $base = ! is_null($base)
            ? $this->parseCurrency($base)
            : $this->configuredBaseCurrency();

        return $this->state(fn () => [
            'base_currency_code' => $base,
            'quote_currency_code' => $base,
            'rate_multiplier' => 1.0,
        ]);
    }

    /**
     * Generate a reasonable rate multiplier for a base->quote pair.
     */
    private function rateFor(Currency|string $base, Currency|string $quote): float
    {
        // Simple heuristics for EUR base to popular quotes; otherwise generic ranges
        if ($this->parseCurrency($base) === 'EUR') {
            return match ($this->parseCurrency($quote)) {
                'USD' => fake()->randomFloat(6, 0.9, 1.3),
                'GBP' => fake()->randomFloat(6, 0.7, 1.1),
                'CHF' => fake()->randomFloat(6, 0.7, 1.2),
                'CAD' => fake()->randomFloat(6, 1.2, 1.6),
                'RSD' => fake()->randomFloat(6, 100, 130),
                default => fake()->randomFloat(6, 0.5, 150),
            };
        }

        // Generic range when base is not EUR
        return fake()->randomFloat(6, 0.0001, 200);
    }

    /**
     * Get an array of available quote currencies excluding the base currency.
     */
    protected function getQuoteCurrencies(string $base): array
    {
        return array_diff($this->configuredSymbols(), [$base]);
    }

    /**
     * Parse currency input into a standardized string format.
     */
    protected function parseCurrency(Currency|string $currency): string
    {
        return $currency instanceof Currency ? $currency->value : strtoupper($currency);
    }
}
