<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SessionRepository as SessionRepositoryContract;
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
     * @param  SessionRepositoryContract  $sessions  Repository for session management.
     */
    public function __construct(public SessionRepositoryContract $sessions)
    {
        //
    }

    /**
     * List active sessions for the authenticated user.
     *
     * @param  Request  $request  Current request instance.
     * @return Response Inertia response with sessions data.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentId = $request->session()->getId();

        $rows = $this->sessions->listForUser($user, $currentId);

        return Inertia::render('settings/sessions', [
            'sessions' => $rows,
        ]);
    }

    /**
     * Log out a specific session by ID for the current user.
     *
     * @param  Request  $request  Current request instance.
     * @param  string  $id  Session identifier to terminate.
     * @return RedirectResponse Redirects back or to home if current session was terminated.
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

        return back()->with('status', __('Session logged out.'));
    }

    /**
     * Log out all other sessions for the current user except the active one.
     *
     * @param  Request  $request  Current request instance.
     * @return RedirectResponse Redirects back with a status message.
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentId = $request->session()->getId();

        $this->sessions->deleteOthersForUserExcept($user, $currentId);

        return back()->with('status', __('Other sessions have been logged out.'));
    }

    /**
     * Log out all sessions for the current user and end the current session.
     *
     * @param  Request  $request  Current request instance.
     * @return RedirectResponse Redirects to home after logging out from all sessions.
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
