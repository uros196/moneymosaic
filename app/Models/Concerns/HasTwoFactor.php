<?php

namespace App\Models\Concerns;

use App\Enums\TwoFactorType;
use App\Services\TwoFactor\Contracts\TwoFactorStrategy;
use App\Services\TwoFactor\TwoFactorStrategyFactory;
use App\Services\TwoFactor\UserTwoFactorService;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Two-Factor Authentication helpers for Eloquent models.
 *
 * Intended to be used by the User model to encapsulate 2FA-specific
 * accessors, helpers, casts, and attribute configuration.
 */
trait HasTwoFactor
{
    /**
     * Accessor to get the Two-Factor strategy instance for the configured type.
     */
    public function twoFactorAuth(): Attribute
    {
        return Attribute::get(function (): ?TwoFactorStrategy {
            return app(TwoFactorStrategyFactory::class)->forUser($this);
        });
    }

    /**
     * Checks if a user's configured two-factor authentication matches the given type.
     */
    public function isTwoFactorTypeOf(TwoFactorType $type): bool
    {
        return app(UserTwoFactorService::class)->isTwoFactorTypeOf($this, $type);
    }

    /**
     * Casts for Two-Factor related attributes.
     *
     * @return array<string, string>
     */
    protected function twoFactorCasts(): array
    {
        return [
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Eloquent will call this initializer when the trait is used by a model.
     * Ensures 2FA attributes are fillable and properly hidden.
     */
    protected function initializeHasTwoFactor(): void
    {
        // Ensure 2FA attributes are mass assignable
        $this->mergeFillable([
            'two_factor_enabled',
            'two_factor_type',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]);

        // Ensure sensitive fields are hidden
        $this->makeHidden([
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]);
    }
}
