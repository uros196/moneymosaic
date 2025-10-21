<?php

namespace App\Services\TwoFactor;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\Strategies\EmailTwoFactorStrategy;
use App\Services\TwoFactor\Strategies\TotpTwoFactorStrategy;

/**
 * Factory for creating two-factor authentication strategy instances.
 *
 * Refactored to resolve strategies via an internal map and the container,
 * keyed by TwoFactorType enum values. This avoids constructor wiring and
 * keeps resolution centralized and explicit.
 */
class TwoFactorStrategyFactory
{
    /** @var array<string, class-string<TwoFactorStrategy>> */
    private const array MAP = [
        'email' => EmailTwoFactorStrategy::class,
        'totp' => TotpTwoFactorStrategy::class,
    ];

    /**
     * Resolve the appropriate 2FA strategy for the given user based on their selected type.
     */
    public function forUser(User $user): ?TwoFactorStrategy
    {
        return $user->two_factor_type ? $this->forEnum($user->two_factor_type) : null;
    }

    /**
     * Resolve the 2FA strategy for a specific enum type.
     */
    public function forEnum(TwoFactorType $type): TwoFactorStrategy
    {
        $class = self::MAP[$type->value];

        /** @var TwoFactorStrategy $strategy */
        $strategy = app($class);

        return $strategy;
    }
}
