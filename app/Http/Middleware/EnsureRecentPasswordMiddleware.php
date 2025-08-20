<?php

namespace App\Http\Middleware;

use App\Services\Auth\PasswordConfirmationService;
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
    public function __construct(public PasswordConfirmationService $passwords) {}

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

        // If no user or user disabled the feature, allow
        $minutes = $user ? $this->passwords->getWindowMinutesForUser($user) : 0;
        if (! $user || $minutes === 0) {
            return $next($request);
        }

        if ($this->passwords->needsConfirmation($request)) {
            return redirect()->to(route('password.confirm', absolute: false));
        }

        // Still within window: refresh last interaction
        $this->passwords->confirmNow();

        return $next($request);
    }
}
