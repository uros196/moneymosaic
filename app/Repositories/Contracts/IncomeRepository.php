<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for income data access.
 */
interface IncomeRepository
{
    /**
     * Paginate incomes for the given user with default ordering.
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;
}
