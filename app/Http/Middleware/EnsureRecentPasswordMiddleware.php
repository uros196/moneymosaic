<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce recent password confirmation after inactivity.
 *
 * Checks the user's per-session password_confirm_minutes setting and redirects
 * to the password confirmation page when the inactivity threshold is exceeded.
 * Refreshes the last interaction timestamp on allowed requests.
 */
class EnsureRecentPasswordMiddleware
{
    /**
     * Require recent password confirmation based on user's per-session inactivity setting.
     *
     * @param  Request  $request  Current HTTP request.
     * @param  Closure(Request):Response  $next  Next middleware/controller.
     * @return Response Response after enforcing confirmation or redirecting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $session = $request->session();

        // If no user or user disabled the feature, allow
        $minutes = (int) ($user?->password_confirm_minutes ?? 0);
        if (! $user || $minutes === 0) {
            return $next($request);
        }

        $now = time();
        $lastInteraction = (int) $session->get('auth.password_confirmed_at', 0);

        // If never confirmed in this session, require confirmation
        if ($lastInteraction === 0) {
            return redirect()->guest(route('password.confirm'));
        }

        $inactiveSeconds = $now - $lastInteraction;
        $threshold = $minutes * 60;

        // If over threshold, redirect to password confirm unless we're already on confirm routes
        if ($inactiveSeconds >= $threshold) {
            return redirect()->guest(route('password.confirm'));
        }

        // Still within window: refresh last interaction
        $session->put('auth.password_confirmed_at', $now);

        return $next($request);
    }
}
