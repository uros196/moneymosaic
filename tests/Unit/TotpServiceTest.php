<?php

namespace Tests\Unit;

use App\Services\TwoFactor\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_current_code_verifies(): void
    {
        $service = new TotpService;
        $secret = $service->generateSecret();
        $timestamp = 1_696_000_000; // fixed timestamp
        $code = $service->currentCode($secret, $timestamp);

        $this->assertTrue($service->verify($secret, $code, $timestamp));
        $this->assertFalse($service->verify($secret, '000000', $timestamp - 3600));
    }
}
