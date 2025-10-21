<?php

namespace App\Policies;

use App\Models\Income;
use App\Models\User;

/**
 * Policy governing access to Income models.
 */
class IncomePolicy
{
    /**
     * Determine whether the user can view any incomes.
     */
    public function viewAny(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can view the income.
     */
    public function view(User $user, Income $income): bool
    {
        return $user->getKey() === $income->user_id;
    }

    /**
     * Determine whether the user can create incomes.
     */
    public function create(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can update the income.
     */
    public function update(User $user, Income $income): bool
    {
        return $user->getKey() === $income->user_id;
    }

    /**
     * Determine whether the user can delete the income.
     */
    public function delete(User $user, Income $income): bool
    {
        return $user->getKey() === $income->user_id;
    }

    /**
     * Determine whether the user can restore the income.
     */
    public function restore(User $user, Income $income): bool
    {
        return $user->getKey() === $income->user_id;
    }

    /**
     * Determine whether the user can permanently delete the income.
     */
    public function forceDelete(User $user, Income $income): bool
    {
        return $user->getKey() === $income->user_id;
    }
}
