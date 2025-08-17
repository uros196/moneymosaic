<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function sessionsTable(): string
    {
        return config('session.table', 'sessions');
    }

    public function test_sessions_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings/sessions')
            ->assertOk();
    }

    public function test_user_can_delete_specific_session(): void
    {
        $user = User::factory()->create();

        DB::table($this->sessionsTable())->insert([
            'id' => 'sess1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->actingAs($user)
            ->delete('/settings/sessions/sess1')
            ->assertStatus(302);

        $this->assertDatabaseMissing($this->sessionsTable(), [
            'id' => 'sess1',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_delete_other_sessions(): void
    {
        $user = User::factory()->create();

        DB::table($this->sessionsTable())->insert([
            [
                'id' => 'sessA',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'agent-A',
                'payload' => '',
                'last_activity' => time(),
            ],
            [
                'id' => 'sessB',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.2',
                'user_agent' => 'agent-B',
                'payload' => '',
                'last_activity' => time(),
            ],
        ]);

        $this->actingAs($user)
            ->post('/settings/sessions/others')
            ->assertStatus(302);

        $this->assertDatabaseCount($this->sessionsTable(), 0);
    }

    public function test_user_can_delete_all_sessions_and_is_logged_out(): void
    {
        $user = User::factory()->create();

        DB::table($this->sessionsTable())->insert([
            'id' => 'sessX',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.10',
            'user_agent' => 'agent-X',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->actingAs($user)
            ->post('/settings/sessions/all')
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseCount($this->sessionsTable(), 0);
    }
}
