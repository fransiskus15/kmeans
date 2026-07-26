<?php

namespace App\Http\Controllers;

class HrController extends Controller
{
    private function hrData(): array
    {
        return [
            'nama' => 'Siti Rahayu (HR)',
            'inisial' => 'SR',
        ];
    }

    public function dashboard()
    {
        return view('hr.dashboard', [
            'hr' => $this->hrData(),
            'activeMenu' => 'dashboard',
            'stats' => [
                'menunggu_approval' => 5,
                'disetujui_bulan_ini' => 28,
                'ditolak_bulan_ini' => 3,
                'aset_terlambat' => 2,
            ],
            'permintaan' => [
                ['peminjam' => 'Budi S.', 'aset' => 'Kamera Sony', 'cluster' => 'A'],
                ['peminjam' => 'Doni P.', 'aset' => 'Drone DJI', 'cluster' => 'C'],
                ['peminjam' => 'Rina K.', 'aset' => 'Mikrofon', 'cluster' => 'B'],
                ['peminjam' => 'Sinta D.', 'aset' => 'Tripod', 'cluster' => 'A'],
                ['peminjam' => 'Arif H.', 'aset' => 'Laptop Dell', 'cluster' => 'B'],
            ],
            'cluster' => [
                ['label' => 'A Disiplin', 'value' => 13, 'persen' => 41, 'color' => '#2d9f6f'],
                ['label' => 'B Kasual', 'value' => 14, 'persen' => 44, 'color' => '#e6b800'],
                ['label' => 'C Berisiko', 'value' => 5, 'persen' => 16, 'color' => '#e879a0'],
            ],
            'akurasi' => [
                'klasifikasi' => '82%',
                'silhouette' => '68%',
                'terakhir_diperbarui' => '01 Jul 2025',
            ],
        ]);
    }

    public function approval()
    {
        return view('hr.approval', [
            'hr' => $this->hrData(),
            'activeMenu' => 'approval',
            'permintaan' => [
                'peminjam' => 'Budi Santoso',
                'aset' => 'Kamera Sony A7 III',
                'tanggal_pinjam' => '07 Jul 2025',
                'batas_kembali' => '14 Jul 2025 (7 hari)',
                'keperluan' => 'Peliputan pameran UMKM',
            ],
            'profil' => [
                'cluster' => 'A',
                'judul' => 'Cluster A — Aktif & disiplin',
                'subjudul' => 'Peminjam terpercaya',
                'rekomendasi' => 'Rekam jejak baik. Direkomendasikan untuk disetujui.',
            ],
            'fitur' => [
                ['var' => 'F1 Frekuensi', 'nilai' => '14x', 'ket' => 'Tinggi', 'badge' => 'tinggi'],
                ['var' => 'F2 Durasi', 'nilai' => '4.2 hr', 'ket' => 'Wajar', 'badge' => 'wajar'],
                ['var' => 'F3 Terlambat', 'nilai' => '7.1%', 'ket' => 'Rendah', 'badge' => 'rendah'],
                ['var' => 'F4 Variasi', 'nilai' => '4 kat', 'ket' => 'Beragam', 'badge' => 'beragam'],
                ['var' => 'F5 Nilai', 'nilai' => 'Rp48jt', 'ket' => 'Besar', 'badge' => 'besar'],
            ],
            'radar' => [
                'labels' => ['F1', 'F2', 'F3', 'F4', 'F5'],
                'values' => [85, 70, 90, 65, 80],
            ],
        ]);
    }

    public function cluster()
    {
        return view('hr.cluster', [
            'hr' => $this->hrData(),
            'activeMenu' => 'cluster',
            'meta' => [
                'k' => 3,
                'silhouette' => '68%',
                'run_date' => '01 Jul 2025',
            ],
            'summary' => [
                'total_peminjam' => 32,
                'cluster_a' => ['label' => 'Cluster A Disiplin', 'jumlah' => 13, 'persen' => 41],
                'cluster_b' => ['label' => 'Cluster B Kasual', 'jumlah' => 14, 'persen' => 44],
                'cluster_c' => ['label' => 'Cluster C Berisiko', 'jumlah' => 5, 'persen' => 16],
            ],
            'elbow' => [
                ['k' => 2, 'inertia' => 92],
                ['k' => 3, 'inertia' => 58, 'optimal' => true],
                ['k' => 4, 'inertia' => 44],
                ['k' => 5, 'inertia' => 36],
                ['k' => 6, 'inertia' => 31],
                ['k' => 7, 'inertia' => 27],
                ['k' => 8, 'inertia' => 24],
            ],
            'silhouette' => [
                ['k' => 2, 'score' => 54],
                ['k' => 3, 'score' => 68, 'optimal' => true],
                ['k' => 4, 'score' => 61],
                ['k' => 5, 'score' => 55],
                ['k' => 6, 'score' => 49],
            ],
            'indikator' => [
                ['label' => 'Akurasi klasifikasi', 'nilai' => '82%', 'target' => '≥ 75%'],
                ['label' => 'Silhouette Score', 'nilai' => '68%', 'target' => '≥ 50%'],
                ['label' => 'Davies-Bouldin Index', 'nilai' => '0.71', 'target' => '< 1.0'],
                ['label' => 'Peminjam terklasifikasi', 'nilai' => '88%', 'target' => '≥ 80%'],
            ],
            'peminjam' => [
                ['nama' => 'Budi Santoso', 'cluster' => 'A', 'cluster_label' => 'A Disiplin', 'terlambat' => '7.1%'],
                ['nama' => 'Rina Kurnia', 'cluster' => 'B', 'cluster_label' => 'B Kasual', 'terlambat' => '0%'],
                ['nama' => 'Doni Prasetyo', 'cluster' => 'C', 'cluster_label' => 'C Berisiko', 'terlambat' => '44.4%'],
                ['nama' => 'Sinta Dewi', 'cluster' => 'A', 'cluster_label' => 'A Disiplin', 'terlambat' => '9.1%'],
            ],
        ]);
    }
}
