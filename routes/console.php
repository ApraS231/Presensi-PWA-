<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Evaluasi Ketidakhadiran Harian & Auto-Alpha (18:00 WITA)
Schedule::command('attendance:generate-alpha')
    ->dailyAt('18:00')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping()
    ->runInBackground();

// 2. Penutupan Otomatis Sesi Presensi Menggantung (23:59 WITA)
Schedule::command('attendance:auto-checkout')
    ->dailyAt('23:59')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping()
    ->runInBackground();

// 3. Pembersihan Otomatis Retensi Data Jejak Lokasi 30 Hari (02:00 WITA)
Schedule::command('tracking:cleanup --days=30')
    ->dailyAt('02:00')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping()
    ->runInBackground();
