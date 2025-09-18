<?php

namespace Tests\Feature\Settings;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailTwoFactorEnableTest extends TestCase
{
    use RefreshDatabase;

    public function test_enable_email_2fa_sends_code_and_keeps_disabled(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_type' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.security.email.enable'));

        $response->assertRedirect();

        $user->refresh();
        $this->assertInstanceOf(TwoFactorType::class, $user->two_factor_type);
        $this->assertSame(TwoFactorType::Email, $user->two_factor_type);
        $this->assertFalse($user->two_factor_enabled);

        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }

    public function test_confirm_email_2fa_with_correct_code_enables(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $this->actingAs($user);

        // Prepare session with a known code
        $response = $this->withSession([
            '2fa_code_hash' => Hash::make('123456'),
            '2fa_expires_at' => now()->addMinutes(10)->timestamp,
        ])->post(route('settings.security.email.confirm'), [
            'code' => '123456',
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertTrue((bool) session()->get('2fa_passed', false));
    }

    public function test_confirm_email_2fa_with_invalid_code_fails(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $this->actingAs($user);

        $response = $this->withSession([
            '2fa_code_hash' => Hash::make('123456'),
            '2fa_expires_at' => now()->addMinutes(10)->timestamp,
        ])->post(route('settings.security.email.confirm'), [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    public function test_resend_email_code_sends_notification_again(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_type' => TwoFactorType::Email,
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.security.email.resend'));

        $response->assertRedirect();
        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }
}
