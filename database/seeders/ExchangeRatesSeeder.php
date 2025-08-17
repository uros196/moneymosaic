<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRatesSeeder extends Seeder
{
    /**
     * Seed some sample exchange rates for development/testing.
     */
    public function run(): void
    {
        $data = [
            '2025-08-14' => [
                'EUR' => 1.0,
                'USD' => 1.10,
                'RSD' => 117.50,
            ],
            '2025-08-15' => [
                'EUR' => 1.0,
                'USD' => 1.09,
                'RSD' => 117.40,
            ],
            '2025-08-16' => [
                'EUR' => 1.0,
                'USD' => 1.11,
                'RSD' => 117.70,
            ],
        ];

        foreach ($data as $date => $quotes) {
            foreach ($quotes as $quote => $rate) {
                ExchangeRate::query()->updateOrCreate(
                    [
                        'date' => $date,
                        'base_currency_code' => 'EUR',
                        'quote_currency_code' => $quote,
                    ],
                    [
                        'rate_multiplier' => $rate,
                    ]
                );
            }
        }
    }
}
