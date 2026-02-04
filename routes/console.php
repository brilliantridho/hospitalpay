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
    ->timezone('Asia/Jakarta')
    ->emailOutputOnFailure(config('mail.from.address'));

// For testing: every minute (uncomment untuk test)
// Schedule::command('report:send-daily-transactions')
//     ->everyMinute()
//     ->timezone('Asia/Jakarta');

// Alternative schedule options:
// ->dailyAt('09:00')           // Setiap hari jam 9 pagi
// ->dailyAt('17:00')           // Setiap hari jam 5 sore
// ->weeklyOn(1, '08:00')       // Setiap Senin jam 8 pagi
// ->monthlyOn(1, '00:00')      // Setiap tanggal 1 jam 12 malam
// ->everyFiveMinutes()         // Setiap 5 menit (untuk testing)
