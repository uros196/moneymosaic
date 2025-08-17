<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles user registration.
 *
 * Responsibilities:
 * - Renders the registration page via Inertia.
 * - Validates and creates a new user account.
 * - Fires the Registered event and logs the user in.
 * - Redirects to email verification notice.
 */
class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     *
     * @return Response Inertia response rendering the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  RegisterRequest  $request  Validated registration request.
     * @return RedirectResponse Redirect to email verification notice after registration.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
