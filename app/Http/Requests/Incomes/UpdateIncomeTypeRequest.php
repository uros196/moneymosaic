<?php

namespace App\Http\Requests\Incomes;

use App\Models\IncomeType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var IncomeType $incomeType */
        $incomeType = $this->route('incomeType');

        return [
            'name' => [
                'required',
                'string',
                'max:60',
                function (string $attribute, mixed $value, \Closure $fail) use ($incomeType): void {
                    $user = $this->user();

                    $exists = IncomeType::query()
                        ->where(function ($q) use ($user): void {
                            $q->whereNull('user_id')->orWhere('user_id', $user->getKey());
                        })
                        ->where('id', '!=', $incomeType->getKey())
                        ->where(function ($q) use ($value): void {
                            foreach (config('app.available_locales') as $locale) {
                                $q->orWhere("name->$locale", $value);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('validation.unique', ['attribute' => __(ucfirst($attribute))]));
                    }
                },
            ],
        ];
    }
}
