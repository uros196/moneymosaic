<?php

namespace App\Http\Requests\Incomes;

use App\Enums\Currency;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'occurred_on' => ['required', 'date'],
            'income_type_id' => [
                'required',
                'integer',
                Rule::exists('income_types', 'id')->where(function ($q) {
                    $q->whereNull('user_id')->orWhere('user_id', $this->user()->getKey());
                }),
            ],
            'amount_minor' => ['required', 'integer:', 'min:1'],
            'currency_code' => ['required', Rule::in(Currency::values())],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
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
        $currency = Currency::tryFrom($this->input('currency_code'));

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
