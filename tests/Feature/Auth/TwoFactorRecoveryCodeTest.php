<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactor\RecoveryCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorRecoveryCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pass_two_factor_challenge_with_recovery_code(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => 'totp',
            'two_factor_secret' => 'SOMESECRET', // not actually used since we'll use recovery code
        ]);

        // Generate recovery codes for the user
        $service = new RecoveryCodeService;
        $codes = $service->generateAndStore($user, 3);

        // Simulate login up to challenge (middleware expects auth)
        $this->actingAs($user);

        $response = $this->post(route('twofactor.store'), [
            'code' => $codes[0],
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue((bool) session()->get('2fa_passed', false));

        // Code should be consumed; attempting reuse should fail
        $this->actingAs($user);
        $response2 = $this->from(route('twofactor.challenge'))
            ->post(route('twofactor.store'), ['code' => $codes[0]]);
        $response2->assertSessionHasErrors('code');
    }
}
