<?php

namespace App\Http\Requests\Incomes;

use App\Rules\CurrencyFilter;
use App\Rules\Pagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexIncomeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Filters
            'currency' => [new CurrencyFilter],

            // Pagination
            'page' => [new Pagination\Page],
            'perPage' => [new Pagination\PerPage('incomes')],
        ];
    }
}
