<?php

namespace App\Services;

use App\Enums\Currency;
use App\Repositories\Contracts\ExchangeRateRepository;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * CurrencyConversionService
 *
 * Responsibilities:
 * - Convert integer monetary amounts (minor units) between currencies using daily exchange rates.
 * - Resolve rates by transaction date with rollback to the most recent available date (bounded by lookback).
 * - Cache rate lookups to minimize database load.
 *
 * Conventions:
 * - Amounts are integers in minor units (e.g., cents) – never floats for storage/transport.
 * - Conversion is performed via the configured base currency when necessary (two-leg conversion: from -> base -> to).
 * - Rounding is banker's simple rounding using PHP round() to the nearest minor unit.
 */
class CurrencyConversionService
{
    public function __construct(public ?ExchangeRateRepository $repository = null)
    {
        $this->repository ??= app(ExchangeRateRepository::class);
    }

    /**
     * Convert amount (minor units) from one currency to another by transaction date.
     */
    public function convert(int $amountMinor, string $fromCurrency, string $toCurrency, CarbonInterface $date, int $maxLookbackDays = 7): int
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency);

        if ($amountMinor === 0 || $from === $to) {
            return $amountMinor;
        }

        $base = Currency::default()->value;

        // If converting from base to target or from source to base, we need only one leg.
        if ($from === $base) {
            $rateBaseToTo = $this->getRate($base, $to, $date, $maxLookbackDays);

            return (int) round($amountMinor * (float) $rateBaseToTo);
        }

        if ($to === $base) {
            $rateBaseToFrom = $this->getRate($base, $from, $date, $maxLookbackDays);

            // amount_in_base = amount_in_from / rate(base->from)
            return (int) round($amountMinor / (float) $rateBaseToFrom);
        }

        // General case: from -> base -> to
        $rateBaseToFrom = $this->getRate($base, $from, $date, $maxLookbackDays);
        $rateBaseToTo = $this->getRate($base, $to, $date, $maxLookbackDays);

        // amount_in_base_minor = amountMinor / rate(base->from)
        $amountInBaseMinor = $amountMinor / (float) $rateBaseToFrom;
        // amount_in_target_minor = amount_in_base_minor * rate(base->to)
        $amountInTargetMinor = $amountInBaseMinor * (float) $rateBaseToTo;

        return (int) round($amountInTargetMinor);
    }

    /**
     * Get rate multiplier for pair base->quote on or before date, rolling back up to $lookbackDays.
     */
    public function getRate(string $base, string $quote, CarbonInterface $date, int $lookbackDays = 7): float
    {
        $base = strtoupper($base);
        $quote = strtoupper($quote);

        if ($base === $quote) {
            return 1.0;
        }

        $cacheKey = sprintf('rate:%s:%s:%s:%d', $base, $quote, $date->toDateString(), $lookbackDays);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($base, $quote, $date, $lookbackDays) {
            $record = $this->repository->findLatestOnOrBefore($base, $quote, $date);

            if ($record === null) {
                throw new RuntimeException("Rate $base->$quote not found on or before {$date->toDateString()}");
            }

            $diffDays = $record->date->diffInDays($date);
            if ($diffDays > $lookbackDays) {
                throw new RuntimeException("Rate $base->$quote older than {$lookbackDays} days (found {$diffDays} days old)");
            }

            return (float) $record->rate_multiplier;
        });
    }
}
