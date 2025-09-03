<?php

namespace Tests\Feature\Auth;

use App\Enums\TwoFactorType;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TwoFactorResendThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_is_throttled_after_three_requests_per_minute(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_type' => TwoFactorType::Email->value,
        ]);

        // Postavljamo referer kako bi back()->with() znao gde da se vrati
        $referer = route('twofactor.challenge', absolute: false);

        // Prva tri zahteva treba da prođu (302 redirect nazad)
        $this->actingAs($user)
            ->from($referer)
            ->post(route('twofactor.resend'))
            ->assertRedirect($referer);

        $this->from($referer)
            ->post(route('twofactor.resend'))
            ->assertRedirect($referer);

        $this->from($referer)
            ->post(route('twofactor.resend'))
            ->assertRedirect($referer);

        // Četvrti zahtev u istom prozoru treba da bude odbijen sa 429
        $this->from($referer)
            ->post(route('twofactor.resend'))
            ->assertStatus(429);

        // Notifikacija treba da je poslata tačno 3 puta tokom prva tri zahteva
        Notification::assertSentToTimes($user, TwoFactorCodeNotification::class, 3);
    }
}
