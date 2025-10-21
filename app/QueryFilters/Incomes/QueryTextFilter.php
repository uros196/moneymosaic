<?php

namespace App\QueryFilters\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies a free-text search filter to incomes.
 * Matches against encrypted columns via LIKE on decrypted values (handled by DB).
 */
final class QueryTextFilter
{
    /**
     * Initialize the filter with income filters data.
     */
    public function __construct(protected IncomeFiltersData $filters) {}

    /**
     * Apply a search filter to the query if specified in the filters.
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        $term = $this->filters->query;

        if (! is_null($term)) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        return $next($query);
    }
}
