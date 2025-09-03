<?php

namespace Tests\Unit;

use App\Enums\Currency;
use Tests\TestCase;

class CurrencyEnumTest extends TestCase
{
    public function test_values_and_cases(): void
    {
        $values = Currency::values();
        sort($values);

        $expected = ['EUR', 'RSD', 'USD'];
        sort($expected);

        $this->assertSame($expected, $values);
        $this->assertCount(3, Currency::cases());
    }

    public function test_default_currency_is_eur(): void
    {
        $this->assertSame(Currency::EUR, Currency::default());
        $this->assertSame('EUR', Currency::default()->value);
    }

    public function test_symbols_are_mapped_correctly(): void
    {
        $this->assertSame('€', Currency::EUR->symbol());
        $this->assertSame('$', Currency::USD->symbol());
        $this->assertSame('RSD', Currency::RSD->symbol());
    }

    public function test_fraction_digits_are_two_for_all(): void
    {
        foreach (Currency::cases() as $currency) {
            $this->assertSame(2, $currency->fractionDigits());
        }
    }
}
