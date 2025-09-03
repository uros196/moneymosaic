<?php

namespace App\Repositories\Contracts;

use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Support\Collection;

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
}
