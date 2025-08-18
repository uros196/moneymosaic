<?php

namespace App\Services\TwoFactor;

use Illuminate\Contracts\Session\Session as SessionContract;

/**
 * Centralized helper for managing Two-Factor Authentication (2FA) session state.
 *
 * Normalizes how we mark a 2FA challenge as pending/successful and how we clear
 * temporary email-code state. This avoids duplication across controllers,
 * middleware and services.
 */
class TwoFactorSessionService
{
    /**
     * Mark that a 2FA challenge is pending for the current session.
     * Also clears any previous success flag to ensure the flow is enforced.
     */
    public function beginChallenge(SessionContract $session): void
    {
        $session->forget(['2fa_passed']);
        $session->put('2fa_pending', true);
    }

    /**
     * Mark that 2FA was successfully completed for the current session.
     * Clears any temporary email-code state and the pending flag.
     */
    public function finalizeSuccess(SessionContract $session): void
    {
        $session->put('2fa_passed', true);
        $this->clearEmailState($session);
        $session->forget(['2fa_pending']);
    }

    /**
     * Whether the current session has successfully passed 2FA.
     */
    public function hasPassed(SessionContract $session): bool
    {
        return (bool) $session->get('2fa_passed', false);
    }

    /**
     * Whether a 2FA challenge is currently pending.
     */
    public function isPending(SessionContract $session): bool
    {
        return (bool) $session->get('2fa_pending', false);
    }

    /**
     * Clear temporary email-code verification state from the session.
     */
    public function clearEmailState(SessionContract $session): void
    {
        $session->forget(['2fa_code_hash', '2fa_expires_at']);
    }

    /**
     * Clear all 2FA related flags from the session.
     */
    public function clearAll(SessionContract $session): void
    {
        $session->forget(['2fa_passed', '2fa_code_hash', '2fa_expires_at', '2fa_pending', 'totp_setup_begun']);
    }

    /**
     * Mark that TOTP setup has begun so the UI can auto-open the setup modal.
     */
    public function markTotpSetupBegan(SessionContract $session): void
    {
        $session->put('totp_setup_begun', true);
    }

    /**
     * Whether TOTP setup has been started in this session.
     */
    public function isTotpSetupBegan(SessionContract $session): bool
    {
        return (bool) $session->get('totp_setup_begun', false);
    }

    /**
     * Clear any in-progress TOTP setup flag.
     */
    public function clearTotpSetup(SessionContract $session): void
    {
        $session->forget('totp_setup_begun');
    }
}
