<?php

namespace App\Http\Requests\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use App\Enums\Currency;
use App\Rules\CurrencyFilterRule;
use App\Rules\IncomeTypeRule;
use App\Rules\Pagination;
use App\Rules\SortingRule;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexIncomeRequest extends FormRequest
{
    /**
     * List of sortable columns.
     */
    protected array $sortable = ['occurred_on', 'income_type', 'currency_code'];

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Currency conversion
            'currency' => [new CurrencyFilterRule],

            // Filters
            'currency_code' => [new CurrencyFilterRule],
            'income_type' => ['sometimes', new IncomeTypeRule($this->user())],
            'query' => ['sometimes', 'string', 'max:200'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'amount_minor_min' => ['sometimes', 'integer', 'min:0'],
            'amount_minor_max' => ['sometimes', 'integer', Rule::when($this->filled('amount_minor_min'), 'gt:amount_minor_min')],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'min:1', 'max:50'],

            // Sorting
            'sort' => [new SortingRule($this->sortable)],

            // Pagination
            'page' => [new Pagination\PageRule],
            'perPage' => [new Pagination\PerPageRule('incomes')],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->convertToMinor('amount_min', 'amount_minor_min');
        $this->convertToMinor('amount_max', 'amount_minor_max');

        // Normalize tags: allow comma-separated input to behave like an array
        if ($this->has('tags') && is_string($this->input('tags'))) {
            $parts = array_filter(array_map('trim', explode(',', (string) $this->input('tags'))));
            $this->merge(['tags' => array_values(array_unique($parts))]);
        }
    }

    /**
     * Converts a numeric value from major units to minor units based on the currency.
     *
     * This method checks for the presence of a specified key in the request data and
     * converts its value from major units (e.g., dollars) to minor units (e.g., cents)
     * using the appropriate currency conversion rate. The currency is determined by checking
     * 'currency_code', 'currency' parameters, or falling back to user's default currency.
     */
    protected function convertToMinor(string $key, string $new_key): void
    {
        if ($this->has($key)) {
            // We need the currency so we can convert the amount to minor in the best way possible
            $currency = $this->enum(
                // First, try the 'currency_code' query parameter
                'currency_code', Currency::class,
                $this->enum(
                    // If the 'currency_code' is not available (or not valid),
                    // then try to get it from the 'currency' query parameter
                    'currency', Currency::class,
                    // If the 'currency' 'failed', then use the default currency from the user settings
                    $this->user()->default_currency_code
                )
            );

            // Finally, convert the amount to minor units
            $this->merge([
                $new_key => Money::toMinor($this->string($key), $currency),
            ]);
        }
    }

    /**
     * Get the filtered income data from the request.
     */
    public function filters(): IncomeFiltersData
    {
        return IncomeFiltersData::fromRequest($this);
    }

    /**
     * Retrieve the sortable fields.
     */
    public function sortables(): array
    {
        return $this->sortable;
    }
}
