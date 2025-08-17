<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the user has passed Two-Factor Authentication (2FA).
 *
 * If 2FA is enabled for the user and they have not yet passed the challenge,
 * redirects them to the 2FA challenge route. Allows 2FA routes and logout
 * to bypass verification to avoid redirect loops.
 */
class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  Current HTTP request.
     * @param  Closure(Request):Response  $next  Next middleware/controller.
     * @return Response Response after enforcing 2FA or redirecting to challenge.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled) {
            // Allow the 2FA challenge routes and logout to proceed without passing 2FA
            if ($request->routeIs('twofactor.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            if (! (bool) $request->session()->get('2fa_passed', false)) {
                // Mark pending if not set, used for flow detection
                $request->session()->put('2fa_pending', true);

                return redirect()->route('twofactor.challenge');
            }
        }

        return $next($request);
    }
}
