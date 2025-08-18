<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Show the confirm password page.
     *
     * @return Response Inertia response rendering the confirm password page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $minutes = (int) ($user?->password_confirm_minutes ?? 0);

        // If feature disabled or still within confirmation window, redirect away
        if ($minutes === 0) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $last = (int) $request->session()->get('auth.password_confirmed_at', 0);
        $now = time();
        if ($last > 0 && ($now - $last) < ($minutes * 60)) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('auth/confirm-password', [
            'logout' => route('logout', absolute: false),
        ]);
    }

    /**
     * Confirm the user's password.
     *
     * @param  Request  $request  The current request containing the password to validate.
     * @return RedirectResponse Redirect to the intended page after confirmation.
     *
     * @throws ValidationException If the provided password is invalid.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Check whether password confirmation is required due to inactivity.
     *
     * @param  Request  $request  The current request containing user session data.
     * @return JsonResponse JSON payload with required (bool) and redirect (string) URL.
     */
    public function needsConfirmation(Request $request): JsonResponse
    {
        $user = $request->user();
        $minutes = (int) ($user?->password_confirm_minutes ?? 0);

        $required = false;
        if ($user && $minutes > 0) {
            $last = (int) $request->session()->get('auth.password_confirmed_at', 0);
            $required = $last === 0 || (time() - $last) >= ($minutes * 60);
        }

        return response()->json([
            'required' => $required,
            'redirect' => route('password.confirm', absolute: false),
        ]);
    }
}
