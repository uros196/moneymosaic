<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use Illuminate\Contracts\Session\Session as SessionContract;

/**
 * Coordinates verifying 2FA codes using the user's configured strategy with a recovery-code fallback.
 *
 * Responsibilities:
 * - Try primary strategy first (email/totp).
 * - If primary fails, attempt recovery code verification and consume it.
 * - On success, mark the session as passed and clear temporary state via TwoFactorSessionService.
 */
class TwoFactorChallengeService
{
    /**
     * Create a new TwoFactorChallengeService instance.
     */
    public function __construct(
        public RecoveryCodeService $recoveryCodes,
        public TwoFactorSessionService $sessionService,
    ) {}

    /**
     * Attempt to verify the provided code using the user's configured strategy.
     * Falls back to recovery codes when primary verification fails.
     * On success, mark the session as passed and clear any email code state.
     */
    public function attempt(User $user, string $code, SessionContract $session): bool
    {
        if ($this->verifyWithStrategy($user->two_factor_auth, $user, $code, $session)) {
            $this->sessionService->finalizeSuccess($session);

            return true;
        }

        // Fallback to recovery codes
        if ($this->recoveryCodes->verifyAndConsume($user, $code)) {
            $this->sessionService->finalizeSuccess($session);

            return true;
        }

        return false;
    }

    /**
     * Verify the provided code using the given two-factor authentication strategy.
     */
    protected function verifyWithStrategy(TwoFactorStrategy $strategy, User $user, string $code, SessionContract $session): bool
    {
        return $strategy->verify($user, $code, $session);
    }
}
