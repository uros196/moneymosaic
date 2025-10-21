<?php

namespace App\QueryFilters\Incomes;

use App\DTO\Incomes\IncomeFiltersData;
use App\Models\Income;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter class for applying tag-based filtering to income queries.
 * Filters incomes based on specified tags while ensuring user-specific tag types.
 */
final class TagsFilter
{
    /**
     * Initialize the 'tags filter' with filter data and user context
     */
    public function __construct(protected IncomeFiltersData $filters, public User $user) {}

    /**
     * Apply the 'tags filter' to the query pipeline
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        $tags = $this->filters->tags;

        if (! empty($this->filters->tags)) {
            // Scope to the current user's tag type to avoid cross-user leakage
            $type = Income::tagTypeForUser($this->user);
            // Using Spatie\Tags provided scope
            $query->withAnyTags($tags, $type);
        }

        return $next($query);
    }
}
