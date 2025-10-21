<?php

namespace App\Services;

use App\Enums\Currency;
use App\Repositories\Contracts\ExchangeRateRepository;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * CurrencyConversionService
 *
 * Responsibilities:
 * - Convert integer monetary amounts (minor units) between currencies using daily exchange rates.
 * - Resolve rates by transaction date with rollback to the most recent available date (or nearest if fallback enabled).
 * - Cache rate lookups to minimize a database load.
 *
 * Conventions:
 * - Amounts are integers in minor units (e.g., cents) – never floats for storage/transport.
 * - Conversion is performed via the configured base currency when necessary (two-leg conversion: from -> base -> to).
 * - Rounding is banker's simple rounding using PHP round() to the nearest minor unit.
 */
class CurrencyConversionService
{
    public function __construct(protected ExchangeRateRepository $repository) {}

    /**
     * Convert amount (major units) from one currency to another by transaction date.
     *
     * Accepts integer or float amounts and returns a rounded value in major units
     * using the target currency's fraction digits.
     */
    public function convert(int|float $amount, Currency|string $fromCurrency, Currency|string $toCurrency, CarbonInterface $date, bool $fallback = true): int|float
    {
        $from = $this->parseCurrency($fromCurrency);
        $to = $this->parseCurrency($toCurrency);

        if ((float) $amount === 0.0 || $from === $to) {
            return $amount;
        }

        $base = Currency::default()->value;
        $targetFractionDigits = Currency::from($to)->fractionDigits();

        // If converting from base to target or from source to base, we need only one leg.
        if ($from === $base) {
            $rateBaseToTo = $this->getRate($base, $to, $date, $fallback);

            $raw = (float) $amount * $rateBaseToTo;

            return $this->finalizeConverted($raw, $targetFractionDigits);
        }

        if ($to === $base) {
            $rateBaseToFrom = $this->getRate($base, $from, $date, $fallback);

            // amount_in_base_major = amount_in_from_major / rate(base->from)
            $raw = (float) $amount / $rateBaseToFrom;

            return $this->finalizeConverted($raw, $targetFractionDigits);
        }

        // General case: from -> base -> to
        $rateBaseToFrom = $this->getRate($base, $from, $date, $fallback);
        $rateBaseToTo = $this->getRate($base, $to, $date, $fallback);

        // amount_in_base_major = amount / rate(base->from)
        $amountInBase = (float) $amount / $rateBaseToFrom;
        // amount_in_target_major = amount_in_base * rate(base->to)
        $amountInTarget = $amountInBase * $rateBaseToTo;

        return $this->finalizeConverted($amountInTarget, $targetFractionDigits);
    }

    /**
     * Convert minor units (integer) to the target currency amount using conversion via amount.
     * It will first transform the minor units to a major amount based on the source currency
     * and then delegate to convert().
     */
    public function convertMinor(int $minor, Currency|string $fromCurrency, Currency|string $toCurrency, CarbonInterface $date, bool $fallback = true): int|float
    {
        $fromCode = $this->parseCurrency($fromCurrency);
        $fromEnum = Currency::from($fromCode);

        // Convert minor to a major amount string, then cast to float for math
        $amountMajor = (float) Money::fromMinor($minor, $fromEnum);

        return $this->convert($amountMajor, $fromEnum, $toCurrency, $date, $fallback);
    }

    /**
     * Get rate multiplier for pair base->quote for the given date.
     * If an exact match is not found and $fallback is true, the nearest available date (before or after)
     * will be used. If $fallback is false and the exact match is missing, an exception is thrown.
     */
    public function getRate(Currency|string $base, Currency|string $quote, CarbonInterface $date, bool $fallback = true): float
    {
        $base = $this->parseCurrency($base);
        $quote = $this->parseCurrency($quote);

        if ($base === $quote) {
            return 1.0;
        }

        $cacheKey = sprintf('rate:%s:%s:%s:%s', $base, $quote, $date->toDateString(), $fallback ? 'fb1' : 'fb0');

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($base, $quote, $date, $fallback) {
            $beforeOrSame = $this->repository->findLatestOnOrBefore($base, $quote, $date);

            if ($beforeOrSame !== null && $beforeOrSame->date->toDateString() === $date->toDateString()) {
                return (float) $beforeOrSame->rate_multiplier;
            }

            if ($fallback === false) {
                throw new RuntimeException("Exact rate $base->$quote not found for {$date->toDateString()}");
            }

            $afterOrSame = $this->repository->findEarliestOnOrAfter($base, $quote, $date);

            // If both sides exist (but before is not the same day), pick the nearest by absolute day difference.
            if ($beforeOrSame !== null && $beforeOrSame->date->toDateString() !== $date->toDateString()) {
                if ($afterOrSame === null) {
                    return (float) $beforeOrSame->rate_multiplier;
                }

                $beforeDiff = $beforeOrSame->date->diffInDays($date);
                $afterDiff = $afterOrSame->date->diffInDays($date);

                return (float) (($afterDiff < $beforeDiff) ? $afterOrSame->rate_multiplier : $beforeOrSame->rate_multiplier);
            }

            // If we didn't have a before record, try after; if neither exists, throw.
            if ($afterOrSame !== null) {
                return (float) $afterOrSame->rate_multiplier;
            }

            throw new RuntimeException("Rate $base->$quote not found near {$date->toDateString()}");
        });
    }

    /**
     * Finalize the converted value according to target currency fraction digits.
     * Rounds to the given number of fraction digits and returns int if effectively an integer,
     * otherwise returns a float.
     */
    private function finalizeConverted(float $raw, int $fractionDigits): int|float
    {
        $epsilon = 1e-6;
        $rounded = round($raw, $fractionDigits);

        if (abs($rounded - (int) $rounded) < $epsilon) {
            return (int) $rounded;
        }

        return $rounded;
    }

    /**
     * Parse a currency input into a standardized string representation.
     */
    protected function parseCurrency(Currency|string $currency): string
    {
        if (is_string($currency)) {
            $currency = Currency::from(strtoupper($currency));
        }

        return $currency->value;
    }
}
