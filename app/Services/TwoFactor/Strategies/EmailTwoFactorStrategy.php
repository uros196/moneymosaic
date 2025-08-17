<?php

namespace App\Services\TwoFactor\Strategies;

use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\EmailCodeService;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Support\Facades\Hash;

/**
 * Implements two-factor authentication using email verification codes.
 * Sends a verification code to the user's email and validates the submitted code.
 */
class EmailTwoFactorStrategy implements TwoFactorStrategy
{
    /**
     * @param  EmailCodeService  $emailService  Service for generating and sending email verification codes
     */
    public function __construct(public EmailCodeService $emailService) {}

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
     *
     * @param  User  $user  The user attempting verification
     * @param  string  $code  The verification code submitted by the user
     * @param  SessionContract  $session  The current session containing stored verification data
     * @return bool True if verification succeeds, false otherwise
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
}
