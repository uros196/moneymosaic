<?php

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeTypeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:60',
                // Unique within system types (user_id null) and the current user's types
                Rule::unique('income_types', 'name')->where(function ($q) {
                    $q->whereNull('user_id')->orWhere('user_id', $this->user()->getKey());
                }),
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [];
    }
}
