<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Aset;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
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
            ->whereBetween('tgl_pengajuan', [$periodeFrom, $periodeTo]);

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
            $query->whereRaw('tgl_kembali_aktual <= tgl_rencana_kembali')
                  ->whereNotNull('tgl_kembali_aktual');
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

        $allData        = $allQuery->get();
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
    // EKSPOR PDF — menggunakan barryvdh/laravel-dompdf
    // ══════════════════════════════════════════════════════════════════════
    public function exportPdf(Request $request)
    {
        $filter = [
            'periode_from' => $request->get('periode_from', session('laporan_filter.periode_from', Carbon::now()->startOfMonth()->format('Y-m-d'))),
            'periode_to'   => $request->get('periode_to',   session('laporan_filter.periode_to',   Carbon::now()->format('Y-m-d'))),
            'kategori'     => $request->get('kategori',     session('laporan_filter.kategori',     'all')),
            'status'       => $request->get('status',       session('laporan_filter.status',       'all')),
        ];

        $peminjaman = $this->getFilteredData($filter);
        $statistik  = $this->hitungStatistik($peminjaman);

        $pdf = Pdf::loadView('laporan.pdf', [
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
    // EKSPOR EXCEL — CSV streaming (tidak butuh library Excel eksternal)
    // File CSV bisa langsung dibuka Excel dengan format kolom rapi
    // ══════════════════════════════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $filter = [
            'periode_from' => $request->get('periode_from', session('laporan_filter.periode_from', Carbon::now()->startOfMonth()->format('Y-m-d'))),
            'periode_to'   => $request->get('periode_to',   session('laporan_filter.periode_to',   Carbon::now()->format('Y-m-d'))),
            'kategori'     => $request->get('kategori',     session('laporan_filter.kategori',     'all')),
            'status'       => $request->get('status',       session('laporan_filter.status',       'all')),
        ];

        $peminjaman = $this->getFilteredData($filter);
        $statistik  = $this->hitungStatistik($peminjaman);

        $filename = 'laporan_peminjaman_'
                   . $filter['periode_from'] . '_sd_'
                   . $filter['periode_to'] . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($peminjaman, $statistik, $filter) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel Windows bisa baca UTF-8 dengan benar
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ── Info laporan ──────────────────────────────────────────────
            fputcsv($out, ['LAPORAN PEMINJAMAN ASET']);
            fputcsv($out, ['Periode', $filter['periode_from'] . ' s/d ' . $filter['periode_to']]);
            fputcsv($out, ['Kategori', $filter['kategori'] === 'all' ? 'Semua' : $filter['kategori']]);
            fputcsv($out, ['Status', $filter['status'] === 'all' ? 'Semua' : ucfirst(str_replace('_', ' ', $filter['status']))]);
            fputcsv($out, ['Digenerate', Carbon::now()->format('d/m/Y H:i')]);
            fputcsv($out, []);

            // ── Statistik ─────────────────────────────────────────────────
            fputcsv($out, ['RINGKASAN STATISTIK']);
            fputcsv($out, ['Total Transaksi', 'Tepat Waktu', 'Terlambat', 'Tingkat Terlambat']);
            fputcsv($out, [
                $statistik['total'],
                $statistik['tepat_waktu'],
                $statistik['terlambat'],
                $statistik['pct_terlambat'] . '%',
            ]);
            fputcsv($out, []);

            // ── Header tabel ──────────────────────────────────────────────
            fputcsv($out, [
                'No', 'Peminjam', 'Divisi', 'Aset', 'Kategori Aset',
                'Tgl Pengajuan', 'Tgl Pinjam', 'Batas Kembali',
                'Tgl Kembali Aktual', 'Durasi (hari)', 'Status',
            ]);

            // ── Baris data ────────────────────────────────────────────────
            foreach ($peminjaman as $i => $item) {
                $terlambat = $item->tgl_kembali_aktual &&
                    Carbon::parse($item->tgl_kembali_aktual)
                        ->gt(Carbon::parse($item->tgl_rencana_kembali));

                $statusLabel = $terlambat ? 'Terlambat'
                    : ($item->tgl_kembali_aktual ? 'Tepat Waktu' : 'Belum Kembali');

                $durasi = ($item->tgl_pinjam && $item->tgl_kembali_aktual)
                    ? Carbon::parse($item->tgl_pinjam)->diffInDays(Carbon::parse($item->tgl_kembali_aktual))
                    : '-';

                fputcsv($out, [
                    $i + 1,
                    $item->peminjam->nama ?? '-',
                    $item->peminjam->divisi ?? '-',
                    $item->aset->nama_aset ?? '-',
                    $item->aset->kategori ?? '-',
                    $item->tgl_pengajuan ? Carbon::parse($item->tgl_pengajuan)->format('d/m/Y') : '-',
                    $item->tgl_pinjam ? Carbon::parse($item->tgl_pinjam)->format('d/m/Y') : '-',
                    $item->tgl_rencana_kembali ? Carbon::parse($item->tgl_rencana_kembali)->format('d/m/Y') : '-',
                    $item->tgl_kembali_aktual ? Carbon::parse($item->tgl_kembali_aktual)->format('d/m/Y') : '-',
                    $durasi,
                    $statusLabel,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
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
            $query->whereRaw('tgl_kembali_aktual <= tgl_rencana_kembali')
                  ->whereNotNull('tgl_kembali_aktual');
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