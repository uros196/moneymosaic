<?php

namespace App\Services;

use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Models\Income;

/**
 * Service for managing income-related operations.
 */
class IncomeService
{
    /**
     * Save or update an income record with its associated tags.
     */
    public function save(StoreIncomeRequest $request, Income $income): Income
    {
        // save the income
        $income->fill($request->safe()->except('tags'))->save();

        // update list of the tags
        $request->safe()->whenFilled('tags', fn ($tags) => $income->syncUserTags($tags));

        return $income;
    }
}
