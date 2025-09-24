<?php

namespace App\DTO\ExchangeRates;

use Carbon\CarbonInterface;

/**
 * Represents daily exchange rates for a given base currency.
 *
 * quotes: array of RateQuote objects for quote currencies relative to base.
 */
final class DailyRates
{
    /** @param array<int, RateQuote> $quotes */
    public function __construct(
        public CarbonInterface $date,
        public string $base,
        public array $quotes = [],
    ) {
        $this->base = strtoupper($this->base);
    }

    /**
     * Map of quote currency => rate multiplier, including base -> 1.0.
     *
     * @return array<string, float>
     */
    public function toMap(): array
    {
        $map = [];
        foreach ($this->quotes as $quote) {
            $map[strtoupper($quote->quote)] = (float) $quote->multiplier;
        }

        // Ensure base->base
        $map[$this->base] = 1.0;

        return $map;
    }
}
