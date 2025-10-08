<?php

namespace App\Enums;

/**
 * Currency enum used across the application.
 *
 * Backed by ISO 4217 currency codes (strings) and provides
 * useful helpers for UI/formatting.
 */
enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case RSD = 'RSD';
    case GBP = 'GBP';
    case CHF = 'CHF';
    case CAD = 'CAD';

    /**
     * Default display / base currency for the app.
     */
    public static function default(): self
    {
        return self::from(config('exchange.base_currency'));
    }

    /**
     * List of backed values (ISO codes).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }

    /**
     * Human-friendly, translatable currency name.
     */
    public function label(): string
    {
        return __("common.currencies.{$this->value}");
    }

    /**
     * Human-friendly, translatable currency name with its code.
     */
    public function displayLabel(): string
    {
        return $this->label()." ({$this->value})";
    }

    /**
     * Returns a formatted label suitable for display in UI chip components.
     *
     * Provides the same output as displayLabel(), maintaining consistency
     * across UI components that need to show currency information.
     */
    public function toChipLabel(): string
    {
        return $this->displayLabel();
    }

    /**
     * Common currency symbol for UI display.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::USD => '$',
            self::EUR => '€',
            self::RSD => 'RSD',
            self::GBP => '£',
            self::CHF => 'CHF',
            self::CAD => 'CA$',
        };
    }

    /**
     * Number of fraction digits typically used for this currency.
     */
    public function fractionDigits(): int
    {
        return match ($this) {
            // All configured currencies here use 2 decimals; amounts are stored in minor units (integers)
            self::USD, self::EUR, self::RSD, self::GBP, self::CHF, self::CAD => 2,
        };
    }

    /**
     * Get the minimum step value for currency amount adjustments.
     *
     * Defines the smallest increment/decrement allowed when modifying amounts
     * in this currency. Used for input validation and UI controls.
     */
    public function step(): string
    {
        return match ($this) {
            self::USD, self::EUR, self::RSD, self::GBP, self::CHF, self::CAD => '0.01',
        };
    }

    /**
     * Template describing how to place the currency symbol relative to the amount.
     *
     * Use placeholders:
     * - {symbol} will be replaced with the currency symbol.
     * - {amount} will be replaced with the major amount string.
     *
     * Examples for patterns:
     * - USD: "{symbol}{amount}"
     * - EUR: "{amount}{symbol}"
     * - RSD: "{amount} {symbol}"
     */
    public function formatTemplate(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD => '{symbol}{amount}',
            self::EUR => '{amount}{symbol}',
            self::RSD, self::CHF => '{amount} {symbol}',
        };
    }

    /**
     * 'Decimal' separator to use when displaying amounts for this currency.
     */
    public function decimalSeparator(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD, self::CHF => '.',
            self::EUR, self::RSD => ',',
        };
    }

    /**
     * 'Thousands' separator to use when displaying amounts for this currency.
     */
    public function thousandsSeparator(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD => ',',
            self::EUR, self::RSD => '.',
            // Switzerland commonly uses an apostrophe for grouping
            self::CHF => "'",
        };
    }
}
