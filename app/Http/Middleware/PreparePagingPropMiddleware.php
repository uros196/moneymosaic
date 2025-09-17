<?php

namespace App\Http\Middleware;

use App\Support\TableConfig;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Prepare a paging prop for Inertia/React.
 *
 * Usage in routes:
 *   ->middleware('paging')                      // uses defaults and prop name "paging"
 *   ->middleware('paging:incomes')              // uses table key "incomes", prop name "paging"
 *   ->middleware('paging:incomes,listPaging')   // uses table key and custom prop name
 */
class PreparePagingPropMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): mixed  $next
     * @param  string|null  $tableKey  Table key from config/tables.php (e.g. "incomes")
     * @param  string  $propName  Inertia prop name to expose (default: "paging")
     */
    public function handle(Request $request, Closure $next, ?string $tableKey = null, string $propName = 'paging')
    {
        $prop = $propName !== '' ? $propName : 'paging';

        Inertia::share([
            $prop => TableConfig::pagingData($request, $tableKey),
        ]);

        return $next($request);
    }
}
