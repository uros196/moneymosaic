<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Centralized password confirmation utilities.
 *
 * Consolidates repeated logic around per-user confirmation windows,
 * session timestamps, and password validation.
 */
class PasswordConfirmationService
{
    /**
     * Determine if the current request requires password confirmation.
     */
    public function needsConfirmation(Request $request): bool
    {
        $user = $request->user();
        if (! $user instanceof AuthenticatableContract) {
            return false;
        }

        $minutes = $this->getWindowMinutesForUser($user);
        if ($minutes <= 0) {
            return false;
        }

        $last = (int) $request->session()->get('auth.password_confirmed_at', 0);
        if ($last === 0) {
            return true;
        }

        return (time() - $last) >= ($minutes * 60);
    }

    /**
     * Mark the password as confirmed now (refreshes the confirmation window).
     */
    public function confirmNow(): void
    {
        Session::passwordConfirmed();
    }

    /**
     * Get the remaining seconds until confirmation expires.
     * Returns 0 if no confirmation is present or window is disabled/expired.
     */
    public function secondsUntilExpiry(Request $request): int
    {
        $user = $request->user();
        if (! $user instanceof AuthenticatableContract) {
            return 0;
        }

        $minutes = $this->getWindowMinutesForUser($user);
        if ($minutes <= 0) {
            return 0;
        }

        $last = (int) $request->session()->get('auth.password_confirmed_at', 0);
        if ($last === 0) {
            return 0;
        }

        $threshold = $minutes * 60;
        $elapsed = time() - $last;

        return $elapsed >= $threshold ? 0 : ($threshold - $elapsed);
    }

    /**
     * Validate the given password for the provided user.
     */
    public function validateForUser(User $user, string $password): bool
    {
        // Laravel's Auth::validate will not log the user in, only checks credentials.
        return Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $password,
        ]);
    }

    /**
     * Get per-user confirmation window in minutes.
     */
    public function getWindowMinutesForUser(User $user): int
    {
        /** @var mixed $value */
        $value = $user->password_confirm_minutes ?? 0;

        return (int) ($value ?: 0);
    }
}
