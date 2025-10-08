<?php

namespace App\Support\FilterChips;

/**
 * StringChip represents a simple string-based filter chip.
 *
 * Useful for free-text search or any single string value filter.
 */
class StringChip extends AbstractChip
{
    /**
     * Constructor for StringChip.
     */
    public function __construct(protected ?string $value)
    {
        $this->removeKeys('value');
    }

    /**
     * Creates a new instance of StringChip.
     */
    public static function make(?string $value): self
    {
        return new self($value);
    }

    /**
     * Returns the processed value of the chip.
     */
    protected function proceedValue(): string
    {
        return $this->value;
    }

    /**
     * Returns the default label for the chip.
     */
    protected function initialLabel(): string
    {
        return __('Search');
    }

    /**
     * Checks if the chip value is empty.
     */
    public function isEmpty(): bool
    {
        return $this->value === null || trim($this->value) === '';
    }
}
