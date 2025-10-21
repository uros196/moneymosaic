<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_authenticated_users_can_visit_the_dashboard_after_skipping_reminder_for_session()
    {
        $this->actingAs(User::factory()->create());

        // Even if we call skip, dashboard should be accessible as reminder shows only post-login
        $this->post(route('twofactor.reminder.skip'));

        $this->get(route('dashboard'))->assertOk();
    }
}
