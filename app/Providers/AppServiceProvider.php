<?php

namespace App\Providers;

use App\Services\ExchangeRates\ExchangeRateProviderInterface;
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
        $this->app->bind(ExchangeRateProviderInterface::class, function (): ExchangeRateProviderInterface {
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
