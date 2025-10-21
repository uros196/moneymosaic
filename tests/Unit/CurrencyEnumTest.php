<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Support\Money;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CurrencyEnumTest extends TestCase
{
    public function test_values_and_cases(): void
    {
        $values = Currency::values();
        sort($values);

        $expected = ['EUR', 'RSD', 'USD', 'GBP', 'CHF', 'CAD'];
        sort($expected);

        $this->assertSame($expected, $values);
        $this->assertCount(6, Currency::cases());
    }

    public function test_default_currency_is_eur(): void
    {
        Config::set('exchange.base_currency', 'EUR');

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

    public function test_format_major_rules(): void
    {
        $this->assertSame('$200', Money::formatMajor('200', Currency::USD));
        $this->assertSame('-$200', Money::formatMajor('-200', Currency::USD));

        $this->assertSame('200€', Money::formatMajor('200', Currency::EUR));
        $this->assertSame('-200€', Money::formatMajor('-200', Currency::EUR));

        $this->assertSame('200 RSD', Money::formatMajor('200', Currency::RSD));
        $this->assertSame('-200 RSD', Money::formatMajor('-200', Currency::RSD));
    }

    public function test_format_minor_conversion_and_rules(): void
    {
        // Positive
        $this->assertSame('123,45€', Money::formatMinor(12345, Currency::EUR));
        $this->assertSame('$0.99', Money::formatMinor(99, Currency::USD));
        $this->assertSame('200 RSD', Money::formatMinor(20000, Currency::RSD));
        $this->assertSame('£123.45', Money::formatMinor(12345, Currency::GBP));
        $this->assertSame('123.45 CHF', Money::formatMinor(12345, Currency::CHF));
        $this->assertSame('CA$123.45', Money::formatMinor(12345, Currency::CAD));

        // Negative
        $this->assertSame('-123,45€', Money::formatMinor(-12345, Currency::EUR));
        $this->assertSame('-$0.99', Money::formatMinor(-99, Currency::USD));
        $this->assertSame('-200 RSD', Money::formatMinor(-20000, Currency::RSD));
        $this->assertSame('-£123.45', Money::formatMinor(-12345, Currency::GBP));
        $this->assertSame('-123.45 CHF', Money::formatMinor(-12345, Currency::CHF));
        $this->assertSame('-CA$123.45', Money::formatMinor(-12345, Currency::CAD));
    }

    public function test_label_translations_in_english(): void
    {
        $this->assertSame('Euro', Currency::EUR->label());
        $this->assertSame('Dinar', Currency::RSD->label());
        $this->assertSame('US Dollar', Currency::USD->label());
    }
}
