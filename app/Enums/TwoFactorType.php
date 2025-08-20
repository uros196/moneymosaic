<?php

namespace App\Enums;

use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\TwoFactorStrategyFactory;

/**
 * TwoFactorType enumerates supported 2FA methods and provides helpers
 * to resolve corresponding strategy instances via the factory.
 */
enum TwoFactorType: string
{
    case Email = 'email';
    case Totp = 'totp';

    /**
     * Resolve the concrete TwoFactorStrategy for this type using the factory.
     */
    public function strategy(): TwoFactorStrategy
    {
        return app(TwoFactorStrategyFactory::class)->forEnum($this);
    }
}
