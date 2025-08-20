<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_password_confirm_when_inactivity_exceeds_setting(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (6 * 60),
            ])
            ->get('/settings/profile')
            ->assertRedirect(route('password.confirm', absolute: false));
    }

    public function test_allows_access_within_window(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - 60,
            ])
            ->get('/settings/profile')
            ->assertOk();
    }

    public function test_allows_access_when_disabled(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => null,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (24 * 60 * 60),
            ])
            ->get('/settings/profile')
            ->assertOk();
    }

    public function test_needs_confirmation_endpoint(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        // Expired
        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (10 * 60),
            ])
            ->get('/password/needs-confirmation')
            ->assertRedirect(route('password.confirm', absolute: false));

        // Within window
        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - 60,
            ])
            ->get('/password/needs-confirmation')
            ->assertNoContent();

        // Disabled setting
        $user->update(['password_confirm_minutes' => null]);
        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (10 * 60),
            ])
            ->get('/password/needs-confirmation')
            ->assertNoContent();
    }
}
