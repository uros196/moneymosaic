<?php

namespace App\Console\Commands;

use App\Services\ExchangeRates\ExchangeRateSyncService;
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
        $syncService = app(ExchangeRateSyncService::class);

        $dateOption = (string) $this->option('date');
        $date = $dateOption !== '' ? Carbon::parse($dateOption) : Carbon::today();

        $this->info(sprintf('Rates sync job started for %s.', $date->toDateString()));

        try {
            $syncService->syncForDate($date);
        } catch (Throwable $e) {
            $this->error('Failed to sync rates: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Rates sync job finished.');

        return self::SUCCESS;
    }
}
