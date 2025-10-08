<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncomeFiltersPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_payload_is_provided_with_expected_shape(): void
    {
        $user = User::factory()->create();
        Income::factory()->for($user)->count(3)->create();

        $this->actingAs($user)
            ->get(route('incomes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('incomes/index')
                ->has('filters.fields')
                ->has('filters.meta', fn (Assert $meta) => $meta
                    ->has('total')
                    ->has('applied')
                    ->has('keys')
                )
                ->where('filterChips', [])
            );
    }

    public function test_filters_meta_applied_counts_query_params(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('incomes.index', [
                'query' => 'abc',
                'date_from' => '2024-01-01',
                'amount_min' => '10',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.meta.applied', fn ($applied) => $applied >= 3)
            );
    }
}
