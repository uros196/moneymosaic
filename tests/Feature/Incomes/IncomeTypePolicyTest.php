<?php

namespace Tests\Feature;

use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTypePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_any_is_allowed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('viewAny', IncomeType::class));
    }

    public function test_view_system_and_own_type_rules(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $systemType = IncomeType::factory()->create(['user_id' => null]);
        $ownType = IncomeType::factory()->for($owner)->create();

        // System type is visible to any authenticated user
        $this->assertTrue($owner->can('view', $systemType));
        $this->assertTrue($other->can('view', $systemType));

        // Own type is visible to owner
        $this->assertTrue($owner->can('view', $ownType));
        // Not visible to other users
        $this->assertFalse($other->can('view', $ownType));
    }

    public function test_create_is_allowed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('create', IncomeType::class));
    }

    public function test_update_and_delete_rules(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $systemType = IncomeType::factory()->create(['user_id' => null]);
        $ownType = IncomeType::factory()->for($owner)->create();

        // System types are not updatable/deletable
        $this->assertFalse($owner->can('update', $systemType));
        $this->assertFalse($owner->can('delete', $systemType));

        // Owner can update/delete own type
        $this->assertTrue($owner->can('update', $ownType));
        $this->assertTrue($owner->can('delete', $ownType));

        // Other users cannot update/delete someone else's type
        $this->assertFalse($other->can('update', $ownType));
        $this->assertFalse($other->can('delete', $ownType));
    }
}
