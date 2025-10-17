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

    public function test_filter_fields_include_error_keys(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('incomes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // query (input)
                ->where('filters.fields.0.key', 'query')
                ->where('filters.fields.0.errorKeys', ['query'])
                // date (date-range)
                ->where('filters.fields.1.key', 'date')
                ->where('filters.fields.1.fromKey', 'date_from')
                ->where('filters.fields.1.toKey', 'date_to')
                ->where('filters.fields.1.errorKeys', ['date_from', 'date_to'])
                // amount (min-max)
                ->where('filters.fields.2.key', 'amount')
                ->where('filters.fields.2.minKey', 'amount_min')
                ->where('filters.fields.2.maxKey', 'amount_max')
                ->where('filters.fields.2.errorKeys', ['amount_minor_min', 'amount_minor_max'])
                // tags (simple)
                ->where('filters.fields.3.key', 'tags')
                ->where('filters.fields.3.errorKeys', ['tags'])
                // income type (select)
                ->where('filters.fields.4.key', 'income_type')
                ->where('filters.fields.4.errorKeys', ['income_type'])
                // currency (select)
                ->where('filters.fields.5.key', 'currency_code')
                ->where('filters.fields.5.errorKeys', ['currency_code'])
            );
    }
}
