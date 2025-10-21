<?php

namespace Tests\Feature\Settings;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Services\TwoFactor\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotpConfirmTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_totp_generates_recovery_codes_and_marks_session_passed(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
        ]);

        $totp = new TotpService;
        $secret = $totp->generateSecret();
        $code = $totp->currentCode($secret);

        $user->forceFill([
            'two_factor_type' => TwofactorType::Totp,
            'two_factor_secret' => $secret,
        ])->save();

        $response = $this->actingAs($user)
            ->post(route('settings.security.totp.confirm'), [
                'code' => $code,
            ]);

        $response->assertRedirect(route('settings.security', absolute: false));
        $response->assertSessionHas('recoveryCodes');

        $this->assertTrue((bool) session()->get('2fa_passed', false));

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertNotEmpty($user->two_factor_recovery_codes);
    }
}
