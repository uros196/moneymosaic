<?php

namespace App\Support\FilterChips;

/**
 * MinMaxRangeChip represents a numeric (or comparable scalar) range chip.
 *
 * Displays as "min – max" with em dash placeholder when one side is missing.
 */
class MinMaxRangeChip extends AbstractChip
{
    /**
     * Create a new MinMaxRangeChip instance.
     */
    public function __construct(protected mixed $min, protected mixed $max)
    {
        $this->removeKeys('min', 'max');
    }

    /**
     * Create a new MinMaxRangeChip instance.
     */
    public static function make(mixed $min, mixed $max): static
    {
        return new static($min, $max);
    }

    /**
     * Format the range value as a string.
     * Uses em dash (—) as a placeholder between values.
     */
    protected function proceedValue(): string
    {
        $left = $this->getRangeValue('min');
        $right = $this->getRangeValue('max');

        if (! is_null($left) && ! is_null($right)) {
            return sprintf('%s – %s', $left, $right);
        }

        return ! is_null($left)
            ? __('common.from_value', ['value' => $left])
            : __('common.to_value', ['value' => $right]);

    }

    /**
     * Get the range value as a string.
     */
    protected function getRangeValue(string $name): ?string
    {
        $value = $this->{$name};

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * Get the default label for the range chip.
     */
    protected function initialLabel(): string
    {
        return __('Range');
    }

    /**
     * Check if the range is empty (both min and max are null or empty string).
     */
    public function isEmpty(): bool
    {
        return ($this->min === null || $this->min === '') && ($this->max === null || $this->max === '');
    }
}
