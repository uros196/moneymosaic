<?php

namespace App\Repositories\Eloquent;

use App\DTO\Incomes\IncomeFiltersData;
use App\DTO\Sort;
use App\Models\Income;
use App\Models\User;
use App\QueryFilters\ApplySorting;
use App\QueryFilters\CurrencyFilter;
use App\QueryFilters\Incomes\AmountRangeFilter;
use App\QueryFilters\Incomes\DateRangeFilter;
use App\QueryFilters\Incomes\IncomeTypeFilter;
use App\QueryFilters\Incomes\QueryTextFilter;
use App\QueryFilters\Incomes\TagsFilter;
use App\Repositories\Contracts\IncomeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Pipeline;

class EloquentIncomeRepository implements IncomeRepository
{
    /**
     * EloquentIncomeRepository constructor.
     */
    public function __construct(protected Income $model) {}

    /**
     * Paginate incomes for the given user with applied filters.
     */
    public function paginateForUser(User $user, IncomeFiltersData $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with('incomeType:id,name,user_id')
            ->with('tags:id,name')
            ->where('user_id', $user->id);

        // Run the pipeline to apply filters
        return Pipeline::send($query)
            ->through([
                new IncomeTypeFilter($filters),
                new CurrencyFilter($filters->currency),
                new TagsFilter($filters, $user),
                new AmountRangeFilter($filters),
                new DateRangeFilter($filters),
                new QueryTextFilter($filters),
                new ApplySorting($filters->sort)
                    // If no sorting is applied, default to descending order by created_at
                    ->default(Sort::fromString('created_at:desc')),
            ])
            ->thenReturn()
            // Chain builder methods
            ->paginate(perPage: $perPage)
            ->withQueryString();
    }
}
