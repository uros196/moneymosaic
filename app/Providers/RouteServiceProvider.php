<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Load routes.
     */
    public function boot(): void
    {
        $this->routes(function (): void {
            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Auth routes
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            // App routes (behind the login)
            Route::middleware(['web', 'auth', 'verified', '2fa', 'password.recent'])
                ->group(base_path('routes/app.php'));

            // Settings routes (web middleware applies + file contains its own sub-groups)
            Route::middleware(['web', 'auth', 'verified', '2fa', 'password.recent'])
                ->prefix('settings')
                ->group(base_path('routes/settings.php'));
        });
    }
}
