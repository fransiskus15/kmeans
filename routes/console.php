<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Jalankan setiap hari jam 07:00 pagi — cek aset yang melewati batas
// waktu pengembalian dan kirim notifikasi ke Admin Aset (FR-07)
Schedule::command('aset:cek-keterlambatan')->dailyAt('07:00');

// ═══════════════════════════════════════════════════════════════════════
// CARA MENJALANKAN SCHEDULER DI LOCAL (XAMPP/Windows)
// ═══════════════════════════════════════════════════════════════════════
//
// Scheduler Laravel TIDAK berjalan otomatis — perlu ada proses yang
// memicu Laravel mengecek jadwal setiap menit. Ada 2 cara:
//
// CARA 1 — Untuk development/testing (paling praktis):
//     Buka terminal terpisah, biarkan berjalan selama development:
//
//         php artisan schedule:work
//
//     Perintah ini akan otomatis menjalankan `aset:cek-keterlambatan`
//     setiap hari jam 07:00 selama terminal ini tetap terbuka.
//
// CARA 2 — Test manual langsung tanpa menunggu jadwal:
//
//         php artisan aset:cek-keterlambatan
//
//     Jalankan perintah ini kapan saja untuk memicu pengecekan secara
//     langsung — berguna untuk memastikan fitur bekerja sebelum sidang.
//
// CARA 3 — Untuk production nanti (server sungguhan):
//     Tambahkan satu baris ini ke Cron Job (Linux) server:
//
//         * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
//
//     Atau gunakan Task Scheduler (Windows Server) untuk menjalankan
//     `php artisan schedule:run` setiap menit.
 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
