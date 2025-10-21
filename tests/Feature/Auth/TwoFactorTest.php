<?php

namespace Tests\Feature\Auth;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_email_2fa_redirects_to_challenge_and_sends_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('twofactor.challenge', absolute: false));
        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }

    public function test_email_2fa_challenge_accepts_correct_code(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        // Prepare session with a known code
        $this->actingAs($user);

        $response = $this->withSession([
            '2fa_code_hash' => Hash::make('123456'),
            '2fa_expires_at' => now()->addMinutes(10)->timestamp,
        ])->post(route('twofactor.store'), [
            'code' => '123456',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue(session()->get('2fa_passed'));
    }

    public function test_dashboard_is_gated_until_2fa_is_passed(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $this->actingAs($user);

        $this->get('/dashboard')->assertRedirect(route('twofactor.challenge', absolute: false));
    }

    public function test_challenge_redirects_if_already_passed(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $this->actingAs($user);

        $response = $this->withSession(['2fa_passed' => true])
            ->get(route('twofactor.challenge'));

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
