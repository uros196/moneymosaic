<?php

namespace App\Support\Filters\Fields;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

/**
 * Manages a collection of filter fields and provides serialization and metadata functionality.
 */
class FilterFieldsCollection implements Arrayable
{
    /**
     * Array of filter field definitions.
     *
     * @var array<AbstractField>
     */
    protected array $fields = [];

    /**
     * Creates a new instance of FieldsCollection.
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Adds a new field to the collection.
     */
    public function add(AbstractField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Produce a payload structure with fields and meta using a request.
     */
    public function payload(?Request $request = null): array
    {
        return [
            'fields' => $this->serializeFields(),
            'meta' => $this->meta(
                $request ?? Container::getInstance()->make('request')
            ),
        ];
    }

    /**
     * Serialize only fields; used internally.
     */
    protected function serializeFields(): array
    {
        return array_map(static fn (AbstractField $f) => $f->toArray(), $this->fields);
    }

    /**
     * Compute meta like total fields and how many are currently applied based on query params.
     */
    protected function meta(Request $request): array
    {
        // Ask each field which query keys it controls; no type inspection here.
        $keys = [];
        foreach ($this->fields as $field) {
            $keys = array_merge($keys, $field->queryKeys());
        }

        // Determine how many of those keys are currently applied (present & filled in request)
        $applied = 0;
        foreach ($keys as $key) {
            if ($request->filled($key)) {
                $applied++;
            }
        }

        return [
            'total' => count($this->fields),
            'applied' => $applied,
            'keys' => array_values($keys),
        ];
    }

    /**
     * By default, toArray returns only the field list.
     */
    public function toArray(): array
    {
        return $this->payload();
    }
}
