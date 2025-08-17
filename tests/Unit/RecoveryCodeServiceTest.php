<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TwoFactor\RecoveryCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecoveryCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_store_verify_and_consume_recovery_codes(): void
    {
        $user = User::factory()->create();

        $service = new RecoveryCodeService(defaultCount: 10);
        $codes = $service->generateAndStore($user, 5);

        $this->assertCount(5, $codes);
        $this->assertNotEmpty($codes[0]);

        $stored = (array) $user->fresh()->two_factor_recovery_codes;
        $this->assertCount(5, $stored);
        // Ensure stored values are hashes, not equal to plaintext
        $this->assertNotEquals($codes[0], $stored[0]);

        // First use should pass and consume
        $this->assertTrue($service->verifyAndConsume($user->fresh(), $codes[0]));
        $this->assertCount(4, (array) $user->fresh()->two_factor_recovery_codes);

        // Second use should fail as consumed
        $this->assertFalse($service->verifyAndConsume($user->fresh(), $codes[0]));
    }
}
