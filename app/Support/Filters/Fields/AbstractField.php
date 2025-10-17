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

    /**
     * Optional list of error keys that should be used on the frontend to read validation errors
     * associated with this field. If not defined, defaults to the primary $key.
     */
    protected ?array $errorKeys = null;

    public function __construct(protected string $key, protected string $label) {}

    /**
     * Fluent factory.
     */
    public static function make(string $key, string $label): static
    {
        return new static($key, $label);
    }

    /**
     * Define which request error keys the frontend should check for this field.
     */
    public function errorKeys(string ...$keys): self
    {
        $this->errorKeys = array_values($keys);

        return $this;
    }

    /**
     * Get the effective error keys (defaults to base key if not overridden).
     *
     * @return array<string>
     */
    protected function getErrorKeys(): array
    {
        return $this->errorKeys ?? [$this->key];
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
            'errorKeys' => $this->getErrorKeys(),
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
