<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use App\Repositories\Contracts\ExchangeRateRepository as ExchangeRateRepositoryContract;
use Carbon\CarbonInterface;

class ExchangeRateRepository implements ExchangeRateRepositoryContract
{
    /**
     * Find the latest exchange rate record for a base->quote pair on or before the given date.
     *
     * @param  string  $baseCurrency  ISO 4217 base currency code (e.g., EUR).
     * @param  string  $quoteCurrency  ISO 4217 quote currency code (e.g., USD).
     * @param  CarbonInterface  $date  The date to search on or before.
     * @return ExchangeRate|null The latest matching rate or null if none exists.
     */
    public function findLatestOnOrBefore(string $baseCurrency, string $quoteCurrency, CarbonInterface $date): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('base_currency_code', strtoupper($baseCurrency))
            ->where('quote_currency_code', strtoupper($quoteCurrency))
            ->whereDate('date', '<=', $date->toDateString())
            ->orderByDesc('date')
            ->first();
    }
}
