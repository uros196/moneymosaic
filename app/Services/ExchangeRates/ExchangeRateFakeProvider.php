<?php

namespace App\Services\ExchangeRates;

use App\DTO\ExchangeRates\DailyRates;
use App\DTO\ExchangeRates\RateQuote;
use Carbon\CarbonInterface;
use Illuminate\Support\Testing\Fakes\Fake;

/**
 * Fake Exchange Rate provider intended for tests.
 *
 * This provider lets you configure deterministic exchange rates without any
 * external HTTP calls. You can scope rates globally (date‑agnostic) or to
 * a specific date (Y-m-d). Use the facade to quickly swap the fake in tests.
 *
 * Basic usage in a test (date-scoped):
 *
 *   use App\Facades\ExchangeRateProvider;
 *   ExchangeRateProvider::fake([
 *       '2025-08-10' => [
 *           'EUR' => [
 *               'USD' => 1.10,
 *               'RSD' => 117.50,
 *           ],
 *       ],
 *   ]);
 *
 * Global (date-agnostic) mappings, any of the following shapes are accepted:
 *
 *   ExchangeRateProvider::fake([
 *       'EUR/USD' => 1.12,
 *       'EUR' => ['RSD' => 117.50],
 *   ]);
 *
 * Accepted config shapes for setFakeResponse():
 * - ["EUR/USD" => 1.1]
 * - ["EUR" => ["USD" => 1.1, "RSD" => 117.5]]
 * - ["2025-08-10" => ["EUR/USD" => 1.1]]
 * - ["2025-08-10" => ["EUR" => ["USD" => 1.1]]]
 *
 * Behavior notes:
 * - base === quote always returns 1.0 (identity rate).
 * - Date-specific mapping takes precedence over the global mapping.
 * - If there is no mapping match, a default multiplier of 1.10 is used.
 * - Currency codes are normalized to UPPERCASE.
 * - getRatesForRange() returns an inclusive DailyRates per calendar day.
 *
 * @see \App\Facades\ExchangeRateProvider::fake()
 */
class ExchangeRateFakeProvider implements ExchangeRateProviderInterface, Fake
{
    /**
     * Normalized configuration storage: [date|null][base][quote] => multiplier
     *
     * @var array<string|null, array<string, array<string, float>>>
     */
    private array $config = [];

    /**
     * Default multiplier used when no configured mapping is found.
     *
     * This keeps tests predictable even if a specific pair is not provided.
     */
    private float $defaultMultiplier = 1.10;

    /**
     * Build a DailyRates DTO for the given date, base currency, and list of quote currencies.
     *
     * Each quote is resolved using the configured fake multipliers via getMultiplier().
     * If base equals quote, the identity rate 1.0 is returned. If no mapping matches,
     * the default multiplier is used.
     */
    public function getRatesForDate(CarbonInterface $date, string $baseCurrency, array $currencies): DailyRates
    {
        return new DailyRates(
            date: $date,
            base: strtoupper($baseCurrency),
            quotes: array_map(function ($currency) use ($date, $baseCurrency) {
                return new RateQuote(
                    base: $baseCurrency,
                    quote: $currency,
                    multiplier: $this->getMultiplier($date, $baseCurrency, $currency)
                );
            }, $currencies),
        );
    }

    /**
     * Build DailyRates for each day in the inclusive range between startDate and endDate.
     *
     * If the dates are out of order, they are swapped so that iteration proceeds chronologically.
     * Each DailyRates entry is produced via getRatesForDate().
     *
     * @return array<int, DailyRates>
     */
    public function getRatesForRange(CarbonInterface $startDate, CarbonInterface $endDate, string $baseCurrency, array $currencies): array
    {
        $results = [];

        // Ensure chronological order and inclusive range
        $cursor = $startDate->clone();
        $end = $endDate->clone();

        if ($cursor->gt($end)) {
            [$cursor, $end] = [$end, $cursor];
        }

        while ($cursor->lte($end)) {
            // Clone the cursor to avoid mutating the same Carbon instance across entries
            $results[] = $this->getRatesForDate($cursor->clone(), $baseCurrency, $currencies);
            $cursor = $cursor->addDay();
        }

        return $results;
    }

    /**
     * Configure the fake provider with the given response mapping.
     *
     * This will reset any previously configured mappings and ingest the provided shapes.
     * Accepted shapes include:
     * - ["EUR/USD" => 1.1]
     * - ["EUR" => ["USD" => 1.1, "RSD" => 117.5]]
     * - ["2025-08-10" => ["EUR/USD" => 1.1]]
     * - ["2025-08-10" => ["EUR" => ["USD" => 1.1]]]
     *
     * Date-scoped mappings take precedence over global ones during resolution.
     */
    public function setFakeResponse(array $response): self
    {
        $this->config = [];

        // Accept flexible shapes and normalize them
        foreach ($response as $key => $value) {
            // Date-specific mapping
            if (is_string($key) && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $key) === 1) {
                $dateKey = $key; // keep Y-m-d
                $this->ingestMapping($dateKey, $value);

                continue;
            }

            // Global mappings (no date)
            $this->ingestMapping(null, [$key => $value]);
        }

        return $this;
    }

    /**
     * Ingest a mapping into the normalized configuration array.
     *
     * Accepts either a flat pair map like ["EUR/USD" => 1.1] or a nested
     * base-first map like ["EUR" => ["USD" => 1.1, "RSD" => 117.5]]. All currency
     * codes are normalized to uppercase. Values are cast to float.
     *
     * @param  string|null  $dateKey  Y-m-d string to scope the mapping to a date, or null for global.
     * @param  mixed  $mapping  The mapping payload to ingest.
     */
    private function ingestMapping(?string $dateKey, mixed $mapping): void
    {
        if (! isset($this->config[$dateKey])) {
            $this->config[$dateKey] = [];
        }

        $setConfig = function (string $base, string $quote, float $multiplier) use ($dateKey) {
            $this->config[$dateKey][$base][$quote] = $multiplier;
        };

        // Support formats:
        // 1) ["EUR/USD" => 1.1]
        // 2) ["EUR" => ["USD" => 1.1, "RSD" => 117.5]]
        if (is_array($mapping)) {
            foreach ($mapping as $k => $v) {
                if (is_string($k) && str_contains($k, '/')) {
                    [$base, $quote] = array_map('strtoupper', explode('/', $k, 2));
                    $setConfig($base, $quote, $v);
                } elseif (is_string($k) && is_array($v)) {
                    $base = strtoupper($k);
                    foreach ($v as $q => $mult) {
                        $quote = strtoupper((string) $q);
                        $setConfig($base, $quote, $mult);
                    }
                }
            }
        }
    }

    /**
     * Resolve the multiplier for base/quote at the given date.
     *
     * Precedence:
     * 1) Date-specific mapping, if present.
     * 2) Global mapping, if present.
     * 3) Default multiplier when no mapping is found.
     *
     * The identity rate (base === quote) is always 1.0.
     */
    private function getMultiplier(CarbonInterface $date, string $base, string $quote): float
    {
        if ($base === $quote) {
            return 1.0;
        }

        $dateKey = $date->toDateString();

        // Date-specific exact match
        if (isset($this->config[$dateKey][$base][$quote])) {
            return $this->config[$dateKey][$base][$quote];
        }

        // Global exact match
        if (isset($this->config[null][$base][$quote])) {
            return $this->config[null][$base][$quote];
        }

        // Fallback default
        return $this->defaultMultiplier;
    }
}
