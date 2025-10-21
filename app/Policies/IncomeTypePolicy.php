<?php

namespace App\Policies;

use App\Models\IncomeType;
use App\Models\User;

/**
 * Policy governing access to IncomeType models.
 */
class IncomeTypePolicy
{
    /**
     * Determine whether the user can view any income types.
     */
    public function viewAny(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can view the income type.
     * System types (user_id null) are visible to any authenticated user.
     * User-defined types are visible only to their owner.
     */
    public function view(User $user, IncomeType $type): bool
    {
        return $type->is_system_type || $user->getKey() === $type->user_id;
    }

    /**
     * Determine whether the user can create income types.
     */
    public function create(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can update the income type.
     * System types cannot be modified by users.
     */
    public function update(User $user, IncomeType $type): bool
    {
        return ! $type->is_system_type && $user->getKey() === $type->user_id;
    }

    /**
     * Determine whether the user can delete the income type.
     * System types cannot be deleted by users.
     */
    public function delete(User $user, IncomeType $type): bool
    {
        return ! $type->is_system_type && $user->getKey() === $type->user_id;
    }

    /**
     * Determine whether the user can restore the income type.
     */
    public function restore(User $user, IncomeType $type): bool
    {
        return ! $type->is_system_type && $user->getKey() === $type->user_id;
    }

    /**
     * Determine whether the user can permanently delete the income type.
     */
    public function forceDelete(User $user, IncomeType $type): bool
    {
        return ! $type->is_system_type && $user->getKey() === $type->user_id;
    }
}
