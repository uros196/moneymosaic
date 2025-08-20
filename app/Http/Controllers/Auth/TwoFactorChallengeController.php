<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Services\TwoFactor\TwoFactorChallengeService;
use App\Services\TwoFactor\TwoFactorSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Presents and processes Two-Factor Authentication (2FA) challenges.
 *
 * Responsibilities:
 * - Shows the 2FA challenge screen when a user has 2FA enabled and is pending verification.
 * - Verifies submitted TOTP/email codes or recovery codes via the challenge service.
 * - Allows resending a new code using the current two-factor strategy.
 */
class TwoFactorChallengeController extends Controller
{
    /**
     * Show the Two-Factor Authentication (2FA) challenge page if required.
     *
     * @param  Request  $request  Current request instance.
     * @return Response|RedirectResponse Inertia response with challenge page or redirect if already verified.
     */
    public function create(Request $request, TwoFactorSessionService $tfSession): Response|RedirectResponse
    {
        $user = $request->user();

        // If user already passed 2FA or doesn't require it, redirect away
        if (! $user || ! $user->two_factor_enabled || $tfSession->hasPassed($request->session())) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('auth/two-factor-challenge', [
            'method' => $user->two_factor_type,
            'status' => session('status'),
        ]);
    }

    /**
     * Verify the submitted 2FA code or recovery code.
     *
     * @throws ValidationException When the provided authentication code is invalid.
     */
    public function store(TwoFactorChallengeRequest $request, TwoFactorChallengeService $service): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->two_factor_enabled) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $inputIsRecovery = $request->filled('recovery_code');
        $submitted = $inputIsRecovery
            ? (string) $request->string('recovery_code')
            : (string) $request->string('code');

        $ok = $service->attempt($user, $submitted, $request->session());
        if (! $ok) {
            throw ValidationException::withMessages([
                $inputIsRecovery ? 'recovery_code' : 'code' => __('The provided authentication code is invalid.'),
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend a new 2FA code using the current two-factor strategy.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user && $user->two_factor_enabled) {
            $user->two_factor_auth->beginChallenge($user, $request->session());
        }

        return back()->with('status', __('A new authentication code has been sent to your email address.'));
    }
}
