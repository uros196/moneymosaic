<?php

namespace App\Support\FilterChips;

use Illuminate\Contracts\Support\Arrayable;

/**
 * AbstractChip serves as a base class for filter chips implementation.
 *
 * Filter chips represent selectable filtering options that can be applied to data.
 * Each chip has a label, value, and optional keys that can be removed when the chip is deselected.
 */
abstract class AbstractChip implements Arrayable
{
    /**
     * The display label for the filter chip.
     */
    protected ?string $label;

    /**
     * Keys to be removed from the query when the chip is deselected.
     */
    protected array $removeKeys = [];

    /**
     * Sets the display label for the filter chip.
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Returns the default label when no custom label is set.
     */
    protected function initialLabel(): string
    {
        return 'Chip';
    }

    /**
     * Processes and returns the chip's value as a string.
     */
    abstract protected function proceedValue(): string;

    /**
     * Determines if the chip has no value.
     */
    abstract public function isEmpty(): bool;

    /**
     * Sets the keys to be removed from the query when the chip is deselected.
     */
    public function removeKeys(mixed $keys): self
    {
        $this->removeKeys = is_array($keys) ? $keys : func_get_args();

        return $this;
    }

    /**
     * Converts the chip to its array representation.
     */
    public function toArray(): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        return [
            'label' => $this->label ?? $this->initialLabel(),
            'valueLabel' => $this->proceedValue(),
            'removeKeys' => $this->removeKeys,
        ];
    }
}
