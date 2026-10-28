<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Generate alpha otomatis setiap hari kerja pukul 23:59
| Menandai karyawan yang tidak hadir, tidak ada izin/cuti, bukan hari libur
|
*/
// Reminder ulang tahun karyawan — setiap hari pukul 08:00
Schedule::command('hr:reminder-ulang-tahun')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/reminder-ulang-tahun.log'));

// Reminder kontrak akan habis & sertifikat pelatihan expired — setiap pagi pukul 08:02
Schedule::command('hr:reminder-kontrak')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/reminder-kontrak.log'));

// Auto-close SP yang valid_until sudah lewat — setiap hari pukul 00:05
Schedule::command('sp:auto-close')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-close-sp.log'));

// Notifikasi pelatihan mendekati/sudah expired — setiap pagi pukul 08:05
Schedule::command('pelatihan:notif-expired')
    ->dailyAt('08:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/notif-pelatihan.log'));

// Reset saldo cuti tahunan — setiap 1 Januari pukul 00:30
Schedule::command('cuti:reset-saldo')
    ->yearlyOn(1, 1, '00:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/reset-saldo-cuti.log'));

// Cleanup notifikasi lama (sudah dibaca > 30 hari) — setiap Minggu pukul 02:00
Schedule::command('notifikasi:cleanup --hari=30')
    ->weekly()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/cleanup-notifikasi.log'));

// Jam generate alpha & rekap baca dari pengaturan (default fallback jika belum diset)
$jamAlpha = \App\Models\Pengaturan::get('jam_generate_alpha', '23:00');
$jamRekap  = \App\Models\Pengaturan::get('jam_rekap_harian',  '17:00');

// Senin–Sabtu (pabrik farmasi 6 hari kerja)
Schedule::command('absensi:generate-alpha')
    ->dailyAt($jamAlpha)
    ->days([1,2,3,4,5,6])
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alpha-absensi.log'));

// Deteksi alpha 3 hari berturut-turut — 10 menit setelah generate-alpha
$jamAlphaBerturut = date('H:i', strtotime($jamAlpha) + 600);
Schedule::command('absensi:deteksi-alpha-berturut')
    ->dailyAt($jamAlphaBerturut)
    ->days([1,2,3,4,5,6])
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/alpha-berturut.log'));

// Daily summary absensi ke HRD — jam dari pengaturan
Schedule::command('absensi:daily-summary')
    ->dailyAt($jamRekap)
    ->days([1,2,3,4,5,6])
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/daily-summary.log'));

// Notif lupa scan keluar — tiap 30 menit, jam 15:00–23:30 (hari kerja)
Schedule::command('absensi:notif-lupa-keluar')
    ->everyThirtyMinutes()
    ->between('15:00', '23:30')
    ->days([1,2,3,4,5,6])
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/notif-lupa-keluar.log'));

// Auto-close shift malam yang belum scan keluar — setiap pagi pukul 08:15
Schedule::command('absensi:auto-close-shift-malam')
    ->dailyAt('08:15')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-close-shift-malam.log'));
