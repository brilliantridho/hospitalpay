<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily transaction report at 01:00 AM
Schedule::command('report:send-daily-transactions')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta');
