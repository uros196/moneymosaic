<?php

namespace App\Services\TwoFactor\Strategies;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\EmailCodeService;
use App\Services\TwoFactor\TwoFactorSessionService;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Support\Facades\Hash;

/**
 * Implements two-factor authentication using email verification codes.
 * Sends a verification code to the user's email and validates the submitted code.
 */
class EmailTwoFactorStrategy implements TwoFactorStrategy
{
    /**
     * Create a new EmailTwoFactorStrategy instance.
     *
     * @param  EmailCodeService  $emailService  Service for generating and sending email verification codes
     * @param  TwoFactorSessionService  $sessionService  Centralized session state manager for 2FA
     */
    public function __construct(
        protected EmailCodeService $emailService,
        protected TwoFactorSessionService $sessionService,
    ) {}

    /**
     * Initiates the two-factor authentication challenge by sending a verification code via email.
     *
     * @param  User  $user  The user attempting authentication
     * @param  SessionContract  $session  The current session to store verification data
     */
    public function beginChallenge(User $user, SessionContract $session): void
    {
        $this->emailService->send($user, $session);
    }

    /**
     * Verifies the submitted two-factor authentication code.
     */
    public function verify(User $user, string $code, SessionContract $session): bool
    {
        $numeric = preg_replace('/\D+/', '', (string) $code);
        if ($numeric === null || strlen($numeric) !== 6) {
            return false;
        }

        $hash = (string) $session->get('2fa_code_hash', '');
        $expiresAt = (int) $session->get('2fa_expires_at', 0);
        if (now()->timestamp > $expiresAt) {
            return false;
        }

        return $hash !== '' && Hash::check($numeric, $hash);
    }

    /**
     * Checks if email-based two-factor authentication setup is in progress.
     */
    public function isSetupInProgress(User $user, SessionContract $session): bool
    {
        if (! $user->isTwoFactorTypeOf(TwoFactorType::Email) || $user->two_factor_enabled) {
            return false;
        }

        return $this->sessionService->isPending($session);
    }

    /**
     * Determines if the two-factor setup modal should be shown.
     */
    public function isModalPending(User $user, SessionContract $session): bool
    {
        // For email 2FA, modal is pending when a challenge is pending
        return $this->isSetupInProgress($user, $session);
    }
}
