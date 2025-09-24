<?php

namespace App\Services\ExchangeRates;

use App\DTO\ExchangeRates\DailyRates;
use Carbon\CarbonInterface;

/**
 * Strategy contract for exchange rate providers.
 */
interface RateProvider
{
    /**
     * Fetch daily rates for the given date.
     *
     * @param  string  $baseCurrency  The base currency code (ISO 4217) for the requested rates.
     * @param  array<int,string>  $currencies  A list of quote currency codes to request.
     */
    public function getRatesForDate(CarbonInterface $date, string $baseCurrency, array $currencies): DailyRates;

    /**
     * Fetch daily rates for a timeframe (inclusive dates).
     *
     * @param  string  $baseCurrency  The base currency code (ISO 4217) for the requested rates.
     * @param  array<int,string>  $currencies  A list of quote currency codes to request.
     * @return array<int, DailyRates> Ordered by date ascending.
     */
    public function getRatesForRange(CarbonInterface $startDate, CarbonInterface $endDate, string $baseCurrency, array $currencies): array;
}
