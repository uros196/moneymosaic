<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use Illuminate\Contracts\Session\Session as SessionContract;

class TwoFactorChallengeService
{
    /**
     * TwoFactorChallengeService constructor.
     */
    public function __construct(
        public TwoFactorStrategyFactory $factory,
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
        $strategy = $this->factory->forUser($user);
        if ($this->verifyWithStrategy($strategy, $user, $code, $session)) {
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
     *
     * @param  TwoFactorStrategy  $strategy  The strategy to use for verification
     * @param  User  $user  The user attempting verification
     * @param  string  $code  The verification code to validate
     * @param  SessionContract  $session  The current session
     * @return bool True if verification succeeds, false otherwise
     */
    protected function verifyWithStrategy(TwoFactorStrategy $strategy, User $user, string $code, SessionContract $session): bool
    {
        return $strategy->verify($user, $code, $session);
    }

}
