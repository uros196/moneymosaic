<?php

namespace App\Support\FilterChips;

use App\Enums\Currency;
use App\Support\Money;

/**
 * MinorMinMaxRangeChip handles min-max range filtering for monetary values stored in minor units.
 * Extends the base MinMaxRangeChip to provide currency-aware value conversion.
 */
class MinorMinMaxRangeChip extends MinMaxRangeChip
{
    /**
     * Currency to be used for monetary value conversion.
     */
    protected ?Currency $currency = null;

    /**
     * Retrieves and converts a range value from minor to major units using the specified currency.
     */
    protected function getRangeValue(string $name): ?string
    {
        if ($value = parent::getRangeValue($name)) {
            // Convert the value to major using the passed currency for better precision
            return Money::fromMinor((int) $value, $this->currency ?? Currency::default());
        }

        return $value;
    }

    /**
     * Sets the currency to be used for monetary value conversion.
     */
    public function usingCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
