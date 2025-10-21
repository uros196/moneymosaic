<?php

namespace App\Support;

use App\Enums\Currency;
use InvalidArgumentException;

/**
 * Money helper utilities.
 *
 * All monetary amounts in the database are stored as integers in minor units.
 * This helper provides safe conversion from user inputs (strings / floats)
 * to minor units based on the currency's fraction digits.
 */
final class Money
{
    /**
     * Convert a value to minor units for the given currency.
     *
     * Rules:
     * - If value is an int, it's assumed to already be in minor units and returned as-is.
     * - If value is a float, it is formatted to the currency's fraction digits and converted.
     * - If value is a string, it is normalized (handles comma/dot decimals, a thousand separators)
     *   and converted using rounding half up to the currency's fraction digits.
     */
    public static function toMinor(int|float|string $value, Currency $currency): int
    {
        if (is_int($value)) {
            return $value;
        }

        $stringValue = is_float($value)
            ? self::floatToFixedString($value, $currency->fractionDigits())
            : trim((string) $value);

        $normalized = self::normalizeDecimalString($stringValue);

        return self::decimalStringToMinor($currency, $normalized);
    }

    /**
     * Convert a minor units integer into a major amount string with the proper number of fraction digits.
     *
     * Example: fromMinor(123456, EUR) => "1234.56"; fromMinor(-99, USD) => "-0.99"
     */
    public static function fromMinor(int $minor, Currency $currency): string
    {
        $fd = $currency->fractionDigits();

        $sign = $minor < 0 ? '-' : '';
        $abs = (string) abs($minor);

        if ($fd === 0) {
            return $sign.$abs;
        }

        // Ensure the string has at least fd+1 digits to safely split
        if (strlen($abs) <= $fd) {
            $intPart = '0';
            $fracPart = str_pad($abs, $fd, '0', STR_PAD_LEFT);
        } else {
            $intPart = substr($abs, 0, -$fd);
            $fracPart = substr($abs, -$fd);
        }

        // Trim trailing zeros in a fraction while keeping at least one zero if a fraction is non-empty
        $fracTrimmed = rtrim($fracPart, '0');
        if ($fracTrimmed === '') {
            // No fractional significance, return integer part only
            return $sign.$intPart;
        }

        return $sign.$intPart.'.'.$fracTrimmed;
    }

    /**
     * Normalize a decimal string:
     * - Removes spaces, underscores, apostrophes (common thousand separators)
     * - Converts commas to dots
     * - Keeps only digits and one decimal dot (the last occurrence)
     * - Preserves leading sign if present
     */
    private static function normalizeDecimalString(string $input): string
    {
        if ($input === '') {
            throw new InvalidArgumentException('Empty monetary value.');
        }

        // Trim and strip common thousand separators
        $s = str_replace([' ', "\u{00A0}", '_', "'"], '', trim($input));

        if ($s === '') {
            throw new InvalidArgumentException('Empty monetary value.');
        }

        // Extract and preserve sign
        $sign = '';
        if ($s[0] === '+' || $s[0] === '-') {
            $sign = $s[0];
            $s = substr($s, 1);
        }

        // Convert commas to dots for decimal separation
        $s = str_replace(',', '.', $s);

        // Keep only digits and a single (last) dot
        $lastDot = strrpos($s, '.');
        if ($lastDot !== false) {
            $int = preg_replace('/[^0-9]/', '', substr($s, 0, $lastDot));
            $frac = preg_replace('/[^0-9]/', '', substr($s, $lastDot + 1));
            $s = $int.'.'.$frac;
        } else {
            $s = preg_replace('/[^0-9]/', '', $s);
        }

        if ($s === '' || $s === '.') {
            throw new InvalidArgumentException('Invalid monetary value.');
        }

        return $sign.$s;
    }

    /**
     * Convert a normalized decimal string into minor units using HALF-UP rounding.
     */
    private static function decimalStringToMinor(Currency $currency, string $decimal): int
    {
        $fd = $currency->fractionDigits();

        // Extract sign
        $sign = 1;
        if ($decimal[0] === '+') {
            $decimal = substr($decimal, 1);
        } elseif ($decimal[0] === '-') {
            $sign = -1;
            $decimal = substr($decimal, 1);
        }

        $parts = explode('.', $decimal, 2);
        $intPart = $parts[0] !== '' ? $parts[0] : '0';
        $fracPart = $parts[1] ?? '';

        // Strip leading zeros in integer part (keep at least one zero)
        $intPart = ltrim($intPart, '0');
        if ($intPart === '') {
            $intPart = '0';
        }

        if ($fd === 0) {
            $baseStr = $intPart;
        } else {
            // Build base string with desired fraction digits, applying HALF-UP rounding if needed
            if (strlen($fracPart) > $fd) {
                $roundDigit = (int) $fracPart[$fd];
                $kept = substr($fracPart, 0, $fd);
                $baseStr = $intPart.($kept === '' ? str_repeat('0', $fd) : $kept);
                if ($roundDigit >= 5) {
                    $baseStr = self::incrementNumericString($baseStr);
                }
            } else {
                $baseStr = $intPart.str_pad($fracPart, $fd, '0', STR_PAD_RIGHT);
            }
        }

        // Remove leading zeros
        $baseStr = ltrim($baseStr, '0');
        if ($baseStr === '') {
            $baseStr = '0';
        }

        // Ensure the string is numeric
        if (! preg_match('/^[0-9]+$/', $baseStr)) {
            throw new InvalidArgumentException('Invalid monetary numeric format.');
        }

        $minor = (int) $baseStr;

        return $sign * $minor;
    }

    /**
     * Increment a numeric string (non-negative) by 1, handling carry.
     */
    private static function incrementNumericString(string $num): string
    {
        $carry = 1;
        $chars = str_split($num);
        for ($i = count($chars) - 1; $i >= 0; $i--) {
            $d = (int) $chars[$i] + $carry;
            if ($d >= 10) {
                $chars[$i] = '0';
                $carry = 1;
            } else {
                $chars[$i] = (string) $d;
                $carry = 0;
                break;
            }
        }

        if ($carry === 1) {
            array_unshift($chars, '1');
        }

        return implode('', $chars);
    }

    /**
     * Format a float to a fixed-decimal string without scientific notation.
     */
    private static function floatToFixedString(float $value, int $decimals): string
    {
        if (is_nan($value) || is_infinite($value)) {
            throw new InvalidArgumentException('Invalid float monetary value.');
        }

        // Using sprintf with F to avoid locale and scientific notation issues
        return sprintf('%.'.$decimals.'F', $value);
    }

    /**
     * Format a major amount string with a currency symbol using the currency's template.
     *
     * Examples:
     * - USD: "$200"; negative: "-$200"
     * - EUR: "200€"; negative: "-200€"
     * - RSD: "200 RSD"; negative: "-200 RSD"
     */
    public static function formatMajor(string $amountMajor, Currency $currency): string
    {
        $negative = str_starts_with($amountMajor, '-');
        $abs = $negative ? substr($amountMajor, 1) : $amountMajor;

        $number = self::formatNumberForCurrency($abs, $currency);

        $pattern = $currency->formatTemplate();
        $formatted = strtr($pattern, [
            '{symbol}' => $currency->symbol(),
            '{amount}' => $number,
        ]);

        return $negative ? '-'.$formatted : $formatted;
    }

    /**
     * Format a minor units integer with currency symbol according to the currency's template.
     */
    public static function formatMinor(int $minor, Currency $currency): string
    {
        return self::formatMajor(self::fromMinor($minor, $currency), $currency);
    }

    /**
     * Format a plain major amount string (e.g. "12550.10" or "1000") with thousands and
     * decimal separators according to the currency's display convention.
     */
    private static function formatNumberForCurrency(string $amountMajor, Currency $currency): string
    {
        // Split integer and fractional parts using '.' as produced by fromMinor()
        $parts = explode('.', $amountMajor, 2);
        $int = $parts[0] !== '' ? $parts[0] : '0';
        $frac = $parts[1] ?? '';

        // Group integer part with 'thousands' separator
        $intGrouped = self::groupThousands($int, $currency->thousandsSeparator());

        // If there is no fractional part, return grouped integer only
        if ($frac === '') {
            return $intGrouped;
        }

        return $intGrouped.$currency->decimalSeparator().$frac;
    }

    /**
     * Insert 'thousands' separators into a non-negative integer string.
     */
    private static function groupThousands(string $intPart, string $sep): string
    {
        $len = strlen($intPart);
        if ($len <= 3) {
            return $intPart;
        }

        $out = '';
        $count = 0;
        for ($i = $len - 1; $i >= 0; $i--) {
            $out = $intPart[$i].$out;
            $count++;
            if ($count === 3 && $i !== 0) {
                $out = $sep.$out;
                $count = 0;
            }
        }

        return $out;
    }
}
