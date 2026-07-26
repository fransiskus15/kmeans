<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function adminData(): array
    {
        return [
            'nama' => 'Admin Aset',
            'inisial' => 'AA',
        ];
    }

    public function aset()
    {
        return view('admin.aset', [
            'admin' => $this->adminData(),
            'activeMenu' => 'aset',
            'stats' => [
                'total_aset' => 54,
                'tersedia' => 38,
                'dipinjam' => 12,
                'perbaikan' => 4,
            ],
            'kategori' => [
                'Kamera & Foto',
                'Laptop & PC',
                'Audio',
                'Display',
                'Peralatan studio',
            ],
            'aset' => [
                [
                    'kode' => 'AST-001',
                    'nama' => 'Kamera Sony A7 III',
                    'kategori' => 'Kamera & Foto',
                    'nilai' => 18000000,
                    'kondisi' => 'Baik',
                    'status' => 'Dipinjam',
                ],
                [
                    'kode' => 'AST-002',
                    'nama' => 'Laptop Dell XPS 15',
                    'kategori' => 'Laptop & PC',
                    'nilai' => 22500000,
                    'kondisi' => 'Baik',
                    'status' => 'Tersedia',
                ],
                [
                    'kode' => 'AST-003',
                    'nama' => 'Drone DJI Mini 3',
                    'kategori' => 'Kamera & Foto',
                    'nilai' => 12000000,
                    'kondisi' => 'Perlu perawatan',
                    'status' => 'Perbaikan',
                ],
                [
                    'kode' => 'AST-004',
                    'nama' => 'Mikrofon Rode NT1',
                    'kategori' => 'Audio',
                    'nilai' => 4500000,
                    'kondisi' => 'Baik',
                    'status' => 'Tersedia',
                ],
                [
                    'kode' => 'AST-005',
                    'nama' => 'Proyektor Epson EB-X06',
                    'kategori' => 'Display',
                    'nilai' => 6800000,
                    'kondisi' => 'Baik',
                    'status' => 'Tersedia',
                ],
            ],
        ]);
    }

    private function peminjamanData(): array
    {
        return [
            [
                'id' => 'PJM-012',
                'peminjam' => 'Budi Santoso',
                'divisi' => 'Liputan',
                'aset' => 'Kamera Sony A7 III',
                'nilai_aset' => 18000000,
                'tanggal_pinjam' => '02 Jul 2025',
                'batas_kembali' => '09 Jul 2025',
                'tanggal_kembali_default' => '2025-07-09',
                'status' => 'Dipinjam',
                'terlambat_hari' => 0,
                'cluster' => 'A — Aktif & Disiplin',
                'tepat_waktu' => true,
            ],
            [
                'id' => 'PJM-010',
                'peminjam' => 'Rina Kurnia',
                'divisi' => 'Redaksi',
                'aset' => 'Laptop Dell XPS 15',
                'nilai_aset' => 22500000,
                'tanggal_pinjam' => '28 Jun 2025',
                'batas_kembali' => '05 Jul 2025',
                'tanggal_kembali_default' => '2025-07-19',
                'status' => 'Dipinjam',
                'terlambat_hari' => 3,
                'cluster' => 'B — Perlu perhatian',
                'tepat_waktu' => false,
            ],
            [
                'id' => 'PJM-011',
                'peminjam' => 'Doni Prasetyo',
                'divisi' => 'Multimedia',
                'aset' => 'Mikrofon Rode NT1',
                'nilai_aset' => 4500000,
                'tanggal_pinjam' => '01 Jul 2025',
                'batas_kembali' => '08 Jul 2025',
                'tanggal_kembali_default' => '2025-07-08',
                'status' => 'Dipinjam',
                'terlambat_hari' => 0,
                'cluster' => 'A — Aktif & Disiplin',
                'tepat_waktu' => true,
            ],
            [
                'id' => 'PJM-009',
                'peminjam' => 'Sinta Dewi',
                'divisi' => 'Desain',
                'aset' => 'Tripod Manfrotto 190',
                'nilai_aset' => 3200000,
                'tanggal_pinjam' => '30 Jun 2025',
                'batas_kembali' => '07 Jul 2025',
                'tanggal_kembali_default' => '2025-07-07',
                'status' => 'Dikembalikan',
                'terlambat_hari' => 0,
                'cluster' => 'A — Aktif & Disiplin',
                'tepat_waktu' => true,
            ],
        ];
    }

    public function peminjaman(Request $request)
    {
        $peminjaman = $this->peminjamanData();
        $pilih = $request->query('pilih');

        $selected = null;
        if ($pilih) {
            foreach ($peminjaman as $row) {
                if ($row['id'] === $pilih && $row['status'] !== 'Dikembalikan') {
                    $selected = $row;
                    break;
                }
            }
        }

        return view('admin.peminjaman', [
            'admin' => $this->adminData(),
            'activeMenu' => 'peminjaman',
            'peminjaman' => $peminjaman,
            'selected' => $selected,
        ]);
    }

    public function konfirmasiKembali(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|string',
            'tanggal_kembali_aktual' => 'required|date',
            'kondisi_aset' => 'required|string',
            'catatan_pengembalian' => 'nullable|string|max:500',
        ]);

        return redirect()
            ->route('admin.peminjaman')
            ->with('success', 'Pengembalian ' . $request->peminjaman_id . ' berhasil dikonfirmasi. Status aset diperbarui menjadi Tersedia.');
    }

    public function laporan(Request $request)
    {
         $laporan = [
         [
         'nama'=>'Budi Santoso',
         'aset'=>'Kamera Sony A7',
         'tgl_pinjam'=>'01 Jun',
         'tgl_kembali'=>'08 Jun',
         'durasi'=>'7 hr',
         'status'=>'Tepat waktu'
         ],

         [
         'nama'=>'Rina Komala',
         'aset'=>'Laptop Dell XPS',
         'tgl_pinjam'=>'03 Jun',
         'tgl_kembali'=>'12 Jun',
         'durasi'=>'9 hr',
         'status'=>'Terlambat 2hr'
         ],

         [
         'nama'=>'Doni Prasetyo',
         'aset'=>'Drone DJI Mini',
         'tgl_pinjam'=>'05 Jun',
         'tgl_kembali'=>'20 Jun',
         'durasi'=>'15 hr',
         'status'=>'Terlambat 5hr'
         ],

         [
         'nama'=>'Sinta Dewi',
         'aset'=>'Mikrofon Rode',
         'tgl_pinjam'=>'10 Jun',
         'tgl_kembali'=>'14 Jun',
         'durasi'=>'4 hr',
         'status'=>'Tepat waktu'
         ],

         ];

         return view('admin.laporan', compact('laporan'));
    }
}
