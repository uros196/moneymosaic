<?php

return [
    // Base currency for the system and rate pairs
    'base_currency' => 'EUR',

    // Provider configuration (using exchangerate.host by default)
    'provider' => [
        'base_url' => env('EXCHANGE_BASE_URL', 'https://api.exchangerate.host'),
        // Symbols to sync. We keep it configurable to extend easily.
        'symbols' => explode(',', env('EXCHANGE_SYMBOLS', 'USD,EUR,RSD')),
        // Timeout seconds for HTTP requests
        'timeout' => (int) env('EXCHANGE_TIMEOUT', 10),
    ],
];
