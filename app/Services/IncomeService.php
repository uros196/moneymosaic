<?php

namespace App\Services;

use App\DTO\Incomes\IncomeData;
use App\DTO\Incomes\IncomeFiltersData;
use App\Enums\Currency;
use App\Http\Requests\Incomes\IndexIncomeRequest;
use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepository;
use App\Support\Money;
use App\Support\TableConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Service for managing income-related operations including pagination and CRUD operations.
 */
class IncomeService
{
    /**
     * Paginate incomes for a specific user.
     */
    public function paginate(Request $request, User $user): LengthAwarePaginator
    {
        $repository = app(IncomeRepository::class);

        // Resolve per-page configuration
        $perPage = TableConfig::resolvePerPage($request, 'incomes');

        // Build filters from the current request when available
        $filters = $request instanceof IndexIncomeRequest
            ? $request->filters()
            : new IncomeFiltersData;

        return $repository->paginateForUser($user, $filters, $perPage);
    }

    /**
     * Save or update an income record with its associated tags.
     */
    public function save(IncomeData $data, Income $income): Income
    {
        // save the income
        $income->fill($data->toModelAttributes())->save();

        // update the list of the tags (preserve previous behavior: only when provided and not empty)
        if ($data->tags !== null) {
            $income->syncUserTags($data->tags);
        }

        return $income;
    }

    /**
     * Convert income amount to specified currency and format it as string.
     */
    public function convertIncomeToCurrency(Income $income, Currency $currency): string
    {
        $converted_amount = app(CurrencyConversionService::class)
            ->convertMinor(
                minor: $income->amount_minor,
                fromCurrency: $income->currency_code->value,
                toCurrency: $currency,
                date: $income->occurred_on
            );

        return Money::formatMajor($converted_amount, $currency);
    }
}
