<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncomesModalRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_add_and_edit_modal_routes(): void
    {
        // Create an income to provide a valid ID for the edit route
        $income = Income::factory()->create();

        $this->get(route('incomes.create'))->assertRedirect(route('login'));
        $this->get(route('incomes.edit', $income))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_add_and_edit_modal_routes_and_incomes_is_deferred(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Access 'create' modal
        $this->get(route('incomes.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('incomes/index')
                ->has('modal')
                ->where('modal.type', 'create')
                ->where('modal.method', 'post')
                ->has('tagSuggestions')
                ->missing('incomes')
            );

        // Create an income that belongs to the authenticated user and access edit modal
        $income = Income::factory()->for($user)->create();
        $this->get(route('incomes.edit', $income))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('incomes/index')
                ->has('modal')
                ->where('modal.type', 'edit')
                ->where('modal.method', 'put')
                ->has('income')
                ->has('tagSuggestions')
                ->missing('incomes')
            );
    }

    public function test_authenticated_user_gets_404_when_editing_non_existent_income(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('incomes.edit', 9_999_999))->assertNotFound();
    }
}
