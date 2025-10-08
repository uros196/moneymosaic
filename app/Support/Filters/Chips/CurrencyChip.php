<?php

namespace App\Support\Filters\Chips;

use App\Enums\Currency;

/**
 * CurrencyChip represents a currency-based filter chip.
 */
class CurrencyChip extends AbstractChip
{
    /**
     * Create a new CurrencyChip instance.
     */
    public function __construct(protected ?Currency $currency)
    {
        $this->removeKeys('currency_code');
    }

    /**
     * Create a new CurrencyChip instance.
     */
    public static function make(?Currency $currency): self
    {
        return new self($currency);
    }

    /**
     * Format the currency value for display.
     */
    protected function proceedValue(): string
    {
        return $this->currency->displayLabel();
    }

    /**
     * Get the initial label for the currency chip.
     */
    protected function initialLabel(): string
    {
        return __('Currency');
    }

    /**
     * Check if the currency chip has no value.
     */
    public function isEmpty(): bool
    {
        return $this->currency === null;
    }
}
