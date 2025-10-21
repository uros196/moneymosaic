<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Load one or more translation files and expose them to Inertia.
 *
 * Usage in routes:
 *   ->middleware('translations:incomes')
 *   ->middleware('translations:incomes,common,auth')
 */
class LoadTranslationsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): mixed  $next
     * @param  string  ...$groups  Translation file (group) names to load
     */
    public function handle(Request $request, Closure $next, string ...$groups)
    {
        $groups = array_values(array_filter($groups, static fn ($g) => is_string($g) && $g !== ''));

        if (! empty($groups)) {
            $loaded = [];
            foreach ($groups as $group) {
                $loaded[$group] = trans($group);
            }

            $existing = (array) $request->attributes->get('translations.extra', []);
            $request->attributes->set('translations.extra', array_merge($existing, $loaded));
        }

        return $next($request);
    }
}
