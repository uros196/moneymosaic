<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Support\Facades\Hash;

/**
 * Service for handling email-based two-factor authentication codes.
 * Generates, stores, and sends verification codes to users via email.
 */
class EmailCodeService
{
    /**
     * Initialize the email code service.
     *
     * @param int $ttlMinutes Time-to-live in minutes for the verification code (default: 10)
     */
    public function __construct(public int $ttlMinutes = 10) {}

    /**
     * Generate and send a two-factor authentication code to the user.
     * Stores the hashed code and expiration timestamp in the session.
     *
     * @param User $user The user to send the code to
     * @param SessionContract $session The current session for storing verification data
     */
    public function send(User $user, SessionContract $session): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $session->put('2fa_code_hash', Hash::make($code));
        $session->put('2fa_expires_at', now()->addMinutes($this->ttlMinutes)->timestamp);

        // Send via Notification while keeping the Mailable class in the codebase
        $user->notify(new TwoFactorCodeNotification($code));
    }
}
