<?php

namespace Tests\Feature\Incomes;

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

        $this->postJson(route('settings.lists.income-types.store'), [
            'name' => 'Custom Type',
        ]);

        $this->assertDatabaseHas('income_types', [
            'user_id' => $user->id,
            'name->en' => 'Custom Type',
            'name->sr' => 'Custom Type',
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
        $this->postJson(route('settings.lists.income-types.store'), [
            'name' => 'Salary',
        ])->assertStatus(422);

        // Create a user-owned type
        IncomeType::factory()->create([
            'user_id' => $user->id,
            'name' => 'Side',
        ]);

        // Duplicate for the same user should fail
        $this->postJson(route('settings.lists.income-types.store'), [
            'name' => 'Side',
        ])->assertStatus(422);
    }

    public function test_json_response_returns_created_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('settings.lists.income-types.store'), [
            'name' => 'Freelance',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'is_system']])
            ->assertJsonPath('data.name', 'Freelance');
    }

    public function test_cannot_delete_system_type_via_http(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $systemType = IncomeType::factory()->create(['user_id' => null]);

        $this->delete(route('settings.lists.income-types.destroy', $systemType))
            ->assertStatus(403);
    }

    public function test_cannot_delete_other_users_type_via_http(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $type = IncomeType::factory()->for($owner)->create();

        $this->actingAs($other)
            ->delete(route('settings.lists.income-types.destroy', $type))
            ->assertStatus(403);
    }
}
