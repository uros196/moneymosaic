<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * A validation rule for sorting columns.
 * This rule ensures that the sorting column value is present in the allowed columns list.
 */
class SortingRule implements ValidationRule
{
    /**
     * The list of allowed column names for sorting.
     */
    protected array $columns;

    /**
     * Create a new sorting rule instance.
     *
     * @param  Arrayable|\BackedEnum|\UnitEnum|array|string  $columns  The allowed column names for sorting
     */
    public function __construct($columns)
    {
        if ($columns instanceof Arrayable) {
            $columns = $columns->toArray();
        }

        $this->columns = is_array($columns) ? $columns : func_get_args();
    }

    /**
     * Validate the sorting attribute value.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        [$column, $direction] = $this->splitData($value);

        $validator = Validator::make(
            [$attribute => $column, 'direction' => strtolower($direction)],
            [
                // Validate if the column is present in the allowed columns list
                $attribute => ['nullable', 'string', Rule::in($this->columns)],

                // Validate if the direction is either 'asc' or 'desc'
                'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            ]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }

    /**
     * Split the sorting data string into column and direction components.
     *
     * @param  string|null  $value  The sorting data string in format "column:direction"
     * @return array{0: string|null, 1: string|null} An array containing [column, direction]
     */
    protected function splitData(?string $value): array
    {
        if ($value === null) {
            return [null, null];
        }

        $sortable = explode(':', $value, 2);

        return [
            data_get($sortable, 0),
            data_get($sortable, 1),
        ];
    }
}
