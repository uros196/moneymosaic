<?php

namespace Tests\Unit;

use App\Facades\ExchangeRateProvider;
use App\Services\ExchangeRates\Providers\ExchangeRateHostProvider;
use App\Services\ExchangeRates\RateProviderFactory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateHostProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_resolves_default_provider(): void
    {
        Config::set('exchange.default', 'exchangerate_host');

        $this->assertInstanceOf(ExchangeRateHostProvider::class, ExchangeRateProvider::getFacadeRoot());
    }

    public function test_exchange_rate_host_provider_parses_historical_response(): void
    {
        Config::set('exchange.base_currency', 'EUR');
        Config::set('exchange.default', 'exchangerate_host');
        Config::set('exchange.symbols', 'USD,RSD');

        $date = Carbon::parse('2025-08-13');

        Http::fake([
            'api.exchangerate.host/*' => Http::response([
                'success' => true,
                'historical' => true,
                'source' => 'EUR',
                'date' => '2025-08-13',
                'quotes' => [
                    'EURUSD' => 1.1,
                    'EURRSD' => 117.5,
                ],
            ], 200),
        ]);

        $daily = ExchangeRateProvider::getRatesForDate($date, 'EUR', ['USD', 'RSD']);

        $this->assertSame('EUR', $daily->base);
        $this->assertSame('2025-08-13', $daily->date->toDateString());

        $map = $daily->toMap();
        $this->assertSame(1.1, $map['USD']);
        $this->assertSame(117.5, $map['RSD']);
        $this->assertSame(1.0, $map['EUR']); // base should be included
    }

    public function test_exchange_rate_host_provider_parses_timeframe_response(): void
    {
        Config::set('exchange.base_currency', 'EUR');
        Config::set('exchange.default', 'exchangerate_host');
        Config::set('exchange.symbols', 'USD');

        $start = Carbon::parse('2025-08-10');
        $end = Carbon::parse('2025-08-12');

        Http::fake([
            'api.exchangerate.host/*' => Http::response([
                'success' => true,
                'source' => 'EUR',
                'start_date' => '2025-08-10',
                'end_date' => '2025-08-12',
                'quotes' => [
                    '2025-08-10' => ['EURUSD' => 1.2],
                    '2025-08-11' => ['EURUSD' => 1.15],
                    '2025-08-12' => ['EURUSD' => 1.1],
                ],
            ], 200),
        ]);

        $days = ExchangeRateProvider::getRatesForRange($start, $end, 'EUR', ['USD']);

        $this->assertCount(3, $days);
        $this->assertContainsOnlyInstancesOf(\App\DTO\ExchangeRates\DailyRates::class, $days);
        $this->assertSame(['2025-08-10', '2025-08-11', '2025-08-12'], array_map(fn ($d) => $d->date->toDateString(), $days));

        $this->assertSame(1.2, $days[0]->toMap()['USD']);
        $this->assertSame(1.15, $days[1]->toMap()['USD']);
        $this->assertSame(1.1, $days[2]->toMap()['USD']);
    }

    public function test_make_for_returns_requested_driver(): void
    {
        $provider = RateProviderFactory::makeFor('exchangerate_host');
        $this->assertInstanceOf(ExchangeRateHostProvider::class, $provider);
    }
}
