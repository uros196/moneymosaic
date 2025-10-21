<?php

namespace App\Repositories\Contracts;

use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * IncomeType repository contract.
 */
interface IncomeTypeRepository
{
    /**
     * Get income types visible for the given user (system + user-defined).
     *
     * @return Collection<int, IncomeType>
     */
    public function visibleForUser(User $user): Collection;

    /**
     * Get income types created by the given user.
     *
     * @return Collection<int, IncomeType>
     */
    public function createdByUser(User $user): Collection;
}
