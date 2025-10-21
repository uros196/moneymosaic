<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Defaults
    |--------------------------------------------------------------------------
    |
    | Generic defaults for tables across the app (can be overridden per table
    | below). The per_page section defines allowed options, and the default
    | selection is used when a value is not provided or invalid.
    |
    */
    'defaults' => [
        'per_page' => [
            'options' => [10, 25, 50, 100],
            'default' => 25,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table-specific configuration
    |--------------------------------------------------------------------------
    |
    | Here you can override settings for individual tables.
    |
    */
    'incomes' => [
        'per_page' => [
            'options' => [10, 25, 50, 100],
            'default' => 25,
        ],
    ],
];
