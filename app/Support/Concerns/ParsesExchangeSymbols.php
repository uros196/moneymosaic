<?php

namespace App\Support\Concerns;

use App\Enums\Currency;

/**
 * Helper for parsing exchange symbols configured as a comma-separated string.
 *
 * Keep config('exchange.symbols') as a plain string (e.g. "USD,EUR,RSD").
 * Use this trait wherever you need an array of symbols.
 */
trait ParsesExchangeSymbols
{
    /**
     * Parse a list of symbols from a string or array to a normalized array.
     *
     * - Accepts comma-separated string or array input
     * - Trims whitespace, uppercases codes, removes empties and duplicates
     */
    protected function parseCurrenciesToArray(string|array|null $currencies): array
    {
        if ($currencies === null) {
            return [];
        }

        $list = is_array($currencies)
            ? $currencies
            : explode(',', $currencies);

        $normalized = array_map(static function ($s): string {
            return strtoupper(trim((string) $s));
        }, $list);

        // Remove empty values and duplicates, reindex
        $filtered = array_values(array_filter($normalized, static function ($s): bool {
            return $s !== '';
        }));

        return array_values(array_unique($filtered));
    }

    /**
     * Convert an array of currency codes to an uppercase comma-separated string.
     */
    public function parseCurrenciesToString(array $currencies): string
    {
        return implode(',', array_map('strtoupper', $currencies));
    }

    /**
     * Get the globally configured exchange symbols as an array.
     */
    protected function configuredSymbols(): array
    {
        /** @var string|array|null $symbols */
        $symbols = config('exchange.symbols');

        return $this->parseCurrenciesToArray($symbols);
    }

    /**
     * Get the globally configured base currency.
     */
    protected function configuredBaseCurrency(): string
    {
        return Currency::default()->value;
    }
}
