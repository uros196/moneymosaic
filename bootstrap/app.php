<?php

use App\Http\Middleware\EnsureRecentPasswordMiddleware;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Register route middleware aliases
        $middleware->alias([
            '2fa' => EnsureTwoFactorVerified::class,
            'password.recent' => EnsureRecentPasswordMiddleware::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            \App\Http\Middleware\SetLocale::class,
            HandleInertiaRequests::class,
            \App\Http\Middleware\UpdateSessionMetadata::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
