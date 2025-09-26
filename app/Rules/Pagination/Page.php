<?php

namespace App\Rules\Pagination;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Validates an optional page number (>= 1).
 */
class Page implements ValidationRule
{
    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Group all validation rules in one place
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [
                'nullable', 'integer', 'min:1',
            ]]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}
