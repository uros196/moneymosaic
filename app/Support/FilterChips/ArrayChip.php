<?php

namespace App\Support\FilterChips;

/**
 * ArrayChip represents a chip for an array of scalar values.
 *
 * It joins values with a comma for display and is empty when there are no values.
 */
class ArrayChip extends AbstractChip
{
    /**
     * Creates a new ArrayChip instance.
     */
    public function __construct(protected ?array $values)
    {
        $this->removeKeys('items');
    }

    /**
     * Static factory method to create a new ArrayChip instance.
     */
    public static function make(?array $values): self
    {
        return new self($values);
    }

    /**
     * Processes the normalized values into a string representation.
     * Converts all values to strings and joins them with commas.
     */
    protected function proceedValue(): string
    {
        $vals = array_map(static fn ($v) => (string) $v, $this->normalized());

        return implode(', ', $vals);
    }

    /**
     * Returns the default label for the chip.
     */
    protected function initialLabel(): string
    {
        return __('Items');
    }

    /**
     * Checks if the chip has no values after normalization.
     */
    public function isEmpty(): bool
    {
        return count($this->normalized()) === 0;
    }

    /**
     * Normalizes the chip values by trimming strings and removing empty values.
     * Null values and empty strings are filtered out.
     */
    protected function normalized(): array
    {
        if ($this->values === null) {
            return [];
        }

        // Trim strings and filter out empty values
        $norm = array_map(static function ($v) {
            if (is_string($v)) {
                $v = trim($v);
            }

            return $v;
        }, $this->values);

        return array_values(array_filter($norm, static function ($v) {
            if ($v === null) {
                return false;
            }

            if (is_string($v)) {
                return $v !== '';
            }

            return true;
        }));
    }
}
