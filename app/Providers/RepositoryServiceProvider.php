<?php

namespace App\Providers;

use App\Repositories\Contracts\ExchangeRateRepository;
use App\Repositories\Contracts\IncomeRepository;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Repositories\Contracts\SessionRepository;
use App\Repositories\Contracts\TagRepository;
use App\Repositories\Eloquent\EloquentExchangeRateRepository;
use App\Repositories\Eloquent\EloquentIncomeRepository;
use App\Repositories\Eloquent\EloquentIncomeTypeRepository;
use App\Repositories\Eloquent\EloquentSessionRepository;
use App\Repositories\Eloquent\EloquentTagRepository;
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
        $this->app->bind(SessionRepository::class, EloquentSessionRepository::class);
        $this->app->bind(ExchangeRateRepository::class, EloquentExchangeRateRepository::class);
        $this->app->bind(TagRepository::class, EloquentTagRepository::class);
        $this->app->bind(IncomeTypeRepository::class, EloquentIncomeTypeRepository::class);
        $this->app->bind(IncomeRepository::class, EloquentIncomeRepository::class);
    }
}
