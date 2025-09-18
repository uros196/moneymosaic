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
            'two_factor_type' => TwoFactorType::Email,
        ]);

        // Set the referer so that back()->with() knows where to redirect back to
        $referer = route('twofactor.challenge', absolute: false);

        // The first three requests should succeed (302 redirect back)
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

        // The fourth request within the same one-minute window should be rejected with 429
        $this->from($referer)
            ->post(route('twofactor.resend'))
            ->assertStatus(429);

        // The notification should have been sent exactly 3 times during the first three requests
        Notification::assertSentToTimes($user, TwoFactorCodeNotification::class, 3);
    }
}
