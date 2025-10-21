<?php

namespace App\QueryFilters;

use App\Enums\Currency;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter for querying data based on currency code.
 * This class is part of the pipeline pattern for filtering records.
 */
final class CurrencyFilter
{
    /**
     * Create a new currency filter instance.
     */
    public function __construct(protected ?Currency $currency, protected string $field = 'currency_code') {}

    /**
     * Apply the currency filter to the query.
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        if ($this->currency !== null) {
            $query->where($this->field, $this->currency);
        }

        return $next($query);
    }
}
