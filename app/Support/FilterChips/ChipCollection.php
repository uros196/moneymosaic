<?php

namespace App\Support\FilterChips;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A collection class for managing filter chips.
 *
 * This class provides functionality to create and manage a collection of filter chips,
 * implementing the Arrayable interface for easy conversion to array format.
 */
class ChipCollection implements Arrayable
{
    /**
     * Array of filter chips stored in this collection.
     *
     * @var AbstractChip[]
     */
    protected array $chips = [];

    /**
     * Creates a new empty chip collection instance.
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Creates a new chip collection from an array of chips.
     *
     * @param  AbstractChip[]  $chips  Array of filter chips
     */
    public static function fromArray(array $chips): self
    {
        $collection = self::make();

        foreach ($chips as $chip) {
            $collection->addChip($chip);
        }

        return $collection;
    }

    /**
     * Adds a single chip to the collection.
     */
    public function addChip(AbstractChip $chip): self
    {
        $this->chips[] = $chip;

        return $this;
    }

    /**
     * Converts the collection to an array, filtering out empty chips.
     */
    public function toArray(): array
    {
        return collect($this->chips)
            ->filter(fn (AbstractChip $chip) => ! $chip->isEmpty())
            ->values()
            ->toArray();
    }
}
