<?php

namespace App\Support\Filters\Fields;

/**
 * Numeric min/max filter field implementation that handles minimum and maximum value filtering.
 *
 * This field type is used for filtering numeric ranges where both minimum and maximum
 * bounds can be specified. By default, uses <key>_min and <key>_max as query parameters.
 */
class MinMaxField extends AbstractField
{
    /**
     * Field type identifier.
     */
    public const string TYPE = 'min-max';

    /**
     * The query parameter key for minimum value filtering.
     */
    protected ?string $minKey = null;

    /**
     * The query parameter key for maximum value filtering.
     */
    protected ?string $maxKey = null;

    /**
     * Placeholder text for the minimum value input field.
     */
    protected ?string $minPlaceholder = null;

    /**
     * Placeholder text for the maximum value input field.
     */
    protected ?string $maxPlaceholder = null;

    /**
     * By default, the field uses <key>_min and <key>_max as query keys.
     */
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->keys("{$key}_min", "{$key}_max");
    }

    /**
     * Override query parameter keys used for min and max.
     */
    public function keys(string $minKey, string $maxKey): self
    {
        $this->minKey = $minKey;
        $this->maxKey = $maxKey;

        // If error keys were not explicitly overridden, default them to both range keys
        if ($this->errorKeys === null) {
            $this->errorKeys($minKey, $maxKey);
        }

        return $this;
    }

    /**
     * UI placeholders for min and max inputs.
     */
    public function placeholders(?string $min, ?string $max): self
    {
        $this->minPlaceholder = $min;
        $this->maxPlaceholder = $max;

        return $this;
    }

    /**
     * Expose all underlying query keys for meta/introspection.
     */
    public function queryKeys(): array
    {
        return [$this->minKey, $this->maxKey];
    }

    /**
     * Retrieve specific query parameters including keys and placeholders.
     */
    protected function specific(): array
    {
        return [
            'minKey' => $this->minKey,
            'maxKey' => $this->maxKey,
            'minPlaceholder' => $this->minPlaceholder,
            'maxPlaceholder' => $this->maxPlaceholder,
        ];
    }
}
