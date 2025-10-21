<?php

namespace App\Facades;

use App\Services\ExchangeRates\ExchangeRateFakeProvider;
use App\Services\ExchangeRates\ExchangeRateProviderInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing exchange rate provider functionality.
 * Provides a simple interface for fetching currency exchange rates.
 */
class ExchangeRateProvider extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ExchangeRateProviderInterface::class;
    }

    /**
     * Replace the bound instance with a fake implementation.
     */
    public static function fake(array $response = []): void
    {
        tap(new ExchangeRateFakeProvider, function (ExchangeRateFakeProvider $fake) use ($response) {
            static::swap($fake->setFakeResponse($response));
        });
    }
}
