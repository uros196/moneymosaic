<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeStoreUpdateToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_flash_is_set_after_store(): void
    {
        $user = User::factory()->create();
        $type = IncomeType::factory()->create(['user_id' => null, 'name' => 'Salary']);

        $payload = [
            'name' => 'Test income',
            'occurred_on' => now()->format('Y-m-d'),
            'income_type_id' => $type->id,
            'amount' => '10.00',
            'currency_code' => Currency::EUR->value,
            'description' => 'Just a test',
            'tags' => [],
        ];

        $response = $this->actingAs($user)
            ->post(route('incomes.store'), $payload);

        $response->assertRedirect(route('incomes.index'));
        $response->assertSessionHas('success', trans('incomes.toasts.created'));
    }

    public function test_success_flash_is_set_after_update(): void
    {
        $user = User::factory()->create();
        $type = IncomeType::factory()->create(['user_id' => null, 'name' => 'Salary']);
        $income = Income::factory()->for($user)->create([
            'income_type_id' => $type->id,
            'currency_code' => Currency::EUR,
        ]);

        $payload = [
            'name' => 'Updated income',
            'occurred_on' => now()->format('Y-m-d'),
            'income_type_id' => $type->id,
            'amount' => '15.50',
            'currency_code' => Currency::EUR->value,
            'description' => 'Updated',
            'tags' => ['x'],
        ];

        $response = $this->actingAs($user)
            ->put(route('incomes.update', $income), $payload);

        $response->assertRedirect(route('incomes.index'));
        $response->assertSessionHas('success', trans('incomes.toasts.updated'));
    }
}
