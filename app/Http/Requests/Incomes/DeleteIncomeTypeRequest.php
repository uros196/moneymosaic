<?php

namespace App\Http\Requests\Incomes;

use App\Models\IncomeType;
use Illuminate\Foundation\Http\FormRequest;

class DeleteIncomeTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // No inputs, but we enforce business rules via validator callback in withValidator
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var IncomeType $incomeType */
            $incomeType = $this->route('incomeType');

            // Prevent deleting 'system type'
            if ($incomeType->is_system_type) {
                $validator->errors()->add('incomeType', __('incomes.types.cannot_delete_system'));

                return;
            }

            // Prevent deleting a type linked to incomes
            if ($incomeType->incomes()->exists()) {
                $validator->errors()->add('incomeType', __('incomes.types.cannot_delete_linked'));
            }
        });
    }
}
