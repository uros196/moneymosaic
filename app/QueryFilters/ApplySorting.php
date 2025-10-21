<?php

namespace App\QueryFilters;

use App\DTO\Sort;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter class responsible for applying sorting to income queries based on provided filters.
 */
class ApplySorting
{
    /**
     * Default sorting configuration if none is provided.
     */
    protected ?Sort $default = null;

    /**
     * Create a new ApplySorting instance.
     */
    public function __construct(protected ?Sort $sort) {}

    /**
     * Set the default sort configuration if none is provided.
     */
    public function default(Sort $sort): self
    {
        $this->default = $sort;

        return $this;
    }

    /**
     * Apply sorting to the query based on the filters.
     */
    public function __invoke(Builder $query, Closure $next): Builder
    {
        $sorting = $this->sort ?? $this->default;

        if ($sorting) {
            $this->orderBy($query, $sorting);
        }

        return $next($query);
    }

    /**
     * Apply the sorting to the query.
     */
    protected function orderBy(Builder $query, Sort $sort): void
    {
        $query->orderBy($sort->field, $sort->direction);
    }
}
