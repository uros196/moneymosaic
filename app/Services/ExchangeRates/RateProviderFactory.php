<?php

namespace App\Services\ExchangeRates;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class RateProviderFactory
{
    /**
     * Create a provider instance using the configured default driver.
     */
    public static function make(?string $driver = null): RateProvider
    {
        return self::makeFor(! empty($driver)
            ? $driver
            : (string) config('exchange.default')
        );
    }

    /**
     * Create a provider instance for the given driver.
     */
    public static function makeFor(string $driver): RateProvider
    {
        $driver = strtolower($driver);

        // Init the requested provider
        if (! is_null($config = config("exchange.providers.{$driver}"))) {
            /** @var RateProvider $instance */
            $instance = app(Arr::pull($config, 'driver'), $config);

            return $instance;
        }

        throw new InvalidArgumentException("Unsupported exchange rates driver: {$driver}");
    }
}
