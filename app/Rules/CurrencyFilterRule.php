<?php

namespace App\Rules;

use App\Enums\Currency;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Validates an optional currency filter value.
 *
 * Accepts null or a valid ISO code backed by the Currency enum.
 */
class CurrencyFilterRule implements ValidationRule
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
                'sometimes', 'string', Rule::enum(Currency::class),
            ]]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}
