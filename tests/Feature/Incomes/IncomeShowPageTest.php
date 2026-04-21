<?php

namespace Tests\Feature\Incomes;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('incomes.show', 1))->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_income_details_page(): void
    {
        $user = User::factory()->create();
        $income = Income::factory()->for($user)->create();

        $this->actingAs($user);
        $this->get(route('incomes.show', $income->id))
            ->assertOk();
    }
}
