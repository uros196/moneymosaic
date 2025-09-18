<?php

namespace App\Models;

use App\Enums\Currency;
use App\Models\Concerns\HasTwoFactor;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Application user model.
 *
 * Includes profile preferences (locale, password confirmation timeout) and
 * Two-Factor Authentication (2FA) attributes with appropriate casting and
 * encryption for sensitive fields.
 */
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasTwoFactor, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'locale',
        'password_confirm_minutes',
        'password',
        'default_currency_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge([
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_confirm_minutes' => 'integer',
            'default_currency_code' => Currency::class,
        ], $this->twoFactorCasts());
    }
}
