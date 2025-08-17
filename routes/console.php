<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the daily exchange rates sync at 18:00 CET
Schedule::command('rates:sync')
    ->timezone('Europe/Belgrade')
    ->dailyAt('18:00');
