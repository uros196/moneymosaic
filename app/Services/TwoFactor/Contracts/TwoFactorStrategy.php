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

    /**
     * Determine if the setup/enable flow for this strategy is currently in progress for the user.
     */
    public function isSetupInProgress(User $user, SessionContract $session): bool;

    /**
     * Determine if the UI modal should be considered pending/open for this strategy.
     * For example: email 2FA pending challenge; TOTP setup just began in this session.
     */
    public function isModalPending(User $user, SessionContract $session): bool;
}
