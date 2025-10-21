<?php

namespace App\Rules\Pagination;

use App\Support\TableConfig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Validates an optional perPage parameter against configured options.
 */
class PerPageRule implements ValidationRule
{
    public function __construct(protected ?string $tableKey = null) {}

    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pagingData = TableConfig::paging($this->tableKey);

        // Group all validation rules in one place
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [
                'nullable', 'integer', Rule::in(data_get($pagingData, 'options', [])),
            ]]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}
