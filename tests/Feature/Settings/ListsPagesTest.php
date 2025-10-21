<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ListsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_selector_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.lists'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('settings/lists/index')
            ->has('cards')
        );
    }

    public function test_income_types_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.lists.income-types'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('settings/lists/income-types')
            ->has('incomeTypes')
        );
    }
}
