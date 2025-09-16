<?php

namespace App\Services;

use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepository;
use App\Support\TableConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Service for managing income-related operations including pagination and CRUD operations.
 */
class IncomeService
{
    /**
     * IncomeService constructor.
     */
    public function __construct(protected Request $request) {}

    /**
     * Paginate incomes for a specific user.
     */
    public function paginate(User $user): LengthAwarePaginator
    {
        $repository = app(IncomeRepository::class);

        // Resolve per-page configuration
        $perPage = TableConfig::resolvePerPage($this->request, 'incomes');

        return $repository->paginateForUser($user, $perPage);
    }

    /**
     * Save or update an income record with its associated tags.
     */
    public function save(StoreIncomeRequest $request, Income $income): Income
    {
        // save the income
        $income->fill($request->safe()->except('tags'))->save();

        // update list of the tags
        $request->safe()->whenFilled('tags', fn($tags) => $income->syncUserTags($tags));

        return $income;
    }
}
