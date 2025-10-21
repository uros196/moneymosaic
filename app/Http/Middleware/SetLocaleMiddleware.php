<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that sets the application locale for the current request.
 *
 * Prefers the authenticated user's preferred locale, then a session-stored
 * locale for guests, falling back to the configured app locale and finally
 * to 'en' as a safe default.
 */
class SetLocaleMiddleware
{
    /**
     * Set the application locale for the current request.
     *
     * @param  Request  $request  Current HTTP request, possibly with an authenticated user.
     * @param  Closure(Request):Response  $next  Next middleware/controller.
     * @return Response Response with locale applied.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? config('app.locale');

        app()->setLocale($locale ?: 'en');

        return $next($request);
    }
}
