<?php

namespace Tests\Unit\Support\FilterChips;

use App\Enums\Currency;
use App\Models\IncomeType;
use App\Support\FilterChips\ArrayChip;
use App\Support\FilterChips\CurrencyChip;
use App\Support\FilterChips\MinMaxRangeChip;
use App\Support\FilterChips\ModelChip;
use App\Support\FilterChips\StringChip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterChipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_string_chip_basic(): void
    {
        $chip = StringChip::make('hello')->label('Search')->removeKeys('query');

        $array = $chip->toArray();

        $this->assertSame('Search', $array['label']);
        $this->assertSame('hello', $array['valueLabel']);
        $this->assertSame(['query'], $array['removeKeys']);
    }

    public function test_currency_chip_displays_translation_key(): void
    {
        $chip = CurrencyChip::make(Currency::EUR);
        $array = $chip->toArray();

        // We don't assert exact translation value, just that it returns a non-empty string
        $this->assertNotSame([], $array);
        $this->assertIsString($array['valueLabel']);
        $this->assertNotSame('', $array['valueLabel']);
        $this->assertContains('currency_code', $array['removeKeys']);
    }

    public function test_model_chip_resolves_name(): void
    {
        $type = IncomeType::factory()->create(['name' => 'Consulting']);

        $chip = ModelChip::make(IncomeType::class, $type->getKey())
            ->label('Type')
            ->removeKeys('income_type');

        $array = $chip->toArray();

        $this->assertSame('Type', $array['label']);
        $this->assertSame('Consulting', $array['valueLabel']);
        $this->assertSame(['income_type'], $array['removeKeys']);
    }

    public function test_empty_string_chip_is_filtered_out(): void
    {
        $chip = StringChip::make('');
        $this->assertTrue($chip->isEmpty());
        $this->assertSame([], $chip->toArray());
    }

    public function test_array_chip_basic(): void
    {
        $chip = ArrayChip::make(['one', 'two'])
            ->label('Tags')
            ->removeKeys('tags');

        $array = $chip->toArray();

        $this->assertSame('Tags', $array['label']);
        $this->assertSame('one, two', $array['valueLabel']);
        $this->assertSame(['tags'], $array['removeKeys']);
    }

    public function test_min_max_range_chip_basic(): void
    {
        $chip = MinMaxRangeChip::make(10, 20)
            ->label('Amount')
            ->removeKeys('amount_min', 'amount_max');

        $array = $chip->toArray();

        $this->assertSame('Amount', $array['label']);
        $this->assertSame('10 – 20', $array['valueLabel']);
        $this->assertSame(['amount_min', 'amount_max'], $array['removeKeys']);
    }

    public function test_min_max_range_chip_empty_when_both_missing(): void
    {
        $chip = MinMaxRangeChip::make(null, null);
        $this->assertTrue($chip->isEmpty());
        $this->assertSame([], $chip->toArray());
    }
}
