<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Models\Income;
use Tests\TestCase;

class IncomeFormattedAmountTest extends TestCase
{
    public function test_formatted_amount_accessor_works_for_all_currencies(): void
    {
        $incomeEur = new Income([
            'amount_minor' => 12345,
            'currency_code' => Currency::EUR,
        ]);
        $this->assertSame('123,45€', $incomeEur->formatted_amount);

        $incomeUsd = new Income([
            'amount_minor' => -990,
            'currency_code' => Currency::USD,
        ]);
        $this->assertSame('-$9.9', $incomeUsd->formatted_amount);

        $incomeRsd = new Income([
            'amount_minor' => 20000,
            'currency_code' => Currency::RSD,
        ]);
        $this->assertSame('200 RSD', $incomeRsd->formatted_amount);
    }
}
