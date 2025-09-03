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

    /**
     * Default display / base currency for the app.
     */
    public static function default(): self
    {
        return self::EUR;
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
     * Common currency symbol for UI display.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::USD => '$',
            self::EUR => '€',
            self::RSD => 'RSD',
        };
    }

    /**
     * Number of fraction digits typically used for this currency.
     */
    public function fractionDigits(): int
    {
        return match ($this) {
            // All configured currencies use 2 decimals; amounts are stored in minor units (integers)
            self::USD, self::EUR, self::RSD => 2,
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
            self::USD, self::EUR, self::RSD => '0.01',
        };
    }
}
