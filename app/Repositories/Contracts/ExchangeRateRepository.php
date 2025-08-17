<?php

namespace App\Repositories\Contracts;

use App\Models\ExchangeRate;
use Carbon\CarbonInterface;

/**
 * Contract for retrieving exchange rates.
 *
 * Defines operations to resolve the most recent rate for a currency pair
 * at or before a given date (supports rollback to the last available date).
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
}
