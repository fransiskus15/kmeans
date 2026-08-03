<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Aset;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPeminjamanExport;

class LaporanController extends Controller
{
    /**
     * CATATAN: Middleware 'auth' dan 'role:admin_aset' TIDAK didefinisikan
     * di sini karena Laravel 11+ menghapus method middleware() dari
     * base Controller. Middleware didefinisikan di routes/web.php, contoh:
     *
     *   Route::middleware(['auth', 'role:admin_aset'])->group(function () {
     *       Route::get('/laporan', [LaporanController::class, 'index']);
     *   });
     */

    // ══════════════════════════════════════════════════════════════════════
    // INDEX — Tampilkan halaman laporan dengan filter
    // ══════════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        // ── Ambil parameter filter dari request ──────────────────────────
        $periodeFrom  = $request->get('periode_from',
                            Carbon::now()->startOfMonth()->format('Y-m-d'));
        $periodeTo    = $request->get('periode_to',
                            Carbon::now()->format('Y-m-d'));
        $kategori     = $request->get('kategori', 'all');
        $status       = $request->get('status',   'all');

        // ── Query utama peminjaman dengan eager loading ──────────────────
        $query = Peminjaman::with(['peminjam', 'aset', 'approval'])
            ->whereBetween('tgl_pengajuan', [$periodeFrom, $periodeTo])
            ->whereNotNull('tgl_kembali_aktual'); // hanya yang sudah selesai

        // Filter kategori aset
        if ($kategori !== 'all') {
            $query->whereHas('aset', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        // Filter status
        if ($status === 'terlambat') {
            $query->whereRaw('tgl_kembali_aktual > tgl_rencana_kembali');
        } elseif ($status === 'tepat_waktu') {
            $query->whereRaw('tgl_kembali_aktual <= tgl_rencana_kembali');
        } elseif ($status === 'menunggu') {
            $query->whereNull('tgl_kembali_aktual');
        }

        $peminjaman = $query->orderBy('tgl_pengajuan', 'desc')->paginate(10);

        // ── Hitung statistik ringkasan ───────────────────────────────────
        $allQuery = Peminjaman::with('aset')
            ->whereBetween('tgl_pengajuan', [$periodeFrom, $periodeTo]);

        if ($kategori !== 'all') {
            $allQuery->whereHas('aset', fn($q) => $q->where('kategori', $kategori));
        }

        $allData       = $allQuery->get();
        $totalTransaksi = $allData->count();

        $terlambat = $allData->filter(function ($p) {
            return $p->tgl_kembali_aktual &&
                   Carbon::parse($p->tgl_kembali_aktual)
                       ->gt(Carbon::parse($p->tgl_rencana_kembali));
        });

        $tepat = $allData->filter(function ($p) {
            return $p->tgl_kembali_aktual &&
                   !Carbon::parse($p->tgl_kembali_aktual)
                       ->gt(Carbon::parse($p->tgl_rencana_kembali));
        });

        $statistik = [
            'total'           => $totalTransaksi,
            'tepat_waktu'     => $tepat->count(),
            'terlambat'       => $terlambat->count(),
            'pct_terlambat'   => $totalTransaksi > 0
                                    ? round($terlambat->count() / $totalTransaksi * 100)
                                    : 0,
        ];

        // ── Daftar kategori untuk dropdown filter ────────────────────────
        $kategoriList = Aset::distinct()->pluck('kategori')->sort()->values();

        // ── Simpan filter ke session untuk ekspor ────────────────────────
        session([
            'laporan_filter' => [
                'periode_from' => $periodeFrom,
                'periode_to'   => $periodeTo,
                'kategori'     => $kategori,
                'status'       => $status,
            ]
        ]);

        return view('admin.laporan', compact(
            'peminjaman', 'statistik', 'kategoriList',
            'periodeFrom', 'periodeTo', 'kategori', 'status'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // EKSPOR PDF
    // ══════════════════════════════════════════════════════════════════════
    public function exportPdf(Request $request)
    {
        $filter = session('laporan_filter', [
            'periode_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'periode_to'   => Carbon::now()->format('Y-m-d'),
            'kategori'     => 'all',
            'status'       => 'all',
        ]);

        $peminjaman = $this->getFilteredData($filter);

        $statistik = $this->hitungStatistik($peminjaman);

        $pdf = PDF::loadView('laporan.pdf', [
            'peminjaman' => $peminjaman,
            'statistik'  => $statistik,
            'filter'     => $filter,
            'generated'  => Carbon::now()->isoFormat('D MMMM YYYY HH:mm'),
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_peminjaman_'
                   . $filter['periode_from'] . '_sd_'
                   . $filter['periode_to'] . '.pdf';

        return $pdf->download($filename);
    }

    // ══════════════════════════════════════════════════════════════════════
    // EKSPOR EXCEL
    // ══════════════════════════════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $filter = session('laporan_filter', [
            'periode_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'periode_to'   => Carbon::now()->format('Y-m-d'),
            'kategori'     => 'all',
            'status'       => 'all',
        ]);

        $filename = 'laporan_peminjaman_'
                   . $filter['periode_from'] . '_sd_'
                   . $filter['periode_to'] . '.xlsx';

        return Excel::download(new LaporanPeminjamanExport($filter), $filename);
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER PRIVATE: ambil data sesuai filter
    // ══════════════════════════════════════════════════════════════════════
    private function getFilteredData(array $filter)
    {
        $query = Peminjaman::with(['peminjam', 'aset', 'approval'])
            ->whereBetween('tgl_pengajuan', [
                $filter['periode_from'],
                $filter['periode_to'],
            ]);

        if ($filter['kategori'] !== 'all') {
            $query->whereHas('aset', fn($q) =>
                $q->where('kategori', $filter['kategori'])
            );
        }

        if ($filter['status'] === 'terlambat') {
            $query->whereRaw('tgl_kembali_aktual > tgl_rencana_kembali');
        } elseif ($filter['status'] === 'tepat_waktu') {
            $query->whereRaw('tgl_kembali_aktual <= tgl_rencana_kembali');
        }

        return $query->orderBy('tgl_pengajuan', 'desc')->get();
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER PRIVATE: hitung statistik dari collection
    // ══════════════════════════════════════════════════════════════════════
    private function hitungStatistik($peminjaman): array
    {
        $total     = $peminjaman->count();
        $terlambat = $peminjaman->filter(fn($p) =>
            $p->tgl_kembali_aktual &&
            Carbon::parse($p->tgl_kembali_aktual)
                ->gt(Carbon::parse($p->tgl_rencana_kembali))
        )->count();

        return [
            'total'         => $total,
            'tepat_waktu'   => $total - $terlambat,
            'terlambat'     => $terlambat,
            'pct_terlambat' => $total > 0
                                ? round($terlambat / $total * 100)
                                : 0,
        ];
    }
}