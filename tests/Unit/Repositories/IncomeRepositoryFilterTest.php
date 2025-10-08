<?php

namespace Tests\Unit\Repositories;

use App\DTO\Incomes\IncomeFiltersData;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeRepositoryFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSystemType(string $name = 'Salary'): IncomeType
    {
        return IncomeType::factory()->create([
            'user_id' => null,
            'name' => $name,
        ]);
    }

    public function test_filters_by_tags_scoped_per_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $user1_incomes = Income::factory()->for($user)->count(2)->create();

        $user1_income1 = $user1_incomes->get(0);
        $user1_income2 = $user1_incomes->get(1);

        $user2_income1 = Income::factory()->for($other)->create();

        // Tag incomes
        $user1_income1->syncUserTags(['bonus']);
        $user1_income2->syncUserTags(['salary']);
        $user2_income1->syncUserTags(['bonus']);

        /** @var IncomeRepository $repo */
        $repo = app(IncomeRepository::class);

        $filters = new IncomeFiltersData(tags: ['bonus']);
        $page = $repo->paginateForUser($user, $filters, perPage: 50);

        $this->assertGreaterThanOrEqual(1, $page->total());
        $ids = collect($page->items())->pluck('id')->all();

        $this->assertContains($user1_income1->id, $ids, 'Should include current user income tagged bonus');
        $this->assertNotContains($user1_income2->id, $ids, 'Should exclude current user income without the tag');
        $this->assertNotContains($user2_income1->id, $ids, 'Should not leak other user tagged records');
    }
}
