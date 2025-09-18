<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages password confirmation flow for sensitive actions.
 *
 * Responsibilities:
 * - Displays the confirm password screen.
 * - Validates the provided password against the authenticated user.
 * - Records password confirmation and last interaction timestamps in session.
 * - Exposes an endpoint to check if confirmation is required due to inactivity.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirmation password page.
     */
    public function show(Request $request, PasswordConfirmationService $passwords): Response|RedirectResponse
    {
        $user = $request->user();
        $minutes = $user ? $passwords->getWindowMinutesForUser($user) : 0;

        // If feature disabled or still within confirmation window, redirect away
        if ($minutes === 0) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $passwords->needsConfirmation($request)) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('auth/confirm-password');
    }

    /**
     * Confirm the user's password.
     *
     * @throws ValidationException If the provided password is invalid.
     */
    public function store(Request $request, PasswordConfirmationService $passwords): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $passwords->validateForUser($user, (string) $request->password)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $passwords->confirmNow();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Check whether password confirmation is required due to inactivity.
     *
     * Behaviors:
     * - Guest: redirect to the login page.
     * - Authenticated and needs confirmation: redirect to the confirmation password page.
     * - Authenticated and within the confirmation window: 204 No Content.
     */
    public function needsConfirmation(Request $request, PasswordConfirmationService $passwords): RedirectResponse|\Illuminate\Http\Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->to(route('login', absolute: false));
        }

        $minutes = $passwords->getWindowMinutesForUser($user);
        if ($minutes <= 0) {
            return response()->noContent();
        }

        if ($passwords->needsConfirmation($request)) {
            return redirect()->to(route('password.confirm', absolute: false));
        }

        return response()->noContent();
    }
}
