<?php

namespace App\QueryFilters\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter query builder for incomes based on an income type.
 * This filter is part of the income query pipeline.
 */
final class IncomeTypeFilter
{
    /**
     * Initialize the filter with income filters data.
     */
    public function __construct(protected IncomeFiltersData $filters) {}

    /**
     * Apply income type filter to the query if specified in the filters.
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        if ($this->filters->incomeTypeId !== null) {
            $query->where('income_type_id', $this->filters->incomeTypeId);
        }

        return $next($query);
    }
}
