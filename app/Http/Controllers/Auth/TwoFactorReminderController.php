<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TwoFactorSessionService;
use App\Services\TwoFactor\UserTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Presents a reminder to enable Two-Factor Authentication (2FA) after login
 * and handles a user's choice (enable, skip, snooze).
 */
class TwoFactorReminderController extends Controller
{
    /**
     * TwoFactorReminderController constructor.
     */
    public function __construct(protected UserTwoFactorService $user2fa, protected TwoFactorSessionService $tfSession) {}

    /**
     * Show the 2FA reminder page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        // If a user already enabled 2FA or is not authenticated, just send them forward
        $user = $request->user();
        if (! $user || $user->two_factor_enabled) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('auth/two-factor-reminder', [
            'snoozeDays' => $this->user2fa->getReminderSnoozeDays(),
        ]);
    }

    /**
     * Skip the reminder for the current session only.
     */
    public function skip(Request $request): RedirectResponse
    {
        $this->tfSession->markReminderSkipped($request->session());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Snooze the reminder by setting a timestamp on the user.
     */
    public function snooze(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $this->user2fa->snoozeReminder($user);
        }

        // Clear any session skip flag as we've now persisted the choice
        $this->tfSession->clearReminderSkipped($request->session());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
