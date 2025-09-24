<?php

namespace Tests\Unit;

use App\DTO\ExchangeRates\DailyRates;
use App\DTO\ExchangeRates\RateQuote;
use App\Enums\Currency;
use App\Services\ExchangeRates\ExchangeRateSyncService;
use App\Services\ExchangeRates\RateProvider;
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

        // Mock the RateProvider::getRatesForRange to return three DailyRates
        $this->mock(RateProvider::class, function ($mock) use ($start, $end) {
            $mock->shouldReceive('getRatesForRange')
                ->once()
                ->withArgs(function ($s, $e, $base, $currencies) use ($start, $end) {
                    return $s instanceof \Carbon\CarbonInterface
                        && $e instanceof \Carbon\CarbonInterface
                        && $s->toDateString() === $start->toDateString()
                        && $e->toDateString() === $end->toDateString()
                        && strtoupper($base) === Currency::EUR->value
                        && is_array($currencies);
                })
                ->andReturn([
                    new DailyRates(
                        date: Carbon::parse('2025-08-10'),
                        base: Currency::EUR->value,
                        quotes: [
                            new RateQuote(Currency::EUR->value, Currency::USD->value, 1.20),
                            new RateQuote(Currency::EUR->value, Currency::RSD->value, 118.00),
                        ],
                    ),
                    new DailyRates(
                        date: Carbon::parse('2025-08-11'),
                        base: Currency::EUR->value,
                        quotes: [
                            new RateQuote(Currency::EUR->value, Currency::USD->value, 1.15),
                            new RateQuote(Currency::EUR->value, Currency::RSD->value, 117.70),
                        ],
                    ),
                    new DailyRates(
                        date: Carbon::parse('2025-08-12'),
                        base: Currency::EUR->value,
                        quotes: [
                            new RateQuote(Currency::EUR->value, Currency::USD->value, 1.10),
                            new RateQuote(Currency::EUR->value, Currency::RSD->value, 117.50),
                        ],
                    ),
                ]);
        });

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
