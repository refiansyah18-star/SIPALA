<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pengingat rating otomatis (mengecek tiket SELESAI belum dinilai setiap hari pukul 08:00 dengan interval 7 hari/mingguan)
Schedule::command('sipala:send-rating-reminders --days=7')->dailyAt('08:00');
