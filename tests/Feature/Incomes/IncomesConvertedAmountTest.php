<?php

namespace Tests\Feature\Incomes;

use App\Enums\Currency;
use App\Models\ExchangeRate;
use App\Models\Income;
use App\Models\User;
use App\Services\IncomeService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncomesConvertedAmountTest extends TestCase
{
    use RefreshDatabase;

    public function test_converted_amount_is_included_and_formatted_when_currency_differs(): void
    {
        $user = User::factory()->create();

        // Known date and rate
        $date = Carbon::parse('2025-08-10');
        $convertTo = Currency::USD;

        ExchangeRate::factory()
            ->forPair(Currency::EUR, Currency::USD)
            ->create([
                'date' => $date->toDateString(),
                'rate_multiplier' => 1.10,
            ]);

        // Create one income in EUR on that date
        $income = Income::factory()
            ->for($user)
            ->create([
                'amount_minor' => 10000, // 100.00 EUR
                'currency_code' => Currency::EUR,
                'occurred_on' => $date->toDateString(),
            ]);

        // Prepare expectation value
        $expected = app(IncomeService::class)
            ->convertIncomeToCurrency($income, $convertTo);

        // Perform a partial reload request for only the 'incomes' prop with the currency param
        $this->actingAs($user)
            ->get(route('incomes.index', ['currency' => $convertTo->value]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // Now request only the deferred prop and assert it's returned
                ->reloadOnly('incomes', function (Assert $partial) use ($income, $expected) {
                    $partial->has('incomes.data', 1)
                        ->where('incomes.data.0.id', fn ($id) => $id !== null)
                        // Expect 100 EUR -> 110 USD formatted ("$110" per Money rules)
                        ->where('incomes.data.0.converted_amount', fn ($value) => $value === $expected)
                        ->where('incomes.data.0.amount_formatted', Money::formatMinor($income->amount_minor, Currency::EUR));
                })
            );
    }
}
