<?php

namespace App\Repositories\Contracts;

use App\Models\ExchangeRate;
use Carbon\CarbonInterface;

/**
 * Contract for retrieving and persisting exchange rates.
 *
 * Defines operations to resolve the most recent rate for a currency pair
 * at or before a given date, and to upsert individual rate rows.
 */
interface ExchangeRateRepository
{
    /**
     * Find the latest exchange rate record for a base->quote pair on or before the given date.
     *
     * @param  string  $baseCurrency  ISO 4217 base currency code (e.g., EUR).
     * @param  string  $quoteCurrency  ISO 4217 quote currency code (e.g., USD).
     * @param  CarbonInterface  $date  The date to search on or before.
     * @return ExchangeRate|null The latest matching rate or null if none exists.
     */
    public function findLatestOnOrBefore(string $baseCurrency, string $quoteCurrency, CarbonInterface $date): ?ExchangeRate;

    /**
     * Find the earliest exchange rate record for a base->quote pair on or after the given date.
     *
     * @param  string  $baseCurrency  ISO 4217 base currency code (e.g., EUR).
     * @param  string  $quoteCurrency  ISO 4217 quote currency code (e.g., USD).
     * @param  CarbonInterface  $date  The date to search on or after.
     * @return ExchangeRate|null The earliest matching rate or null if none exists.
     */
    public function findEarliestOnOrAfter(string $baseCurrency, string $quoteCurrency, CarbonInterface $date): ?ExchangeRate;

    /**
     * Upsert a single exchange rate row for a given date and currency pair.
     */
    public function updateOrCreateRate(string $date, string $base, string $quote, float $multiplier): ExchangeRate;
}
