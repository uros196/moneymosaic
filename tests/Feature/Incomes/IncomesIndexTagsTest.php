<?php

namespace Tests\Feature\Incomes;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncomesIndexTagsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSystemType(string $name = 'Salary'): IncomeType
    {
        return IncomeType::factory()->create([
            'user_id' => null,
            'name' => $name,
        ]);
    }

    public function test_incomes_index_includes_tags_list_when_loaded(): void
    {
        $user = User::factory()->create();
        $type = $this->seedSystemType('Salary');

        $income = Income::factory()
            ->for($user)
            ->for($type)
            ->create([
                'amount_minor' => 123_00,
                'currency_code' => Currency::EUR,
            ]);

        $income->syncUserTags(['alpha', 'beta', 'gamma']);

        $this->actingAs($user)
            ->get(route('incomes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // Now request only the deferred prop and assert it's returned
                ->reloadOnly('incomes', function (Assert $partial) {
                    $partial->has('incomes.data', 1)
                        ->where('incomes.data.0.tags_list', fn (Collection $list) => $list->intersect(['alpha', 'beta', 'gamma'])->count() === 3
                        );
                })
            );
    }
}
