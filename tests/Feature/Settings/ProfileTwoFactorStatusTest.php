<?php

namespace Tests\Feature\Settings;

use App\Enums\TwoFactorType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTwoFactorStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_in_progress_when_email_pending(): void
    {
        $user = User::factory()->create([
            'two_factor_type' => TwoFactorType::Email->value,
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_pending' => true])
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/profile')
                ->where('twoFactorSetupInProgress', true)
            );
    }

    public function test_shows_in_progress_when_totp_setup_begun(): void
    {
        $user = User::factory()->create([
            'two_factor_type' => TwoFactorType::Totp->value,
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user)
            ->withSession(['totp_setup_begun' => true])
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/profile')
                ->where('twoFactorSetupInProgress', true)
            );
    }

    public function test_shows_not_in_progress_when_disabled_and_no_flags(): void
    {
        $user = User::factory()->create([
            'two_factor_type' => null,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/profile')
                ->where('twoFactorSetupInProgress', false)
            );
    }
}
