<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_edit_income_of_another_user(): void
    {
        $owner = User::factory()->create();
        $foreignIncome = Income::factory()->for($owner)->create();

        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('incomes.edit', $foreignIncome))
            ->assertStatus(403);
    }

    public function test_user_can_edit_own_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('incomes.edit', $income))
            ->assertOk();
    }

    public function test_user_cannot_update_income_of_another_user(): void
    {
        $owner = User::factory()->create();
        $foreignIncome = Income::factory()->for($owner)->create();

        $otherUser = User::factory()->create();

        // Prepare valid payload (reuse existing values)
        $payload = [
            'name' => $foreignIncome->name,
            'occurred_on' => $foreignIncome->occurred_on->format('Y-m-d'),
            'income_type_id' => $foreignIncome->income_type_id,
            'amount' => '12.34',
            'currency_code' => $foreignIncome->currency_code->value,
            'description' => $foreignIncome->description,
            'tags' => [],
        ];

        $this->actingAs($otherUser)
            ->put(route('incomes.update', $foreignIncome), $payload)
            ->assertStatus(403);
    }

    public function test_user_can_update_own_income(): void
    {
        $user = User::factory()->create();
        // Ensure a system income type exists the user can reference
        $type = IncomeType::factory()->create(['user_id' => null, 'name' => 'Salary']);
        $income = Income::factory()->for($user)->create([
            'income_type_id' => $type->id,
            'currency_code' => Currency::EUR,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'occurred_on' => now()->format('Y-m-d'),
            'income_type_id' => $type->id,
            'amount' => '99.99',
            'currency_code' => Currency::EUR->value,
            'description' => 'Updated description',
            'tags' => ['updated'],
        ];

        $this->actingAs($user)
            ->put(route('incomes.update', $income), $payload)
            ->assertRedirect(route('incomes.index'));

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'user_id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }
}
