<?php

namespace App\Services\ExchangeRates;

use App\DTO\ExchangeRates\DailyRates;
use App\Facades\ExchangeRateProvider;
use App\Models\ExchangeRate;
use App\Repositories\Contracts\ExchangeRateRepository;
use App\Support\Concerns\ParsesExchangeSymbols;
use Carbon\CarbonInterface;

/**
 * ExchangeRateSyncService
 *
 * Responsibilities (organized by logical units):
 * - Fetching: obtain daily rates from the configured RateProvider (by date).
 * - Persistence: persist a DailyRates DTO into the database (idempotent upserts).
 * - Creation API: provide a reusable create method for a single rate row.
 */
class ExchangeRateSyncService
{
    use ParsesExchangeSymbols;

    public function __construct(public ExchangeRateRepository $repository)
    {
        // Provider is resolved from the container; tests can mock RateProvider::class
    }

    /**
     * High-level operation: Fetch and persist rates for a specific date using global configuration.
     */
    public function syncForDate(CarbonInterface $date): void
    {
        $base = $this->configuredBaseCurrency();
        $currencies = $this->configuredSymbols();

        $daily = ExchangeRateProvider::getRatesForDate($date, $base, $currencies);

        $this->persistDaily($daily);
    }

    /**
     * High-level operation: Fetch and persist rates for an inclusive date range using global configuration.
     */
    public function syncForRange(CarbonInterface $startDate, CarbonInterface $endDate): void
    {
        if ($endDate->lessThan($startDate)) {
            throw new \InvalidArgumentException('End date must be on or after start date.');
        }

        $base = $this->configuredBaseCurrency();
        $currencies = $this->configuredSymbols();

        $days = ExchangeRateProvider::getRatesForRange($startDate, $endDate, $base, $currencies);

        foreach ($days as $daily) {
            $this->persistDaily($daily);
        }
    }

    /**
     * Persistence: Upsert all quotes from the provided DailyRates and enforce base->base = 1.0.
     */
    public function persistDaily(DailyRates $daily): void
    {
        $date = $daily->date->toDateString();
        $base = $daily->base;
        $allowed = $this->configuredSymbols();

        // Persist quotes except base->base
        foreach ($daily->toMap() as $quote => $rate) {
            $quote = strtoupper($quote);

            if ($quote === $base) {
                continue; // enforce base->base once below
            }

            // Only persist globally configured quotes
            if (! in_array($quote, $allowed, true)) {
                continue;
            }

            $this->create($date, $base, $quote, $rate);
        }

        // Enforce base->base = 1.0
        $this->create($date, $base, $base, 1.0);
    }

    /**
     * Creation API: Create (or update) a single rate row for date/base/quote.
     *
     * Uses updateOrCreate for idempotency so repeated syncs don't duplicate rows.
     */
    public function create(string $date, string $base, string $quote, float $multiplier): ExchangeRate
    {
        return $this->repository->updateOrCreateRate($date, $base, $quote, $multiplier);
    }
}
