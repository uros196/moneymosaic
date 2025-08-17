<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use App\Services\ExchangeRates\RateProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class SyncExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:sync {--date= : Date to sync (YYYY-MM-DD), defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync daily exchange rates from the configured provider';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateOption = (string) $this->option('date');
        $date = $dateOption !== '' ? Carbon::parse($dateOption) : Carbon::today();

        $this->info(sprintf('Rates sync job started for %s.', $date->toDateString()));

        $provider = app(RateProvider::class);

        try {
            $rates = $provider->getRatesForDate($date);
        } catch (Throwable $e) {
            $this->error('Failed to fetch rates: '.$e->getMessage());

            return self::FAILURE;
        }

        $base = strtoupper((string) config('exchange.base_currency', 'EUR'));

        foreach ($rates as $quote => $rate) {
            $quote = strtoupper($quote);

            // Skip base->base here; we'll enforce it once below to avoid duplicates
            if ($quote === $base) {
                continue;
            }

            // Persist only configured symbols to avoid accidental writes
            if (! in_array($quote, array_map('strtoupper', (array) config('exchange.provider.symbols')), true)) {
                continue;
            }

            ExchangeRate::query()->updateOrCreate(
                [
                    'date' => $date->toDateString(),
                    'base_currency_code' => $base,
                    'quote_currency_code' => $quote,
                ],
                [
                    'rate_multiplier' => (float) $rate,
                ]
            );
        }

        // Ensure base->base = 1.0 exists even if provider omitted it
        ExchangeRate::query()->updateOrCreate(
            [
                'date' => $date->toDateString(),
                'base_currency_code' => $base,
                'quote_currency_code' => $base,
            ],
            ['rate_multiplier' => 1.0]
        );

        $this->info('Rates sync job finished.');

        return self::SUCCESS;
    }
}
