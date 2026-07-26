<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'admin' => [
                'nama' => 'Admin Aset',
                'inisial' => 'AA',
            ],
            'activeMenu' => 'dashboard',
            'stats' => [
                'total_aset' => 54,
                'sedang_dipinjam' => 12,
                'menunggu_approval' => 5,
                'melewati_batas' => 2,
            ],
            'peminjaman_aktif' => [
                ['peminjam' => 'Budi Santoso', 'aset' => 'Kamera Sony A7', 'batas_kembali' => '09 Jul 2025', 'status' => 'Aktif'],
                ['peminjam' => 'Rina Kurnia', 'aset' => 'Laptop Dell XPS', 'batas_kembali' => '05 Jul 2025', 'status' => 'Terlambat'],
                ['peminjam' => 'Doni Prasetyo', 'aset' => 'Mikrofon Rode', 'batas_kembali' => '11 Jul 2025', 'status' => 'Aktif'],
            ],
            'cluster' => [
                ['label' => 'A — Disiplin', 'value' => 13, 'color' => '#2d9f6f'],
                ['label' => 'B — Kasual', 'value' => 14, 'color' => '#3b7dd8'],
                ['label' => 'C — Berisiko', 'value' => 5, 'color' => '#c0392b'],
            ],
        ]);
    }
}
