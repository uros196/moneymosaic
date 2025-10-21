<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_locale_and_it_is_persisted_in_session(): void
    {
        $this->post(route('locale.set'), ['locale' => 'sr'])
            ->assertRedirect();

        // After switching, the welcome page should reflect the new locale via shared props
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'sr')
        );
    }

    public function test_authenticated_user_switches_locale_and_it_is_saved_on_user(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->post(route('locale.set'), ['locale' => 'sr'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'sr',
        ]);

        // Inertia shared props should also reflect the new locale
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'sr')
        );
    }

    public function test_available_locales_shared_to_frontend(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('availableLocales', config('app.available_locales'))
        );
    }
}
