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
     * - Seeds the last 30 days (today and previous days).
     * - Uses global 'exchange.base_currency' and 'exchange.symbols' configuration.
     * - Idempotent: skips a date entirely if any rate for that date already exists.
     */
    public function run(): void
    {
        $base = $this->configuredBaseCurrency();
        $symbols = $this->configuredSymbols();

        // Ensure the base exists among symbols for completeness (factory states handle base->base)
        if (! in_array($base, $symbols, true)) {
            $symbols[] = $base;
        }

        // Last N dates including today
        $days = 30;
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = Carbon::today()->subDays($i)->toDateString();
        }

        foreach ($dates as $date) {

            // Skip seeding if any rate already exists for this date to keep seeder idempotent
            if (ExchangeRate::query()->whereDate('date', $date)->exists()) {
                continue;
            }

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
