<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Facades\ExchangeRateProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExchangeRatesSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_and_persists_rates_for_given_date(): void
    {
        // Configure a fake provider with date-scoped multipliers
        ExchangeRateProvider::fake([
            '2025-08-10' => [
                'EUR' => [
                    'USD' => 1.10,
                    'RSD' => 117.50,
                ],
            ],
        ]);

        // Run command for a specific date
        Artisan::call('rates:sync', ['--date' => '2025-08-10']);

        // Assert database has the expected rows
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
            'rate_multiplier' => 1.1,
        ]);

        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
            'rate_multiplier' => 117.5,
        ]);

        // base->base enforced
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
            'rate_multiplier' => 1.0,
        ]);
    }

    public function test_command_defaults_to_today_when_date_not_provided(): void
    {
        // Freeze "today" for deterministic behavior
        Carbon::setTestNow('2025-08-01');

        // Configure a fake provider with date-scoped multipliers for "today"
        ExchangeRateProvider::fake([
            '2025-08-01' => [
                'EUR' => [
                    'USD' => 1.10,
                    'RSD' => 117.50,
                ],
            ],
        ]);

        // Run command without passing --date (should default to today)
        Artisan::call('rates:sync');

        // Assert database has the expected rows for 2025-08-01
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-01 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
            'rate_multiplier' => 1.1,
        ]);

        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-01 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
            'rate_multiplier' => 117.5,
        ]);

        // base->base enforced
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-01 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
            'rate_multiplier' => 1.0,
        ]);

        // Reset test now
        Carbon::setTestNow();
    }
}
