<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\PasswordConfirmationService;
use App\Services\TwoFactor\TwoFactorSessionService;
use App\Services\TwoFactor\UserTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles user login and logout.
 *
 * Responsibilities:
 * - Renders the login page via Inertia.
 * - Authenticates the user and initializes the session.
 * - If Two-Factor Authentication (2FA) is enabled, starts the challenge and redirects to the 2FA screen.
 * - Logs the user out and invalidates the session.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     *
     * @param  Request  $request  Incoming request to determine reset availability and session status.
     * @return Response Inertia response rendering the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * Authenticates the user and, if 2FA is enabled, initiates the 2FA challenge flow.
     *
     * @param  LoginRequest  $request  Validated login request.
     * @return RedirectResponse Redirects to either the 2FA challenge or intended dashboard.
     */
    public function store(LoginRequest $request, TwoFactorSessionService $tfSession, PasswordConfirmationService $passwords, UserTwoFactorService $user2fa): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Still within window: refresh last interaction
        $passwords->confirmNow();

        // If the user has 2FA enabled, redirect to the 2FA challenge
        $user = $request->user();
        if ($user && $user->two_factor_enabled) {
            // Mark challenge as pending via centralized session service
            $tfSession->beginChallenge($request->session());

            // Initialize the challenge via the appropriate strategy
            $user->two_factor_auth->beginChallenge($user, $request->session());

            return redirect()->route('twofactor.challenge');
        }

        // If 2FA is not enabled, consider showing a one-time reminder page exclusively post-login
        if ($user && $user2fa->shouldShowReminder($user, $request->session())) {
            return redirect()->route('twofactor.reminder');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     *
     * Logs out the user, invalidates the session, and regenerates the CSRF token.
     *
     * @param  Request  $request  Current HTTP request instance.
     * @return RedirectResponse Redirect to the homepage after logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
