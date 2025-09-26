<?php

namespace App\Repositories\Eloquent;

use App\Models\ExchangeRate;
use App\Repositories\Contracts\ExchangeRateRepository;
use Carbon\CarbonInterface;

class EloquentExchangeRateRepository implements ExchangeRateRepository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(protected ExchangeRate $model) {}

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
        return $this->model->query()
            ->where('base_currency_code', strtoupper($baseCurrency))
            ->where('quote_currency_code', strtoupper($quoteCurrency))
            ->whereDate('date', '<=', $date->toDateString())
            ->orderByDesc('date')
            ->first();
    }

    /**
     * Find the earliest exchange rate record for a base->quote pair on or after the given date.
     */
    public function findEarliestOnOrAfter(string $baseCurrency, string $quoteCurrency, CarbonInterface $date): ?ExchangeRate
    {
        return $this->model->query()
            ->where('base_currency_code', strtoupper($baseCurrency))
            ->where('quote_currency_code', strtoupper($quoteCurrency))
            ->whereDate('date', '>=', $date->toDateString())
            ->orderBy('date')
            ->first();
    }

    /**
     * Upsert a single exchange rate row for a given date and currency pair.
     */
    public function updateOrCreateRate(string $date, string $base, string $quote, float $multiplier): ExchangeRate
    {
        return $this->model->query()->updateOrCreate(
            [
                'date' => $date,
                'base_currency_code' => strtoupper($base),
                'quote_currency_code' => strtoupper($quote),
            ],
            [
                'rate_multiplier' => (float) $multiplier,
            ]
        );
    }
}
