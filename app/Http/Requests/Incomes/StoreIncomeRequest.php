<?php

namespace App\Http\Requests\Incomes;

use App\Enums\Currency;
use App\Rules\IncomeTypeRule;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'occurred_on' => ['required', 'date'],
            'income_type_id' => ['required', new IncomeTypeRule($this->user())],
            'amount_minor' => ['required', 'integer', 'min:1'],
            'currency_code' => ['required', Rule::enum(Currency::class)],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation by merging additional attributes and
     * converting the monetary input to minor units using the Money helper.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->getKey(),
        ]);

        // Convert amount to minor units when possible
        $amount = $this->string('amount');
        $currency = $this->enum('currency_code', Currency::class);

        if (! is_null($amount) && ! is_null($currency)) {
            try {
                $this->merge([
                    'amount_minor' => Money::toMinor($amount, $currency),
                ]);
            } catch (\InvalidArgumentException) {
                // Leave as-is; validation rules will catch invalid formats
            }
        }
    }
}
