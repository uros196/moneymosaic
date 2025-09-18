<?php

namespace Database\Seeders;

use App\Enums\Currency;
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
                Currency::EUR->value => 1.0,
                Currency::USD->value => 1.10,
                Currency::RSD->value => 117.50,
            ],
            '2025-08-15' => [
                Currency::EUR->value => 1.0,
                Currency::USD->value => 1.09,
                Currency::RSD->value => 117.40,
            ],
            '2025-08-16' => [
                Currency::EUR->value => 1.0,
                Currency::USD->value => 1.11,
                Currency::RSD->value => 117.70,
            ],
        ];

        foreach ($data as $date => $quotes) {
            foreach ($quotes as $quote => $rate) {
                ExchangeRate::query()->updateOrCreate(
                    [
                        'date' => $date,
                        'base_currency_code' => Currency::EUR->value,
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
