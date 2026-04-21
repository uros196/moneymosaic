<?php

namespace Tests\Feature\Incomes;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\Tag;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTagsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSystemType(string $name = 'Salary'): IncomeType
    {
        return IncomeType::factory()->create([
            'user_id' => null,
            'name' => $name,
        ]);
    }

    public function test_income_can_be_tagged_per_user_type(): void
    {
        $locale = app()->getLocale();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $type = $this->seedSystemType('Salary');

        $income1 = Income::create([
            'user_id' => $user1->id,
            'amount_minor' => 100_00,
            'currency_code' => Currency::EUR,
            'income_type_id' => $type->id,
            'occurred_on' => now()->toDateString(),
        ]);

        $income2 = Income::create([
            'user_id' => $user2->id,
            'amount_minor' => 200_00,
            'currency_code' => Currency::USD,
            'income_type_id' => $type->id,
            'occurred_on' => now()->toDateString(),
        ]);

        // Tag both with the same label, but scoped by user type (should create separate tags under different types)
        $income1->syncUserTags(['work']);
        $income2->syncUserTags(['work']);

        $type1 = Income::tagTypeForUser($user1);
        $type2 = Income::tagTypeForUser($user2);

        $tag1 = Tag::query()
            ->where('type', $type1)
            ->where("name->$locale", 'work')
            ->first();
        $tag2 = Tag::query()
            ->where('type', $type2)
            ->where("name->$locale", 'work')
            ->first();

        $this->assertNotNull($tag1, 'Tag for user1 not found');
        $this->assertNotNull($tag2, 'Tag for user2 not found');
        $this->assertNotSame($tag1->id, $tag2->id, 'Tags should be distinct records per user type');
    }

    public function test_user_tag_suggestions_are_scoped(): void
    {
        $tagService = app(TagService::class);

        $user = User::factory()->create();
        $type = $this->seedSystemType('Salary');

        $incomes = [
            Income::create([
                'user_id' => $user->id,
                'amount_minor' => 101_00,
                'currency_code' => Currency::EUR,
                'income_type_id' => $type->id,
                'occurred_on' => now()->toDateString(),
            ]),
            Income::create([
                'user_id' => $user->id,
                'amount_minor' => 102_00,
                'currency_code' => Currency::EUR,
                'income_type_id' => $type->id,
                'occurred_on' => now()->toDateString(),
            ]),
        ];

        $incomes[0]->syncUserTags(['bonus', 'freelance']);
        $incomes[1]->syncUserTags(['salary']);

        // Another user's tag should not appear in this user's type
        $other = User::factory()->create();
        $otherIncome = Income::create([
            'user_id' => $other->id,
            'amount_minor' => 500_00,
            'currency_code' => Currency::USD,
            'income_type_id' => $type->id,
            'occurred_on' => now()->toDateString(),
        ]);
        $otherIncome->syncUserTags(['bonus']);

        $names = $tagService->getSuggestions($user)->pluck('name');

        $this->assertTrue($names->contains('bonus'), 'Bonus tag should be suggested');
        $this->assertTrue($names->contains('freelance'), 'Freelance tag should be suggested');
        $this->assertTrue($names->contains('salary'), 'Salary tag should be suggested');

        // Ensure the other user's tag of the same textual label is not mixed in this type (it would be in another type)
        $otherNames = $tagService->getSuggestions($other)->pluck('name');

        $this->assertTrue($otherNames->contains('bonus'), 'Bonus tag should be suggested');
        // Still, user type segregation implies tags are separate rows under different 'type'
        $this->assertNotEqualsCanonicalizing($names->all(), $otherNames->all());
    }
}
