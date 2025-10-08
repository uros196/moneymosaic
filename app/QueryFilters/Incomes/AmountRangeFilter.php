<?php

namespace App\QueryFilters\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter for applying minimum and maximum amount constraints to income queries.
 * Filters income records based on the amount_minor field using provided range boundaries.
 */
final class AmountRangeFilter
{
    /**
     * Create a new amount-range filter instance.
     */
    public function __construct(protected IncomeFiltersData $filters) {}

    /**
     * Apply 'amount range' filters to the query.
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        if ($this->filters->amountMinorMin !== null) {
            $query->where('amount_minor', '>=', $this->filters->amountMinorMin);
        }
        if ($this->filters->amountMinorMax !== null) {
            $query->where('amount_minor', '<=', $this->filters->amountMinorMax);
        }

        return $next($query);
    }
}
