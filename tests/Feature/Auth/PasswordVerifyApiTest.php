<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordVerifyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected(): void
    {
        $this->post(route('auth.password.verify'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_verify_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('auth.password.verify'), ['password' => 'password'])
            ->assertNoContent(); // 204
    }

    public function test_user_gets_validation_error_on_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('auth.password.verify'), ['password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
