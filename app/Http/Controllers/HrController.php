<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Aset;
use App\Models\Approval;
use App\Models\ProfilCluster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD HR — data dari database
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard()
    {
        $now = Carbon::now();

        // ── Statistik ─────────────────────────────────────────────────────
        $stats = [
            'menunggu_approval'  => Peminjaman::where('status', 'Menunggu Persetujuan')->count(),
            'disetujui_bulan_ini'=> Peminjaman::where('status', 'Disetujui')
                                        ->whereMonth('updated_at', $now->month)
                                        ->whereYear('updated_at', $now->year)
                                        ->count(),
            'ditolak_bulan_ini'  => Peminjaman::where('status', 'Ditolak')
                                        ->whereMonth('updated_at', $now->month)
                                        ->whereYear('updated_at', $now->year)
                                        ->count(),
            'aset_terlambat'     => Peminjaman::whereNull('tgl_kembali_aktual')
                                        ->where('tgl_rencana_kembali', '<', $now)
                                        ->whereIn('status', ['Disetujui', 'Dipinjam'])
                                        ->count(),
        ];

        // ── Permintaan menunggu (untuk tabel dashboard) ───────────────────
        $permintaanRaw = Peminjaman::with(['peminjam', 'aset', 'peminjam.profilCluster'])
            ->where('status', 'Menunggu Persetujuan')
            ->orderBy('tgl_pengajuan', 'asc')
            ->take(10)
            ->get();

        $permintaan = $permintaanRaw->map(fn($p) => [
            'id'       => $p->id_peminjaman,
            'peminjam' => $p->peminjam->nama ?? '-',
            'aset'     => $p->aset->nama_aset ?? '-',
            'cluster'  => $p->peminjam->profilCluster->label_cluster ?? '?',
            'tgl_ajuan'=> Carbon::parse($p->tgl_pengajuan)->format('d M Y'),
        ])->toArray();

        // ── Distribusi cluster (dari profil_cluster) ─────────────────────
        $logTerakhir = DB::table('clustering_log')->orderBy('created_at', 'desc')->first();

        $clusterRaw = ProfilCluster::selectRaw('label_cluster, nama_cluster, COUNT(*) as jumlah')
            ->groupBy('label_cluster', 'nama_cluster')
            ->orderBy('label_cluster')
            ->get();

        $total = $clusterRaw->sum('jumlah') ?: 1;

        $clusterColors = ['A' => '#2d9f6f', 'B' => '#e6b800', 'C' => '#e879a0'];

        $cluster = $clusterRaw->map(fn($c) => [
            'label'  => $c->label_cluster . ' — ' . $c->nama_cluster,
            'value'  => $c->jumlah,
            'persen' => round($c->jumlah / $total * 100),
            'color'  => $clusterColors[$c->label_cluster] ?? '#aaaaaa',
        ])->toArray();

        if (empty($cluster)) {
            $cluster = [
                ['label' => 'A — Disiplin',  'value' => 0, 'persen' => 0, 'color' => '#2d9f6f'],
                ['label' => 'B — Kasual',    'value' => 0, 'persen' => 0, 'color' => '#e6b800'],
                ['label' => 'C — Berisiko',  'value' => 0, 'persen' => 0, 'color' => '#e879a0'],
            ];
        }

        $akurasi = [
            'klasifikasi'        => $logTerakhir ? ($logTerakhir->akurasi_persen ?? '-') . '%' : '-',
            'silhouette'         => $logTerakhir
                                        ? ($logTerakhir->silhouette_persen ?? round($logTerakhir->silhouette_score * 100)) . '%'
                                        : '-',
            'terakhir_diperbarui'=> $logTerakhir
                                        ? Carbon::parse($logTerakhir->created_at)->format('d M Y')
                                        : 'Belum dijalankan',
        ];

        $hr = $this->hrData();

        return view('hr.dashboard', compact(
            'hr', 'stats', 'permintaan', 'cluster', 'akurasi'
        ) + ['activeMenu' => 'dashboard']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // HALAMAN APPROVAL — detail satu peminjaman + profil cluster peminjam
    // ══════════════════════════════════════════════════════════════════════
    public function approval(Request $request)
    {
        // Jika tidak ada ?id= , tampilkan daftar semua yang menunggu
        if (!$request->filled('id')) {
            return $this->daftarApproval($request);
        }

        $p = Peminjaman::with(['peminjam', 'aset', 'peminjam.profilCluster'])
            ->findOrFail($request->id);

        // ── Detail permintaan ──────────────────────────────────────────
        $permintaan = [
            'id'             => $p->id_peminjaman,
            'peminjam'       => $p->peminjam->nama ?? '-',
            'divisi'         => $p->peminjam->divisi ?? '-',
            'aset'           => $p->aset->nama_aset ?? '-',
            'kategori_aset'  => $p->aset->kategori ?? '-',
            'tanggal_pinjam' => Carbon::parse($p->tgl_pinjam)->format('d M Y'),
            'batas_kembali'  => Carbon::parse($p->tgl_rencana_kembali)->format('d M Y') .
                                ' (' . Carbon::parse($p->tgl_pinjam)->diffInDays($p->tgl_rencana_kembali) . ' hari)',
            'keperluan'      => $p->keterangan ?? '-',
            'status'         => $p->status,
            'tgl_ajuan'      => Carbon::parse($p->tgl_pengajuan)->format('d M Y'),
        ];

        // ── Profil cluster peminjam ────────────────────────────────────
        $pc = $p->peminjam->profilCluster;

        $clusterLabel = $pc ? $pc->label_cluster : '?';
        $clusterNama  = $pc ? $pc->nama_cluster  : 'Belum terklasifikasi';

        $rekomendasi = match ($clusterLabel) {
            'A' => 'Rekam jejak sangat baik. Sangat direkomendasikan untuk disetujui.',
            'B' => 'Rekam jejak cukup baik. Dapat dipertimbangkan untuk disetujui.',
            'C' => 'Rekam jejak kurang baik. Pertimbangkan dengan hati-hati sebelum menyetujui.',
            default => 'Data cluster belum tersedia untuk peminjam ini.',
        };

        $profil = [
            'cluster'     => $clusterLabel,
            'judul'       => 'Cluster ' . $clusterLabel . ' — ' . $clusterNama,
            'subjudul'    => $pc ? 'Data dari hasil K-Means clustering' : 'Belum pernah terklasifikasi',
            'rekomendasi' => $rekomendasi,
        ];

        // ── Fitur F1-F5 dari profil_cluster ───────────────────────────
        if ($pc) {
            $fitur = [
                [
                    'var'   => 'F1 Frekuensi',
                    'nilai' => ($pc->nilai_f1 ?? 0) . 'x',
                    'ket'   => $this->ketFrekuensi($pc->nilai_f1 ?? 0),
                    'badge' => $this->badgeFrekuensi($pc->nilai_f1 ?? 0),
                ],
                [
                    'var'   => 'F2 Durasi rata-rata',
                    'nilai' => round($pc->nilai_f2 ?? 0, 1) . ' hr',
                    'ket'   => 'Rata-rata',
                    'badge' => 'wajar',
                ],
                [
                    'var'   => 'F3 Keterlambatan',
                    'nilai' => round($pc->nilai_f3 ?? 0, 1) . '%',
                    'ket'   => ($pc->nilai_f3 ?? 0) < 20 ? 'Rendah' : (($pc->nilai_f3 ?? 0) < 50 ? 'Sedang' : 'Tinggi'),
                    'badge' => ($pc->nilai_f3 ?? 0) < 20 ? 'rendah' : (($pc->nilai_f3 ?? 0) < 50 ? 'wajar' : 'tinggi'),
                ],
                [
                    'var'   => 'F4 Variasi kategori',
                    'nilai' => ($pc->nilai_f4 ?? 0) . ' kat',
                    'ket'   => 'Beragam',
                    'badge' => 'beragam',
                ],
                [
                    'var'   => 'F5 Nilai aset',
                    'nilai' => 'Rp' . $this->formatJuta($pc->nilai_f5 ?? 0),
                    'ket'   => ($pc->nilai_f5 ?? 0) > 10000000 ? 'Besar' : 'Sedang',
                    'badge' => ($pc->nilai_f5 ?? 0) > 10000000 ? 'besar' : 'wajar',
                ],
            ];

            // Radar chart values (normalisasi ke 0-100)
            $radar = [
                'labels' => ['F1', 'F2', 'F3', 'F4', 'F5'],
                'values' => [
                    min(100, ($pc->nilai_f1 ?? 0) * 7),
                    min(100, ($pc->nilai_f2 ?? 0) * 10),
                    max(0, 100 - ($pc->nilai_f3 ?? 0)),
                    min(100, ($pc->nilai_f4 ?? 0) * 20),
                    min(100, ($pc->nilai_f5 ?? 0) / 500000),
                ],
            ];
        } else {
            $fitur = [
                ['var' => 'F1 Frekuensi',        'nilai' => '-', 'ket' => '-', 'badge' => 'wajar'],
                ['var' => 'F2 Durasi rata-rata',  'nilai' => '-', 'ket' => '-', 'badge' => 'wajar'],
                ['var' => 'F3 Keterlambatan',     'nilai' => '-', 'ket' => '-', 'badge' => 'wajar'],
                ['var' => 'F4 Variasi kategori',  'nilai' => '-', 'ket' => '-', 'badge' => 'wajar'],
                ['var' => 'F5 Nilai aset',        'nilai' => '-', 'ket' => '-', 'badge' => 'wajar'],
            ];
            $radar = ['labels' => ['F1','F2','F3','F4','F5'], 'values' => [0,0,0,0,0]];
        }

        $hr = $this->hrData();

        return view('hr.approval', compact(
            'hr', 'permintaan', 'profil', 'fitur', 'radar'
        ) + ['activeMenu' => 'approval']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // DAFTAR APPROVAL — semua peminjaman menunggu (halaman approval tanpa ?id)
    // ══════════════════════════════════════════════════════════════════════
    private function daftarApproval(Request $request)
    {
        $peminjaman = Peminjaman::with(['peminjam', 'aset', 'peminjam.profilCluster'])
            ->where('status', 'Menunggu Persetujuan')
            ->orderBy('tgl_pengajuan', 'asc')
            ->paginate(15);

        $hr = $this->hrData();

        return view('hr.approval', compact('hr', 'peminjaman')
            + ['permintaan' => null, 'profil' => null, 'fitur' => [], 'radar' => null, 'activeMenu' => 'approval']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // PROSES APPROVAL — setujui atau tolak peminjaman
    // ══════════════════════════════════════════════════════════════════════
    public function prosesApproval(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|integer|exists:peminjaman,id_peminjaman',
            'keputusan'     => 'required|in:setujui,tolak',
            'catatan'       => 'nullable|string|max:500',
        ]);

        $peminjaman = Peminjaman::with(['aset', 'peminjam.profilCluster'])
            ->findOrFail($request->peminjaman_id);

        if ($peminjaman->status !== 'Menunggu Persetujuan') {
            return redirect()->route('hr.approval')
                ->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
        }

        $hrUserId = Auth::id();
        $clusterSaatIni = $peminjaman->peminjam?->profilCluster?->label_cluster ?? null;

        if ($request->keputusan === 'setujui') {
            // Update status peminjaman
            $peminjaman->update([
                'status'     => 'Disetujui',
                'keterangan' => $peminjaman->keterangan .
                                ($request->filled('catatan') ? ' [HR: ' . $request->catatan . ']' : ''),
            ]);

            // Ubah status aset menjadi Dipinjam (tidak tersedia)
            if ($peminjaman->aset) {
                $peminjaman->aset->update(['status' => 'Dipinjam']);
            }

            $keputusanLabel = 'Disetujui';
            $message        = 'Peminjaman berhasil disetujui. Status aset telah diubah menjadi Dipinjam.';
        } else {
            // Update status peminjaman
            $peminjaman->update([
                'status'     => 'Ditolak',
                'keterangan' => $peminjaman->keterangan .
                                ' [Ditolak HR: ' . ($request->catatan ?: 'Tidak ada catatan') . ']',
            ]);

            $keputusanLabel = 'Ditolak';
            $message        = 'Peminjaman telah ditolak.';
        }

        // Simpan data keputusan ke tabel approval
        Approval::updateOrCreate(
            ['peminjaman_id' => $peminjaman->id_peminjaman],
            [
                'pengguna_id'          => $hrUserId,
                'keputusan'            => $keputusanLabel,
                'catatan'              => $request->catatan ?? null,
                'tgl_approval'         => now(),
                'cluster_saat_approval'=> $clusterSaatIni,
            ]
        );

        return redirect()->route('hr.approval')
            ->with('success', $message);
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER — data user HR dari auth
    // ══════════════════════════════════════════════════════════════════════
    private function hrData(): array
    {
        $user = auth()->user();
        if ($user) {
            return [
                'nama'    => $user->nama,
                'inisial' => strtoupper(substr($user->nama, 0, 2)),
            ];
        }
        return ['nama' => 'HR', 'inisial' => 'HR'];
    }

    // ── Helpers label F1 ──────────────────────────────────────────────────
    private function ketFrekuensi($v): string
    {
        if ($v >= 10) return 'Tinggi';
        if ($v >= 5)  return 'Sedang';
        return 'Rendah';
    }

    private function badgeFrekuensi($v): string
    {
        if ($v >= 10) return 'tinggi';
        if ($v >= 5)  return 'wajar';
        return 'rendah';
    }

    private function formatJuta($nilai): string
    {
        if ($nilai >= 1_000_000) return round($nilai / 1_000_000, 1) . 'jt';
        if ($nilai >= 1_000)    return round($nilai / 1_000) . 'rb';
        return (string)$nilai;
    }
}