<?php

namespace Tests\Feature;

use App\Enums\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRatesSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_and_persists_rates_for_given_date(): void
    {
        // Fake provider API response
        Http::fake([
            // Match any call to the configured provider base URL
            'api.exchangerate.host/*' => Http::response([
                'rates' => [
                    'USD' => 1.10,
                    'RSD' => 117.50,
                ],
            ], 200),
        ]);

        // Run command for a specific date
        Artisan::call('rates:sync', ['--date' => '2025-08-10']);

        // Assert database has the expected rows
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
        ]);

        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
        ]);

        // base->base enforced
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
        ]);
    }
}
