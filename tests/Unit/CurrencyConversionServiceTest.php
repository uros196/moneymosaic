<?php

namespace Tests\Unit;

use App\Models\ExchangeRate;
use App\Services\CurrencyConversionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrencyConversionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CurrencyConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CurrencyConversionService;
        Cache::flush();
    }

    public function test_passthrough_same_currency(): void
    {
        $date = Carbon::parse('2025-08-10');
        $amount = 12345;

        $converted = $this->service->convert($amount, 'USD', 'USD', $date);

        $this->assertSame($amount, $converted);
    }

    public function test_convert_base_to_quote(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::create([
            'date' => $date->toDateString(),
            'base_currency_code' => 'EUR',
            'quote_currency_code' => 'USD',
            'rate_multiplier' => 1.10,
        ]);

        $amountEur = 10000; // 100.00 EUR in minor units
        $converted = $this->service->convert($amountEur, 'EUR', 'USD', $date);
        $this->assertSame(11000, $converted); // 110.00 USD
    }

    public function test_convert_quote_to_base(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::create([
            'date' => $date->toDateString(),
            'base_currency_code' => 'EUR',
            'quote_currency_code' => 'USD',
            'rate_multiplier' => 1.10,
        ]);

        $amountUsd = 11000; // 110.00 USD
        $converted = $this->service->convert($amountUsd, 'USD', 'EUR', $date);
        $this->assertSame(10000, $converted); // 100.00 EUR
    }

    public function test_convert_cross_via_eur(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::insert([
            [
                'date' => $date->toDateString(),
                'base_currency_code' => 'EUR',
                'quote_currency_code' => 'USD',
                'rate_multiplier' => 1.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => $date->toDateString(),
                'base_currency_code' => 'EUR',
                'quote_currency_code' => 'RSD',
                'rate_multiplier' => 117.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $amountUsd = 10000; // 100.00 USD
        $converted = $this->service->convert($amountUsd, 'USD', 'RSD', $date);
        // 100 / 1.1 * 117.5 = 10681.818 RSD -> 1,068,182 minor units (rounded)
        $this->assertSame(1068182, $converted);
    }

    public function test_rollback_within_bound(): void
    {
        $rateDate = Carbon::parse('2025-08-10');
        $targetDate = Carbon::parse('2025-08-12'); // 2 days after

        ExchangeRate::create([
            'date' => $rateDate->toDateString(),
            'base_currency_code' => 'EUR',
            'quote_currency_code' => 'USD',
            'rate_multiplier' => 1.20,
        ]);

        $amountEur = 10000;
        $converted = $this->service->convert($amountEur, 'EUR', 'USD', $targetDate, maxLookbackDays: 3);
        $this->assertSame(12000, $converted);
    }

    public function test_throws_when_beyond_lookback(): void
    {
        $rateDate = Carbon::parse('2025-08-10');
        $targetDate = Carbon::parse('2025-08-20'); // 10 days after

        ExchangeRate::create([
            'date' => $rateDate->toDateString(),
            'base_currency_code' => 'EUR',
            'quote_currency_code' => 'USD',
            'rate_multiplier' => 1.20,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->service->convert(10000, 'EUR', 'USD', $targetDate, maxLookbackDays: 7);
    }
}
