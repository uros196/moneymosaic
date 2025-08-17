<?php

namespace App\Services\TwoFactor\Contracts;

use App\Models\User;
use Illuminate\Contracts\Session\Session as SessionContract;

interface TwoFactorStrategy
{
    /**
     * Initialize or (re)send the challenge for the user (e.g., send email code).
     */
    public function beginChallenge(User $user, SessionContract $session): void;

    /**
     * Verify a provided code for the user.
     */
    public function verify(User $user, string $code, SessionContract $session): bool;
}
