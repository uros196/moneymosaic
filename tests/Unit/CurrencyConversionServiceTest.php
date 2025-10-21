<?php

namespace Tests\Unit;

use App\Enums\Currency;
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
        $this->service = app(CurrencyConversionService::class);
        Cache::flush();
    }

    public function test_passthrough_same_currency(): void
    {
        $date = Carbon::parse('2025-08-10');
        $amount = 123;

        $converted = $this->service->convert($amount, Currency::USD, Currency::USD, $date);

        $this->assertSame($amount, $converted);
    }

    public function test_convert_base_to_quote(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);

        $amountEur = 100; // 100.00 EUR in major units
        $converted = $this->service->convert($amountEur, Currency::EUR, Currency::USD, $date);
        $this->assertSame(110, $converted); // 110.00 USD
    }

    public function test_convert_quote_to_base(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);

        $amountUsd = 110; // 110.00 USD
        $converted = $this->service->convert($amountUsd, Currency::USD, Currency::EUR, $date);
        $this->assertSame(100, $converted); // 100.00 EUR
    }

    public function test_convert_cross_via_eur(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::RSD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 117.50,
            ]);

        $amountUsd = 100; // 100.00 USD
        $converted = $this->service->convert($amountUsd, Currency::USD, Currency::RSD, $date);
        // 100 / 1.1 * 117.5 = 10681.818 RSD -> 10,681.82 (rounded)
        $this->assertIsFloat($converted);
        $this->assertEquals(10681.82, $converted);
    }

    public function test_rollback_within_bound(): void
    {
        $rateDate = Carbon::parse('2025-08-10');
        $targetDate = Carbon::parse('2025-08-12'); // 2 days after

        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $rateDate->toDateString(),
                'rate_multiplier' => 1.20,
            ]);

        $amountEur = 100;
        $converted = $this->service->convert($amountEur, Currency::EUR, Currency::USD, $targetDate, fallback: true);
        $this->assertSame(120, $converted);
    }

    public function test_throws_when_exact_missing_and_fallback_disabled(): void
    {
        $rateDate = Carbon::parse('2025-08-10');
        $targetDate = Carbon::parse('2025-08-20'); // 10 days after

        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $rateDate->toDateString(),
                'rate_multiplier' => 1.20,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->service->convert(100, Currency::EUR, Currency::USD, $targetDate, fallback: false);
    }

    public function test_convert_minor_base_to_quote(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);

        $minorEur = 10000; // 100.00 EUR
        $converted = $this->service->convertMinor($minorEur, Currency::EUR, Currency::USD, $date);
        $this->assertSame(110, $converted); // 110.00 USD
    }

    public function test_convert_minor_cross_via_eur(): void
    {
        $date = Carbon::parse('2025-08-10');
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);
        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::RSD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 117.50,
            ]);

        $minorUsd = 10000; // 100.00 USD
        $converted = $this->service->convertMinor($minorUsd, Currency::USD, Currency::RSD, $date);
        $this->assertIsFloat($converted);
        $this->assertEquals(10681.82, $converted);
    }
}
