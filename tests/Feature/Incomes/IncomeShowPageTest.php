<?php

namespace Tests\Feature;

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

        $this->actingAs(User::factory()->create());

        $this->get(route('incomes.show', 1))
            ->assertOk();
    }
}
