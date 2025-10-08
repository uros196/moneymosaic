<?php

namespace App\QueryFilters\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter that applies date range constraints to income queries.
 * Filters results based on dateFrom and dateTo parameters.
 */
final class DateRangeFilter
{
    /**
     * Create a new date-range filter instance.
     */
    public function __construct(protected IncomeFiltersData $filters) {}

    /**
     * Apply date range filters to the query if dates are specified
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        if ($this->filters->dateFrom !== null) {
            $query->whereDate('occurred_on', '>=', $this->filters->dateFrom);
        }
        if ($this->filters->dateTo !== null) {
            $query->whereDate('occurred_on', '<=', $this->filters->dateTo);
        }

        return $next($query);
    }
}
