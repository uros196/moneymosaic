<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Validates that the provided income type identifier is an integer
 * and exists either globally (user_id is null) or for the current user.
 */
class IncomeTypeRule implements ValidationRule
{
    public function __construct(protected User $user) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Group rules to keep consistency with other rules in the app
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [
                'integer',
                Rule::exists('income_types', 'id')->where(function ($q): void {
                    $q->whereNull('user_id')->orWhere('user_id', $this->user->id);
                }),
            ]]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}
