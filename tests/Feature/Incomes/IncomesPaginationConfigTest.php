<?php

namespace Tests\Feature\Incomes;

use App\Models\Income;
use App\Models\User;
use App\Support\TableConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncomesPaginationConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_configured_per_page_and_options_and_defers_incomes(): void
    {
        $paging = TableConfig::paging('incomes');

        $user = User::factory()->create();
        Income::factory()->for($user)->count(60)->create();

        $this->actingAs($user)
            ->get(route('incomes.index', ['perPage' => $paging['default']]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('incomes/index')
                ->where('paging.perPage', $paging['default'])
                ->where('paging.options', $paging['options'])
                // 'incomes' is deferred on an initial load
                ->missing('incomes')
                // other lightweight props are present
                ->has('currencies')
                ->has('incomeTypes')
                // Now request only the deferred prop and assert it's returned
                ->reloadOnly('incomes', function (Assert $partial) use ($paging) {
                    $partial->has('incomes.data', $paging['default']);
                })
            );
    }

    public function test_invalid_per_page_falls_back_to_default(): void
    {
        $user = User::factory()->create();
        Income::factory()->for($user)->count(5)->create();

        // Invalid perPage (not in config options) should fail validation
        $this->actingAs($user)
            ->get(route('incomes.index', ['perPage' => 999]))
            ->assertInvalid(['perPage']);
    }
}
