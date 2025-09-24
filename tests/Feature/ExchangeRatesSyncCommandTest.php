<?php

namespace Tests\Feature;

use App\DTO\ExchangeRates\DailyRates;
use App\DTO\ExchangeRates\RateQuote;
use App\Enums\Currency;
use App\Services\ExchangeRates\RateProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExchangeRatesSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_and_persists_rates_for_given_date(): void
    {
        // Mock the RateProvider strategy instead of faking HTTP
        $this->mock(RateProvider::class, function ($mock) {
            $mock->shouldReceive('getRatesForDate')
                ->once()
                ->withArgs(function ($date, $base, $currencies) {
                    return $date instanceof \Carbon\CarbonInterface
                        && $date->toDateString() === '2025-08-10'
                        && strtoupper($base) === Currency::EUR->value;
                })
                ->andReturn(
                    new DailyRates(
                        date: Carbon::parse('2025-08-10'),
                        base: Currency::EUR->value,
                        quotes: [
                            new RateQuote(Currency::EUR->value, Currency::USD->value, 1.10),
                            new RateQuote(Currency::EUR->value, Currency::RSD->value, 117.50),
                        ],
                    )
                );
        });

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
}
