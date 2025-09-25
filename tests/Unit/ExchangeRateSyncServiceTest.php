<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Facades\ExchangeRateProvider;
use App\Services\ExchangeRates\ExchangeRateSyncService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_for_range_fetches_and_persists_each_day(): void
    {
        $start = Carbon::parse('2025-08-10');
        $end = Carbon::parse('2025-08-12');

        // Use the configurable fake provider instead of mocking
        ExchangeRateProvider::fake([
            '2025-08-10' => [
                'EUR' => [
                    'USD' => 1.20,
                    'RSD' => 118.00,
                ],
            ],
            '2025-08-11' => [
                'EUR' => [
                    'USD' => 1.15,
                    'RSD' => 117.70,
                ],
            ],
            '2025-08-12' => [
                'EUR' => [
                    'USD' => 1.10,
                    'RSD' => 117.50,
                ],
            ],
        ]);

        $service = app(ExchangeRateSyncService::class);
        $service->syncForRange($start, $end);

        // Assert for 2025-08-10
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
            'rate_multiplier' => 1.20,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
            'rate_multiplier' => 118.00,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-10 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
            'rate_multiplier' => 1.0,
        ]);

        // Assert for 2025-08-11
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-11 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
            'rate_multiplier' => 1.15,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-11 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
            'rate_multiplier' => 117.70,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-11 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
            'rate_multiplier' => 1.0,
        ]);

        // Assert for 2025-08-12
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-12 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::USD->value,
            'rate_multiplier' => 1.10,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-12 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::RSD->value,
            'rate_multiplier' => 117.50,
        ]);
        $this->assertDatabaseHas('exchange_rates', [
            'date' => '2025-08-12 00:00:00',
            'base_currency_code' => Currency::EUR->value,
            'quote_currency_code' => Currency::EUR->value,
            'rate_multiplier' => 1.0,
        ]);
    }
}
