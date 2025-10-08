<?php

namespace App\DTO;

/**
 * Data Transfer Object for handling sort parameters.
 * Contains field name and sort direction information.
 */
final readonly class Sort
{
    /**
     * Create a new Sort instance.
     */
    public function __construct(public string $field, public string $direction) {}

    /**
     * Create a class instance from a string representation.
     * Expected format: "field:direction".
     */
    public static function fromString(string $sort): self
    {
        $sortable = explode(':', $sort, 2);

        return new self(
            data_get($sortable, 0),
            data_get($sortable, 1, 'asc')
        );
    }
}
