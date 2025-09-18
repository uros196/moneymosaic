<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_update_delete_self(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('view', $user));
        $this->assertTrue($user->can('update', $user));
        $this->assertTrue($user->can('delete', $user));
    }

    public function test_user_cannot_view_update_delete_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->assertFalse($user->can('view', $other));
        $this->assertFalse($user->can('update', $other));
        $this->assertFalse($user->can('delete', $other));
    }
}
