<?php

namespace App\Providers;

use App\Repositories\Contracts\ExchangeRateRepository as ExchangeRateRepositoryContract;
use App\Repositories\Contracts\SessionRepository as SessionRepositoryContract;
use App\Repositories\ExchangeRateRepository;
use App\Repositories\SessionRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository interfaces to their concrete implementations.
 *
 * Centralizes container bindings for repositories used across the app.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SessionRepositoryContract::class, SessionRepository::class);
        $this->app->bind(ExchangeRateRepositoryContract::class, ExchangeRateRepository::class);
    }
}
