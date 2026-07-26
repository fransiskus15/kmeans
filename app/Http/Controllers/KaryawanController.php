<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    private function karyawanData(): array
    {
        return [
            'nama' => 'Budi Santoso',
            'role' => 'Karyawan',
            'inisial' => 'BS',
            'unread_notifikasi' => 3,
        ];
    }

    private function notifikasiData(): array
    {
        return [
            [
                'id' => 1,
                'type' => 'approved',
                'kategori' => 'peminjaman',
                'judul' => 'Permintaan peminjaman disetujui',
                'pesan' => 'Kamera Sony A7 III disetujui oleh Siti Rahayu (HR). Ambil ke Admin Aset.',
                'waktu' => '5 mnt lalu',
                'dibaca' => false,
            ],
            [
                'id' => 2,
                'type' => 'reminder',
                'kategori' => 'peminjaman',
                'judul' => 'Pengingat: batas pengembalian hari ini',
                'pesan' => 'Laptop Dell XPS 15 harus dikembalikan hari ini (05 Jul).',
                'waktu' => '2 jam lalu',
                'dibaca' => false,
            ],
            [
                'id' => 3,
                'type' => 'rejected',
                'kategori' => 'peminjaman',
                'judul' => 'Permintaan peminjaman ditolak',
                'pesan' => 'Tripod Manfrotto ditolak. Alasan: Aset dibutuhkan untuk liputan prioritas minggu ini.',
                'waktu' => 'Kemarin',
                'dibaca' => false,
            ],
            [
                'id' => 4,
                'type' => 'returned',
                'kategori' => 'peminjaman',
                'judul' => 'Pengembalian aset dikonfirmasi',
                'pesan' => 'Admin mengkonfirmasi pengembalian Drone DJI Mini 3 Pro pada 28 Jun dalam kondisi baik.',
                'waktu' => '28 Jun',
                'dibaca' => true,
            ],
            [
                'id' => 5,
                'type' => 'approved',
                'kategori' => 'peminjaman',
                'judul' => 'Permintaan peminjaman disetujui',
                'pesan' => 'Drone DJI Mini 3 Pro disetujui. Periode pinjam: 20–28 Jun 2025.',
                'waktu' => '20 Jun',
                'dibaca' => true,
            ],
        ];
    }

    public function dashboard()
    {
        return view('karyawan.dashboard', [
            'karyawan' => $this->karyawanData(),
            'activeMenu' => 'dashboard',
            'stats' => [
                'sedang_dipinjam' => 2,
                'menunggu_approval' => 1,
                'total_peminjaman' => 14,
            ],
            'peminjaman_aktif' => [
                ['aset' => 'Kamera Sony A7', 'batas_kembali' => '09 Jul', 'status' => 'Aktif'],
                ['aset' => 'Laptop Dell XPS', 'batas_kembali' => '05 Jul', 'status' => 'Terlambat'],
            ],
            'menunggu_approval' => [
                ['aset' => 'Mikrofon Rode NT1', 'tgl_ajukan' => '04 Jul', 'status' => 'Menunggu'],
            ],
            'riwayat_terakhir' => [
                ['aset' => 'Tripod Manfrotto', 'kembali' => '28 Jun', 'ket' => 'Tepat waktu'],
                ['aset' => 'Drone DJI Mini', 'kembali' => '20 Jun', 'ket' => 'Tepat waktu'],
                ['aset' => 'Kamera Canon EOS', 'kembali' => '10 Jun', 'ket' => 'Terlambat 2hr'],
            ],
            'profil' => [
                'total_peminjaman' => 14,
                'rata_durasi' => 4.2,
                'tingkat_keterlambatan' => '7.1%',
                'keterangan' => 'baik',
            ],
        ]);
    }

    public function pengajuan()
    {
        return view('karyawan.pengajuan', [
            'karyawan' => $this->karyawanData(),
            'activeMenu' => 'pengajuan',
            'kategori_aset' => [
                ['id' => '1', 'nama' => 'Kamera & peralatan foto'],
                ['id' => '2', 'nama' => 'Laptop & komputer'],
                ['id' => '3', 'nama' => 'Audio & mikrofon'],
                ['id' => '4', 'nama' => 'Proyektor & display'],
            ],
            'aset_tersedia' => [
                ['id' => '1', 'kategori_id' => '1', 'nama' => 'Kamera Sony A7 III', 'status' => 'Tersedia'],
                ['id' => '2', 'kategori_id' => '1', 'nama' => 'Kamera Canon EOS R6', 'status' => 'Tersedia'],
                ['id' => '3', 'kategori_id' => '2', 'nama' => 'Laptop Dell XPS 15', 'status' => 'Tersedia'],
                ['id' => '4', 'kategori_id' => '2', 'nama' => 'MacBook Pro 14"', 'status' => 'Tersedia'],
                ['id' => '5', 'kategori_id' => '3', 'nama' => 'Mikrofon Rode NT1', 'status' => 'Tersedia'],
                ['id' => '6', 'kategori_id' => '4', 'nama' => 'Proyektor Epson EB-X06', 'status' => 'Tersedia'],
            ],
            'form' => [
                'kategori_aset' => '1',
                'aset_id' => '1',
                'tanggal_pinjam' => '2025-07-07',
                'tanggal_kembali' => '2025-07-14',
                'keperluan' => 'Peliputan acara pameran UMKM Batam Centre',
            ],
            'info_teks' => 'Kamera Sony A7 III tersedia. Durasi peminjaman: 7 hari. Permintaan akan diteruskan ke HR untuk disetujui.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_aset' => ['required', 'string'],
            'aset_id' => ['required', 'string'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali' => ['required', 'date', 'after:tanggal_pinjam'],
            'keperluan' => ['required', 'string', 'max:1000'],
        ]);

        return redirect()
            ->route('karyawan.pengajuan')
            ->with('success', 'Permintaan peminjaman berhasil diajukan dan menunggu persetujuan HR.');
    }

    public function notifikasi()
    {
        $notifikasi = $this->notifikasiData();
        $unreadCount = collect($notifikasi)->where('dibaca', false)->count();

        return view('karyawan.notifikasi', [
            'karyawan' => array_merge($this->karyawanData(), [
                'unread_notifikasi' => $unreadCount,
            ]),
            'activeMenu' => 'notifikasi',
            'notifikasi' => $notifikasi,
            'unreadCount' => $unreadCount,
        ]);
    }
}
