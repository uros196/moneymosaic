<?php

namespace App\DTO\ExchangeRates;

/**
 * Represents a single exchange rate quote relative to a base currency.
 */
final class RateQuote
{
    public function __construct(
        public string $base,
        public string $quote,
        public float $multiplier,
    ) {
        $this->base = strtoupper($this->base);
        $this->quote = strtoupper($this->quote);
    }
}
