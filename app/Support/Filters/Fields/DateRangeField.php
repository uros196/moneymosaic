<?php

namespace App\Support\Filters\Fields;

/**
 * Date range field implementation for handling date range filter inputs.
 *
 * This class provides functionality for managing date range filters with from/to values.
 * It extends AbstractField and implements specific date range filtering capabilities
 * by managing separate keys for the start and end dates of the range.
 */
class DateRangeField extends AbstractField
{
    /**
     * The type identifier for this field
     */
    public const string TYPE = 'date-range';

    /**
     * The query parameter key for the 'from' date
     */
    protected ?string $fromKey = null;

    /**
     * The query parameter key for the 'to' date
     */
    protected ?string $toKey = null;

    /**
     * The placeholder text for the 'from' date input
     */
    protected ?string $fromPlaceholder = null;

    /**
     * The placeholder text for the 'to' date input
     */
    protected ?string $toPlaceholder = null;

    /**
     * By default, the field uses <key>_from and <key>_to as query keys.
     */
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);

        $this->keys("{$key}_from", "{$key}_to");
    }

    /**
     * Override query parameter keys used for from/to dates.
     */
    public function keys(string $fromKey, string $toKey): self
    {
        $this->fromKey = $fromKey;
        $this->toKey = $toKey;

        return $this;
    }

    /**
     * Set UI placeholders for 'from/to' date inputs.
     */
    public function placeholders(?string $from, ?string $to): self
    {
        $this->fromPlaceholder = $from;
        $this->toPlaceholder = $to;

        return $this;
    }

    /**
     * Expose all underlying query keys for meta/introspection.
     */
    public function queryKeys(): array
    {
        return [$this->fromKey, $this->toKey];
    }

    /**
     * Retrieve a specific set of query-related keys and placeholders.
     */
    protected function specific(): array
    {
        return [
            'fromKey' => $this->fromKey,
            'toKey' => $this->toKey,
            'fromPlaceholder' => $this->fromPlaceholder,
            'toPlaceholder' => $this->toPlaceholder,
        ];
    }
}
