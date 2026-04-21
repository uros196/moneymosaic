<?php

namespace Tests\Feature\Incomes;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_deleting_income(): void
    {
        $income = Income::factory()->create();

        $response = $this->delete(route('incomes.destroy', $income));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('incomes', ['id' => $income->id]);
    }

    public function test_user_can_delete_own_income(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('incomes.destroy', $income));

        // back(303)
        $response->assertStatus(303);
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_user_cannot_delete_income_of_another_user(): void
    {
        $owner = User::factory()->create();
        $foreignIncome = Income::factory()->for($owner)->create();

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->delete(route('incomes.destroy', $foreignIncome));

        $response->assertStatus(403);
        $this->assertDatabaseHas('incomes', ['id' => $foreignIncome->id]);
    }
}
