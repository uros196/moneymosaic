<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ToastType;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SessionRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages user's active sessions in Settings.
 *
 * Responsibilities:
 * - Lists active sessions for the authenticated user.
 * - Logs out a specific session, other sessions, or all sessions.
 * - If the current session is terminated, logs the user out and invalidates the session.
 */
class SessionsController extends Controller
{
    /**
     * @param  SessionRepository  $sessions  Repository for session management.
     */
    public function __construct(public SessionRepository $sessions)
    {
        //
    }

    /**
     * List active sessions for the authenticated user.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentId = $request->session()->getId();

        return Inertia::render('settings/sessions', [
            'sessions' => $this->sessions->listForUser($user, $currentId),
        ]);
    }

    /**
     * Log out a specific session by ID for the current user.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        $currentId = $request->session()->getId();

        $deleted = $this->sessions->deleteByIdForUser($id, $user);

        if ($deleted && $id === $currentId) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        }

        return back()->with(ToastType::Success->message(__('Session logged out.')));
    }

    /**
     * Log out all other sessions for the current user except the active one.
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentId = $request->session()->getId();

        $this->sessions->deleteOthersForUserExcept($user, $currentId);

        return back()->with(ToastType::Success->message(__('Other sessions have been logged out.')));
    }

    /**
     * Log out all sessions for the current user and end the current session.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->sessions->deleteAllForUser($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
