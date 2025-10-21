<?php

namespace App\Support\Filters\Chips;

use Illuminate\Support\Carbon;

/**
 * DateRangeChip represents a filter chip for date range selection.
 *
 * This class handles the display and management of date range filters,
 * showing a date range with optional start and end dates.
 */
class DateRangeChip extends AbstractChip
{
    /**
     * Create a new DateRangeChip instance.
     */
    public function __construct(protected ?Carbon $dateFrom, protected ?Carbon $dateTo)
    {
        // Set default keys to remove
        $this->removeKeys('date_from', 'date_to');
    }

    /**
     * Create a new DateRangeChip instance.
     */
    public static function make(?Carbon $dateFrom, ?Carbon $dateTo): self
    {
        return new self($dateFrom, $dateTo);
    }

    /**
     * Format the date range value for display.
     */
    protected function proceedValue(): string
    {
        if (! is_null($this->dateFrom) && ! is_null($this->dateTo)) {
            return sprintf('%s → %s', $this->convert($this->dateFrom), $this->convert($this->dateTo));
        }

        return ! is_null($this->dateFrom)
            ? __('common.from_value', ['value' => $this->convert($this->dateFrom)])
            : __('common.to_value', ['value' => $this->convert($this->dateTo)]);
    }

    /**
     * Convert the date to a string representation.
     */
    protected function convert(Carbon $date): string
    {
        return $date->format('d.m.Y');
    }

    /**
     * Get the initial label for the date range chip.
     */
    protected function initialLabel(): string
    {
        return __('Date');
    }

    /**
     * Check if the date range is empty (both dates are null).
     */
    public function isEmpty(): bool
    {
        return is_null($this->dateFrom) && is_null($this->dateTo);
    }
}
