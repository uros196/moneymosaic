<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $payload = [
            'name' => 'New Name',
            'email' => $user->email, // unchanged, unique rule ignores current user
            'locale' => 'sr',
            'default_currency_code' => 'USD',
            'password_confirm_minutes' => 60,
        ];

        $this->actingAs($user)
            ->patch(route('profile.update'), $payload)
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'locale' => 'sr',
            'default_currency_code' => 'USD',
        ]);
    }

    public function test_authenticated_user_can_delete_account_with_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_default_currency_code_must_be_valid(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'locale' => $user->locale,
            'default_currency_code' => 'XYZ',
            'password_confirm_minutes' => 60,
        ];

        $this->actingAs($user)
            ->patch(route('profile.update'), $payload)
            ->assertSessionHasErrors(['default_currency_code']);
    }
}
