<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy governing access to User models (profile-related actions).
 */
class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $subject): bool
    {
        return $user->getKey() === $subject->getKey();
    }

    /**
     * Determine whether the user can create users.
     * User creation (registration) is handled for guests; keep this false here.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $subject): bool
    {
        return $user->getKey() === $subject->getKey();
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $subject): bool
    {
        return $user->getKey() === $subject->getKey();
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $subject): bool
    {
        return $user->getKey() === $subject->getKey();
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $subject): bool
    {
        return $user->getKey() === $subject->getKey();
    }
}
