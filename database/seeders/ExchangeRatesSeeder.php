<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use App\Support\Concerns\ParsesExchangeSymbols;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExchangeRatesSeeder extends Seeder
{
    use ParsesExchangeSymbols;

    /**
     * Seed some sample exchange rates for development/testing using the factory.
     *
     * - Seeds the last 3 days (today and two previous days).
     * - Uses global 'exchange.base_currency' and 'exchange.symbols' configuration.
     * - Idempotent via upsert to avoid unique constraint violations on re-run.
     */
    public function run(): void
    {
        $base = $this->configuredBaseCurrency();
        $symbols = $this->configuredSymbols();

        // Ensure the base exists among symbols for completeness (factory states handle base->base)
        if (! in_array($base, $symbols, true)) {
            $symbols[] = $base;
        }

        // Last 3 dates including today
        $dates = [
            Carbon::today()->subDays(2)->toDateString(),
            Carbon::today()->subDays(1)->toDateString(),
            Carbon::today()->toDateString(),
        ];

        foreach ($dates as $date) {

            // Base -> Base (1.0)
            ExchangeRate::factory()->baseToBase($base)->create([
                'date' => $date,
            ]);

            // Base -> Quote for all configured symbols (excluding base to avoid duplicates)
            foreach ($symbols as $quote) {
                $quote = strtoupper($quote);
                if ($quote === $base) {
                    continue;
                }

                ExchangeRate::factory()->forPair($base, $quote)->create([
                    'date' => $date,
                ]);
            }
        }
    }
}
