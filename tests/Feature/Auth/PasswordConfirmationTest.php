<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered_when_required()
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (10 * 60),
            ])
            ->get(route('password.confirm'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/confirm-password')
        );
    }

    public function test_password_can_be_confirmed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('password.confirm'), [
                'password' => 'password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('password.confirm'), [
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_needs_confirmation_guest_is_redirected_to_login(): void
    {
        $this->get(route('password.needs-confirmation'))
            ->assertRedirect(route('login'));
    }

    public function test_needs_confirmation_redirects_to_confirm_page_when_required(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - (10 * 60),
            ])
            ->get(route('password.needs-confirmation'))
            ->assertRedirect(route('password.confirm', absolute: false));
    }

    public function test_needs_confirmation_returns_no_content_when_within_window(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time() - 60,
            ])
            ->get(route('password.needs-confirmation'))
            ->assertNoContent();
    }

    public function test_needs_confirmation_returns_no_content_when_feature_disabled(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('password.needs-confirmation'))
            ->assertNoContent();
    }

    public function test_confirm_password_screen_redirects_when_feature_disabled(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('password.confirm'))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_confirm_password_screen_redirects_when_within_window(): void
    {
        $user = User::factory()->create([
            'password_confirm_minutes' => 5,
        ]);

        $this->actingAs($user)
            ->withSession([
                'auth.password_confirmed_at' => time(),
            ])
            ->get(route('password.confirm'))
            ->assertRedirect(route('dashboard', absolute: false));
    }
}
