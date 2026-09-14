<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Models\Pengguna;
use App\Models\Notifikasi;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CekKeterlambatanAset extends Command
{
    /**
     * Nama & signature command.
     * Jalankan manual untuk testing: php artisan aset:cek-keterlambatan
     */
    protected $signature = 'aset:cek-keterlambatan';

    protected $description = 'Cek peminjaman yang melewati batas waktu pengembalian dan kirim notifikasi ke Admin Aset (FR-07)';

    public function handle(): int
    {
        $this->info('Mengecek peminjaman yang melewati batas waktu...');

        // ── 1. Ambil semua peminjaman yang sudah lewat batas & belum dikembalikan ──
        $terlambat = Peminjaman::with(['peminjam', 'aset'])
            ->whereIn('status', ['Disetujui', 'Dipinjam'])
            ->whereNull('tgl_kembali_aktual')
            ->where('tgl_rencana_kembali', '<', Carbon::today())
            ->get();

        if ($terlambat->isEmpty()) {
            $this->info('Tidak ada aset yang melewati batas waktu pengembalian.');
            return self::SUCCESS;
        }

        // ── 2. Ambil semua akun Admin Aset (penerima notifikasi) ──────────
        $adminList = Pengguna::where('role', 'admin_aset')
            ->where('status_akun', 'aktif')
            ->get();

        if ($adminList->isEmpty()) {
            $this->warn('Tidak ada akun Admin Aset aktif untuk menerima notifikasi.');
            return self::FAILURE;
        }

        $jumlahNotifikasiBaru = 0;

        foreach ($terlambat as $p) {
            $hariTerlambat = Carbon::today()->diffInDays(Carbon::parse($p->tgl_rencana_kembali));

            foreach ($adminList as $admin) {
                // ── Cegah notifikasi duplikat untuk peminjaman yang sama ──
                // Hanya kirim SATU notifikasi keterlambatan per peminjaman per admin,
                // bukan diulang setiap hari command ini berjalan
                $sudahAda = Notifikasi::where('pengguna_id', $admin->id_pengguna)
                    ->where('peminjaman_id', $p->id_peminjaman)
                    ->where('tipe', 'keterlambatan')
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                Notifikasi::create([
                    'pengguna_id'   => $admin->id_pengguna,
                    'peminjaman_id' => $p->id_peminjaman,
                    'judul'         => 'Aset melewati batas pengembalian',
                    'pesan'         => "{$p->peminjam->nama} belum mengembalikan {$p->aset->nama_aset}. "
                                        . "Sudah terlambat {$hariTerlambat} hari dari batas waktu "
                                        . Carbon::parse($p->tgl_rencana_kembali)->format('d M Y') . '.',
                    'tipe'          => 'keterlambatan',
                    'dibaca'        => false,
                ]);

                $jumlahNotifikasiBaru++;
            }
        }

        $this->info("Selesai. {$terlambat->count()} peminjaman terlambat ditemukan, "
                    . "{$jumlahNotifikasiBaru} notifikasi baru dikirim ke Admin Aset.");

        return self::SUCCESS;
    }
}