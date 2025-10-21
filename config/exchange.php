<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | The system's base currency used for rate pairs and conversions.
    |
    */

    'base_currency' => 'EUR',

    /*
    |--------------------------------------------------------------------------
    | Exchange Symbols (Global)
    |--------------------------------------------------------------------------
    |
    | A comma-separated list of currency codes that the application should
    | sync and work with. This is global for all providers. Keep this value
    | as a plain string; parsing to arrays is handled by classes using the
    | ParsesExchangeSymbols trait.
    |
    */

    'symbols' => env('EXCHANGE_SYMBOLS', 'USD,EUR,RSD,GBP,CHF,CAD'),

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default exchange rates provider used by the
    | application. You may set this via the "EXCHANGE_DRIVER" environment
    | variable.
    |
    | Supported drivers: "exchangerate_host"
    |
    */

    'default' => env('EXCHANGE_DRIVER', 'exchangerate_host'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Here you may define all the exchange rate providers for your
    | application as well as their driver-specific configuration options.
    | This configuration style is similar to config/database.php and
    | config/cache.php.
    |
    */

    'providers' => [
        'exchangerate_host' => [
            'driver' => \App\Services\ExchangeRates\Providers\ExchangeRateHostProvider::class,
            'base_url' => env('EXCHANGE_BASE_URL', 'https://api.exchangerate.host'),
            'api_key' => env('EXCHANGE_API_KEY'),
            'timeout' => (int) env('EXCHANGE_TIMEOUT', 10),
        ],
    ],
];
