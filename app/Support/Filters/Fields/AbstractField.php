<?php

namespace App\Support\Filters\Fields;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Base class for server-driven filter field definitions.
 *
 * Each field serializes itself into a shape the React FilterSheet already understands.
 */
abstract class AbstractField implements Arrayable
{
    /**
     * Field type constant used by the frontend renderer.
     */
    public const string TYPE = 'input';

    public function __construct(protected string $key, protected string $label) {}

    /**
     * Fluent factory.
     */
    public static function make(string $key, string $label): static
    {
        return new static($key, $label);
    }

    /**
     * Serialize common props for all fields.
     */
    protected function base(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => static::TYPE,
        ];
    }

    /**
     * Child-specific props to be merged into the base field payload.
     *
     * Do not include common keys like 'key', 'label', or 'type' — they are provided by base().
     */
    abstract protected function specific(): array;

    /**
     * Serialize field definition for the frontend.
     */
    public function toArray(): array
    {
        return $this->base() + array_filter(
            $this->specific(),
            static fn ($v) => $v !== null && $v !== []
        );
    }

    /**
     * Query parameter keys controlled by this field.
     *
     * By default, a field maps to a single key (its base $key). Complex fields
     * (e.g. date-range, min-max) should override this to expose all keys.
     */
    public function queryKeys(): array
    {
        return [$this->key];
    }
}
