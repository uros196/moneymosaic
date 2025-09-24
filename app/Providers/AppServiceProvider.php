<?php

namespace App\Providers;

use App\Services\ExchangeRates\RateProvider;
use App\Services\ExchangeRates\RateProviderFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the exchange rates provider strategy via factory
        $this->app->bind(RateProvider::class, function (): RateProvider {
            return RateProviderFactory::make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
