<?php

namespace App\Repositories\Contracts;

use App\DTO\Incomes\IncomeFiltersData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for income data access.
 */
interface IncomeRepository
{
    /**
     * Paginate incomes for the given user with applied filters.
     */
    public function paginateForUser(User $user, IncomeFiltersData $filters, int $perPage): LengthAwarePaginator;
}
