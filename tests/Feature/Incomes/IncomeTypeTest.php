<?php

namespace Tests\Feature;

use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_income_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('incomes.types.store'), [
            'name' => 'Custom Type',
        ]);

        $this->assertDatabaseHas('income_types', [
            'user_id' => $user->id,
            'name' => 'Custom Type',
        ]);
    }

    public function test_validation_error_on_duplicate_within_scope(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // System type exists
        IncomeType::factory()->create([
            'user_id' => null,
            'name' => 'Salary',
        ]);

        // Attempt to create the same name should fail (conflicts with a system type)
        $this->postJson(route('incomes.types.store'), [
            'name' => 'Salary',
        ])->assertStatus(422);

        // Create a user-owned type
        IncomeType::factory()->create([
            'user_id' => $user->id,
            'name' => 'Side',
        ]);

        // Duplicate for the same user should fail
        $this->postJson(route('incomes.types.store'), [
            'name' => 'Side',
        ])->assertStatus(422);
    }
}
