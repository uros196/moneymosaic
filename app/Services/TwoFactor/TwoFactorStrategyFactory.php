<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\Strategies\EmailTwoFactorStrategy;
use App\Services\TwoFactor\Strategies\TotpTwoFactorStrategy;

/**
 * Factory for creating two-factor authentication strategy instances based on user preferences.
 */
class TwoFactorStrategyFactory
{
    /**
     * Create a new factory instance with available 2FA strategies.
     */
    public function __construct(
        public EmailTwoFactorStrategy $emailStrategy,
        public TotpTwoFactorStrategy $totpStrategy,
    ) {}

    /**
     * Get the appropriate 2FA strategy for the given user.
     */
    public function forUser(User $user): TwoFactorStrategy
    {
        return $this->forType($user->two_factor_type);
    }

    /**
     * Get the 2FA strategy for the specified type.
     *
     * @param  'email'|'totp'|null  $type  The type of 2FA strategy to return
     * @return TwoFactorStrategy Returns email strategy as fallback when type is null
     */
    public function forType(?string $type): TwoFactorStrategy
    {
        return match ($type) {
            'totp' => $this->totpStrategy,
            default => $this->emailStrategy,
        };
    }
}
