<?php

namespace Tests\Feature\Incomes;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_factory_creates_valid_income(): void
    {
        $income = Income::factory()->create();

        $this->assertNotNull($income->id);
        $this->assertInstanceOf(User::class, $income->user);
        $this->assertIsInt($income->amount_minor);
        $this->assertInstanceOf(Currency::class, $income->currency_code);
        $this->assertContains($income->currency_code->value, Currency::values());
        $this->assertTrue($income->occurred_on instanceof \Illuminate\Support\Carbon);
        $this->assertNotNull($income->income_type_id);
        $this->assertNotNull($income->incomeType);
    }

    public function test_can_mass_assign_income_fields(): void
    {
        $user = User::factory()->create();

        // Ensure a system type exists to reference
        $salaryType = IncomeType::factory()->create([
            'user_id' => null,
            'name' => 'Salary',
        ]);

        $income = Income::factory()
            ->for($user)
            ->for($salaryType)
            ->create([
                'amount_minor' => 123_456,
                'currency_code' => Currency::EUR,
                'description' => 'Monthly salary',
                'occurred_on' => '2025-08-01',
            ]);

        $this->assertInstanceOf(Currency::class, $income->currency_code);
        $this->assertSame(Currency::EUR->value, $income->currency_code->value);

        // Encrypted columns (name, description, amount_minor) cannot be asserted directly in the database
        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'user_id' => $user->id,
            'currency_code' => Currency::EUR->value,
            'income_type_id' => $salaryType->id,
        ]);

        // Verify decrypted value via Eloquent
        $this->assertSame(123_456, $income->amount_minor);

        $this->assertSame('Monthly salary', $income->description);
        $this->assertEquals('2025-08-01', $income->occurred_on->format('Y-m-d'));
        $this->assertTrue($income->user->is($user));
        $this->assertSame('Salary', $income->incomeType->name);
    }
}
