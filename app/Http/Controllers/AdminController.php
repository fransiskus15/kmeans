<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Peminjaman;
use App\Models\ProfilCluster;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD ADMIN ASET (Gambar 3.9)
    // Disesuaikan dengan struktur variabel view admin.dashboard yang sudah ada
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard()
    {
        // ── Kartu statistik ──────────────────────────────────────────────
        $stats = [
            'total_aset'         => Aset::count(),
            'sedang_dipinjam'    => Aset::where('status', 'Dipinjam')->count(),
            'menunggu_approval'  => Peminjaman::where('status', 'Menunggu Persetujuan')->count(),
            'melewati_batas'     => Peminjaman::whereNull('tgl_kembali_aktual')
                                        ->where('tgl_rencana_kembali', '<', now())
                                        ->count(),
        ];

        // ── Tabel peminjaman aktif ───────────────────────────────────────
        $peminjaman_aktif = Peminjaman::with(['peminjam', 'aset'])
            ->whereIn('status', ['Disetujui', 'Dipinjam'])
            ->orderBy('tgl_rencana_kembali')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'peminjam'      => $p->peminjam->nama,
                    'aset'          => $p->aset->nama_aset,
                    'batas_kembali' => Carbon::parse($p->tgl_rencana_kembali)->format('d M Y'),
                    'status'        => $p->isTerlambat() ? 'Terlambat' : 'Aktif',
                ];
            });

        // ── Distribusi cluster untuk Chart.js doughnut ───────────────────
        $warnaCluster = [
            'A' => '#9FE1CB',   // hijau — Aktif & Disiplin
            'B' => '#FAC775',   // kuning — Kasual
            'C' => '#F09595',   // merah — Berisiko
        ];

        $distribusiRaw = ProfilCluster::selectRaw(
                'label_cluster, nama_cluster, COUNT(*) as jumlah', []
            )
            ->groupBy('label_cluster', 'nama_cluster')
            ->orderBy('label_cluster')
            ->get();

        $cluster = $distribusiRaw->map(function ($d) use ($warnaCluster) {
            return [
                'label' => $d->label_cluster . ' — ' . $d->nama_cluster,
                'value' => $d->jumlah,
                'color' => $warnaCluster[$d->label_cluster] ?? '#D1D5DB',
            ];
        })->toArray();

        // Jika belum ada data clustering sama sekali, tampilkan placeholder kosong
        if (empty($cluster)) {
            $cluster = [
                ['label' => 'A — Aktif & Disiplin', 'value' => 0, 'color' => '#9FE1CB'],
                ['label' => 'B — Kasual',            'value' => 0, 'color' => '#FAC775'],
                ['label' => 'C — Berisiko',          'value' => 0, 'color' => '#F09595'],
            ];
        }

        // ── Info Silhouette Score & tanggal run terakhir ─────────────────
        $clusteringTerakhir = DB::table('clustering_log')
            ->orderBy('created_at', 'desc')
            ->first();

        $silhouetteInfo = $clusteringTerakhir
            ? round($clusteringTerakhir->silhouette_score, 2) . ' — Run ' . Carbon::parse($clusteringTerakhir->created_at)->format('d M Y')
            : 'Belum ada data — jalankan clustering terlebih dahulu';

        return view('admin.dashboard', compact(
            'stats',
            'peminjaman_aktif',
            'cluster',
            'silhouetteInfo'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MANAJEMEN DATA ASET (Gambar 3.14) — FR-02
    // ══════════════════════════════════════════════════════════════════════
    public function aset(Request $request)
    {
        $query = Aset::query();

        // Filter pencarian
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_aset', 'like', '%' . $request->cari . '%')
                  ->orWhere('kode_aset', 'like', '%' . $request->cari . '%');
            });
        }

        // Filter kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $asetList = $query->orderBy('nama_aset')->paginate(10);

        // Statistik ringkas
        $statistik = [
            'total'     => Aset::count(),
            'tersedia'  => Aset::where('status', 'Tersedia')->count(),
            'dipinjam'  => Aset::where('status', 'Dipinjam')->count(),
            'perbaikan' => Aset::where('status', 'Perbaikan')->count(),
        ];

        $kategoriList = Aset::distinct()->pluck('kategori');

        return view('admin.aset', compact('asetList', 'statistik', 'kategoriList'));
    }

    // Simpan aset baru
    public function simpanAset(Request $request)
    {
        $validated = $request->validate([
            'kode_aset'       => 'required|string|max:20|unique:aset,kode_aset',
            'nama_aset'       => 'required|string|max:150',
            'kategori'        => 'required|string',
            'nilai_perolehan' => 'required|numeric|min:0',
            'kondisi'         => 'required|in:Baik,Perlu Perawatan,Rusak',
            'deskripsi'       => 'nullable|string',
            'lokasi'          => 'nullable|string|max:100',
        ]);

        $validated['status'] = 'Tersedia';

        Aset::create($validated);

        return redirect()->route('admin.aset')
            ->with('success', 'Aset baru berhasil ditambahkan.');
    }

    // Update aset
    public function updateAset(Request $request, $id)
    {
        $aset = Aset::findOrFail($id);

        $validated = $request->validate([
            'nama_aset'       => 'required|string|max:150',
            'kategori'        => 'required|string',
            'nilai_perolehan' => 'required|numeric|min:0',
            'kondisi'         => 'required|in:Baik,Perlu Perawatan,Rusak',
            'deskripsi'       => 'nullable|string',
            'lokasi'          => 'nullable|string|max:100',
        ]);

        $aset->update($validated);

        return redirect()->route('admin.aset')
            ->with('success', 'Data aset berhasil diperbarui.');
    }

    // Hapus aset
    public function hapusAset($id)
    {
        $aset = Aset::findOrFail($id);

        if ($aset->status === 'Dipinjam') {
            return redirect()->route('admin.aset')
                ->with('error', 'Aset tidak bisa dihapus karena sedang dipinjam.');
        }

        $aset->delete();

        return redirect()->route('admin.aset')
            ->with('success', 'Aset berhasil dihapus.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // KONFIRMASI PENGAMBILAN & PENGEMBALIAN ASET (Gambar 3.18) — FR-06
    // ══════════════════════════════════════════════════════════════════════
    public function peminjaman(Request $request)
    {
        // ── Ambil semua peminjaman aktif + yang sudah dikembalikan (10 terakhir) ──
        $rawList = Peminjaman::with(['peminjam', 'aset', 'peminjam.profilCluster'])
            ->whereIn('status', ['Disetujui', 'Dipinjam', 'Dikembalikan', 'Dikembalikan Terlambat'])
            ->orderBy('tgl_rencana_kembali')
            ->paginate(15);

        // Format ke array yang sesuai blade
        $peminjaman = $rawList->map(function ($p) {
            $batasKembali   = Carbon::parse($p->tgl_rencana_kembali);
            $terlambatHari  = $p->tgl_kembali_aktual
                ? 0  // sudah dikembalikan, tidak terlambat untuk tampilan
                : (Carbon::now()->gt($batasKembali) ? Carbon::now()->diffInDays($batasKembali) : 0);

            return [
                'id'             => $p->id_peminjaman,
                'peminjam'       => $p->peminjam->nama ?? '-',
                'aset'           => $p->aset->nama_aset ?? '-',
                'tanggal_pinjam' => $p->tgl_pinjam
                                        ? Carbon::parse($p->tgl_pinjam)->format('d M Y')
                                        : ($p->tgl_pengajuan ? Carbon::parse($p->tgl_pengajuan)->format('d M Y') : '-'),
                'batas_kembali'  => $batasKembali->format('d M Y'),
                'status'         => in_array($p->status, ['Dikembalikan', 'Dikembalikan Terlambat'])
                                        ? 'Dikembalikan'
                                        : $p->status,
                'terlambat_hari' => $terlambatHari,
            ];
        });

        // ── Jika ada parameter ?pilih=id, tampilkan form konfirmasi ─────
        $selected = null;
        if ($request->filled('pilih')) {
            $pilih = Peminjaman::with(['peminjam', 'aset', 'peminjam.profilCluster'])
                ->find($request->pilih);

            if ($pilih) {
                $batas         = Carbon::parse($pilih->tgl_rencana_kembali);
                $terlambatHari = Carbon::now()->gt($batas) ? Carbon::now()->diffInDays($batas) : 0;

                $selected = [
                    'id'                       => $pilih->id_peminjaman,
                    'peminjam'                 => $pilih->peminjam->nama ?? '-',
                    'divisi'                   => $pilih->peminjam->divisi ?? '-',
                    'aset'                     => $pilih->aset->nama_aset ?? '-',
                    'nilai_aset'               => $pilih->aset->nilai_perolehan ?? 0,
                    'tanggal_pinjam'           => $pilih->tgl_pinjam
                                                    ? Carbon::parse($pilih->tgl_pinjam)->format('d M Y')
                                                    : ($pilih->tgl_pengajuan ? Carbon::parse($pilih->tgl_pengajuan)->format('d M Y') : '-'),
                    'batas_kembali'            => $batas->format('d M Y'),
                    'terlambat_hari'           => $terlambatHari,
                    'cluster'                  => $pilih->peminjam->profilCluster
                                                    ? ($pilih->peminjam->profilCluster->label_cluster . ' — ' . $pilih->peminjam->profilCluster->nama_cluster)
                                                    : 'Belum terklasifikasi',
                    'tanggal_kembali_default'  => now()->format('Y-m-d'),
                ];
            }
        }

        return view('admin.peminjaman', compact('peminjaman', 'selected', 'rawList'));
    }

    // Konfirmasi pengambilan aset (setelah HR approve)
    public function konfirmasiAmbil($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'Disetujui') {
            return back()->with('error', 'Peminjaman ini belum disetujui HR.');
        }

        $peminjaman->update(['status' => 'Dipinjam']);
        $peminjaman->aset->update(['status' => 'Dipinjam']);

        // Kirim notifikasi ke peminjam
        Notifikasi::create([
            'pengguna_id'   => $peminjaman->pengguna_id,
            'peminjaman_id' => $peminjaman->id_peminjaman,
            'judul'         => 'Pengambilan aset dikonfirmasi',
            'pesan'         => "Aset {$peminjaman->aset->nama_aset} telah Anda ambil pada " . now()->format('d M Y') . '.',
            'tipe'          => 'pengambilan',
        ]);

        return back()->with('success', 'Pengambilan aset berhasil dikonfirmasi.');
    }

    // Konfirmasi pengembalian aset
    public function konfirmasiKembali(Request $request)
    {
        $validated = $request->validate([
            'peminjaman_id'         => 'required|exists:peminjaman,id_peminjaman',
            'tanggal_kembali_aktual'=> 'required|date',
            'kondisi_aset'          => 'required|in:Baik,Perlu Perawatan,Rusak',
            'catatan'               => 'nullable|string',
        ]);

        $peminjaman = Peminjaman::with('aset')->findOrFail($validated['peminjaman_id']);

        // Pastikan peminjaman memang sedang aktif (belum dikembalikan)
        if (in_array($peminjaman->status, ['Dikembalikan', 'Dikembalikan Terlambat'])) {
            return redirect()->route('admin.peminjaman')
                ->with('error', 'Peminjaman ini sudah dikonfirmasi sebelumnya.');
        }

        $tglKembali  = Carbon::parse($validated['tanggal_kembali_aktual']);
        $tglRencana  = Carbon::parse($peminjaman->tgl_rencana_kembali);
        $isTerlambat = $tglKembali->gt($tglRencana);

        // Update status peminjaman → Dikembalikan
        $peminjaman->update([
            'tgl_kembali_aktual' => $tglKembali,
            'status'             => $isTerlambat ? 'Dikembalikan Terlambat' : 'Dikembalikan',
            'keterangan'         => $peminjaman->keterangan . ($validated['catatan'] ? ' | Catatan pengembalian: ' . $validated['catatan'] : ''),
        ]);

        // Update status aset → kembali Tersedia (atau Perbaikan jika rusak)
        $peminjaman->aset->update([
            'status'  => $validated['kondisi_aset'] === 'Rusak' ? 'Perbaikan' : 'Tersedia',
            'kondisi' => $validated['kondisi_aset'],
        ]);

        // Notifikasi ke peminjam
        Notifikasi::create([
            'pengguna_id'   => $peminjaman->pengguna_id,
            'peminjaman_id' => $peminjaman->id_peminjaman,
            'judul'         => 'Pengembalian aset dikonfirmasi',
            'pesan'         => "Pengembalian {$peminjaman->aset->nama_aset} telah dikonfirmasi Admin Aset."
                                . ($isTerlambat ? ' Pengembalian tercatat TERLAMBAT.' : ' Pengembalian tepat waktu.'),
            'tipe'          => 'pengembalian',
        ]);

        return redirect()->route('admin.peminjaman')
            ->with('success', 'Pengembalian aset berhasil dikonfirmasi. Status aset kembali menjadi Tersedia.'
                . ($isTerlambat ? ' (Tercatat terlambat)' : ' (Tepat waktu)'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // LAPORAN — arahkan ke LaporanController yang sudah ada
    // ══════════════════════════════════════════════════════════════════════
    public function laporan()
    {
        return redirect()->route('laporan.index');
    }

    // ══════════════════════════════════════════════════════════════════════
    // ANALISIS K-MEANS CLUSTERING — ambil data dari database
    // ══════════════════════════════════════════════════════════════════════
    public function cluster()
    {
        // ── Metadata run terakhir dari clustering_log ────────────────────
        // Kolom nyata: id, k_optimal, silhouette_score, silhouette_persen,
        //              davies_bouldin, akurasi_persen, pct_terklasifikasi,
        //              total_peminjam, distribusi_a, distribusi_b, distribusi_c
        $logTerakhir = DB::table('clustering_log')
            ->orderBy('created_at', 'desc')
            ->first();

        $meta = [
            'k'          => $logTerakhir->k_optimal ?? 3,
            'silhouette' => $logTerakhir
                                ? ($logTerakhir->silhouette_persen ?? round($logTerakhir->silhouette_score * 100)) . '%'
                                : '-',
            'run_date'   => $logTerakhir
                                ? Carbon::parse($logTerakhir->created_at)->format('d M Y')
                                : 'Belum dijalankan',
        ];

        // ── Summary distribusi cluster ────────────────────────────────────
        // Jika ada data di clustering_log, ambil distribusi dari sana
        if ($logTerakhir) {
            $totalLog = $logTerakhir->total_peminjam ?? 0;
            $distA    = $logTerakhir->distribusi_a ?? 0;
            $distB    = $logTerakhir->distribusi_b ?? 0;
            $distC    = $logTerakhir->distribusi_c ?? 0;
        } else {
            // Fallback: hitung langsung dari profil_cluster
            $totalLog = ProfilCluster::count();
            $distA    = ProfilCluster::where('label_cluster', 'A')->count();
            $distB    = ProfilCluster::where('label_cluster', 'B')->count();
            $distC    = ProfilCluster::where('label_cluster', 'C')->count();
        }

        // Ambil nama cluster dari profil_cluster
        $namaCluster = ProfilCluster::selectRaw('label_cluster, MAX(nama_cluster) as nama_cluster')
            ->groupBy('label_cluster')
            ->get()
            ->keyBy('label_cluster');

        $total = $totalLog ?: ProfilCluster::count();

        $summary = [
            'total_peminjam' => $total,
            'cluster_a' => [
                'label'  => optional($namaCluster->get('A'))->nama_cluster ?? 'Cluster A',
                'jumlah' => $distA,
                'persen' => $total > 0 ? round($distA / $total * 100) : 0,
            ],
            'cluster_b' => [
                'label'  => optional($namaCluster->get('B'))->nama_cluster ?? 'Cluster B',
                'jumlah' => $distB,
                'persen' => $total > 0 ? round($distB / $total * 100) : 0,
            ],
            'cluster_c' => [
                'label'  => optional($namaCluster->get('C'))->nama_cluster ?? 'Cluster C',
                'jumlah' => $distC,
                'persen' => $total > 0 ? round($distC / $total * 100) : 0,
            ],
        ];

        // ── Elbow — tabel clustering_log tidak menyimpan inertia per-K,
        //   gunakan data statis sebagai ilustrasi
        $elbow = [
            ['k' => 2, 'inertia' => 92, 'optimal' => false],
            ['k' => 3, 'inertia' => 58, 'optimal' => ($meta['k'] == 3)],
            ['k' => 4, 'inertia' => 44, 'optimal' => ($meta['k'] == 4)],
            ['k' => 5, 'inertia' => 36, 'optimal' => ($meta['k'] == 5)],
            ['k' => 6, 'inertia' => 31, 'optimal' => false],
            ['k' => 7, 'inertia' => 27, 'optimal' => false],
        ];

        // ── Silhouette score per-K — ilustrasi, highlight K optimal ──────
        $silhouetteScore = $logTerakhir
            ? ($logTerakhir->silhouette_persen ?? round($logTerakhir->silhouette_score * 100))
            : 68;

        $silhouette = [
            ['k' => 2, 'score' => 54, 'optimal' => false],
            ['k' => 3, 'score' => $meta['k'] == 3 ? $silhouetteScore : 68, 'optimal' => ($meta['k'] == 3)],
            ['k' => 4, 'score' => $meta['k'] == 4 ? $silhouetteScore : 61, 'optimal' => ($meta['k'] == 4)],
            ['k' => 5, 'score' => $meta['k'] == 5 ? $silhouetteScore : 55, 'optimal' => ($meta['k'] == 5)],
            ['k' => 6, 'score' => 49, 'optimal' => false],
        ];

        // ── Indikator akurasi dari clustering_log ────────────────────────
        $indikator = [
            [
                'label'  => 'Silhouette Score',
                'nilai'  => $logTerakhir ? $silhouetteScore . '%' : '-',
                'target' => '≥ 50%',
            ],
            [
                'label'  => 'Davies-Bouldin Index',
                'nilai'  => $logTerakhir ? $logTerakhir->davies_bouldin : '-',
                'target' => '< 1.0',
            ],
            [
                'label'  => 'Akurasi klasifikasi',
                'nilai'  => $logTerakhir && $logTerakhir->akurasi_persen
                                ? $logTerakhir->akurasi_persen . '%'
                                : '-',
                'target' => '≥ 75%',
            ],
            [
                'label'  => 'Peminjam terklasifikasi',
                'nilai'  => $logTerakhir ? $logTerakhir->pct_terklasifikasi . '%' : '-',
                'target' => '≥ 80%',
            ],
        ];

        // ── Daftar peminjam dengan label cluster dari DB ─────────────────
        $peminjamRaw = ProfilCluster::with('pengguna')
            ->orderBy('label_cluster')
            ->paginate(10);

        $peminjam = $peminjamRaw->map(fn($p) => [
            'id'            => $p->pengguna_id,
            'nama'          => $p->pengguna->nama ?? '-',
            'cluster'       => $p->label_cluster,
            'cluster_label' => $p->label_cluster . ' — ' . $p->nama_cluster,
            'terlambat'     => round($p->nilai_f3 ?? 0, 1) . '%',
        ]);

        return view('admin.cluster', compact(
            'meta', 'summary', 'elbow', 'silhouette', 'indikator', 'peminjam', 'peminjamRaw'
        ));
    }  

    // ══════════════════════════════════════════════════════════════════════
    // JALANKAN CLUSTERING — dipanggil tombol "Jalankan Cluster"
    // Menghitung F1-F5 dari peminjaman, kirim ke Flask, simpan hasil
    // ══════════════════════════════════════════════════════════════════════
    public function jalankanCluster(Request $request)
    {
        try {
            // ── 1. Hitung F1-F5 dari data transaksi peminjaman ──────────
            $dataFitur = $this->hitungFiturF1F5();
 
            if (count($dataFitur) < self::MIN_PEMINJAM) {
                $pesan = 'Data belum cukup untuk clustering. Ditemukan '
                        . count($dataFitur) . ' peminjam dengan riwayat memadai, '
                        . 'minimal ' . self::MIN_PEMINJAM . ' peminjam diperlukan '
                        . '(masing-masing minimal ' . self::MIN_TRANSAKSI . ' transaksi selesai).';
 
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $pesan], 422);
                }
                return back()->with('error', $pesan);
            }
 
            // ── 2. Kirim ke Python Flask API ──────────────────────────────
            $response = Http::timeout(60)
                ->post(self::PYTHON_API . '/clustering', ['data' => $dataFitur]);
 
            if (!$response->successful()) {
                $pesan = 'Python API tidak merespons. Pastikan Flask berjalan (python app.py) di port 5000.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $pesan], 502);
                }
                return back()->with('error', $pesan);
            }
 
            $hasil = $response->json();
 
            if (!($hasil['success'] ?? false)) {
                $pesan = $hasil['message'] ?? 'Clustering gagal dijalankan.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $pesan], 422);
                }
                return back()->with('error', $pesan);
            }
 
            // ── 3. Simpan hasil ke tabel profil_cluster ───────────────────
            DB::beginTransaction();
 
            foreach ($hasil['hasil'] as $item) {
                ProfilCluster::updateOrCreate(
                    ['pengguna_id' => $item['user_id']],
                    [
                        'label_cluster'    => $item['label'],
                        'nama_cluster'     => $item['nama_cluster'],
                        'nilai_f1'         => $item['nilai_f1'],
                        'nilai_f2'         => $item['nilai_f2'],
                        'nilai_f3'         => $item['nilai_f3'],
                        'nilai_f4'         => $item['nilai_f4'],
                        'nilai_f5'         => $item['nilai_f5'],
                        'silhouette_score' => $hasil['silhouette_score'] ?? null,
                        'tgl_diperbarui'   => Carbon::now(),
                    ]
                );
            }
 
            // ── 4. Simpan log evaluasi clustering ──────────────────────────
            DB::table('clustering_log')->insert([
                'k_optimal'          => $hasil['k_optimal'] ?? 3,
                'silhouette_score'   => $hasil['silhouette_score'] ?? 0,
                'silhouette_persen'  => $hasil['silhouette_persen'] ?? 0,
                'davies_bouldin'     => $hasil['davies_bouldin'] ?? 0,
                'akurasi_persen'     => $hasil['akurasi_persen'] ?? null,
                'pct_terklasifikasi' => $hasil['pct_terklasifikasi'] ?? 0,
                'total_peminjam'     => $hasil['total_peminjam'] ?? count($dataFitur),
                'distribusi_a'       => $hasil['distribusi']['A'] ?? 0,
                'distribusi_b'       => $hasil['distribusi']['B'] ?? 0,
                'distribusi_c'       => $hasil['distribusi']['C'] ?? 0,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]);
 
            DB::commit();
 
            $pesanSukses = 'Clustering berhasil dijalankan untuk ' . ($hasil['total_peminjam'] ?? count($dataFitur))
                            . ' peminjam. Silhouette Score: ' . ($hasil['silhouette_persen'] ?? '-') . '%.';
 
            if ($request->wantsJson()) {
                return response()->json([
                    'success'           => true,
                    'message'           => $pesanSukses,
                    'total_peminjam'    => $hasil['total_peminjam'] ?? count($dataFitur),
                    'silhouette_persen' => $hasil['silhouette_persen'] ?? null,
                    'akurasi_persen'    => $hasil['akurasi_persen'] ?? null,
                    'distribusi'        => $hasil['distribusi'] ?? null,
                ]);
            }
 
            return redirect()->route('admin.cluster')->with('success', $pesanSukses);
 
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $pesan = 'Tidak dapat terhubung ke Python API. Jalankan dulu: cd ml-python && python app.py';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $pesan], 503);
            }
            return back()->with('error', $pesan);
 
        } catch (\Exception $e) {
            DB::rollBack();
            $pesan = 'Terjadi kesalahan: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $pesan], 500);
            }
            return back()->with('error', $pesan);
        }
    }
 
    // ══════════════════════════════════════════════════════════════════════
    // HELPER: Hitung F1–F5 dari tabel peminjaman (minimal 3 transaksi/user)
    // ══════════════════════════════════════════════════════════════════════
    private function hitungFiturF1F5(): array
    {
        $peminjamanData = DB::table('peminjaman as p')
            ->join('pengguna as u', 'u.id_pengguna', '=', 'p.pengguna_id')
            ->join('aset as a', 'a.id_aset', '=', 'p.aset_id')
            ->select(
                'p.pengguna_id as user_id',
                'u.nama',
                'p.tgl_pinjam',
                'p.tgl_kembali_aktual',
                'p.tgl_rencana_kembali',
                'a.kategori',
                'a.nilai_perolehan'
            )
            ->whereNotNull('p.tgl_kembali_aktual')
            ->orderBy('p.pengguna_id')
            ->get();
 
        $grouped   = $peminjamanData->groupBy('user_id');
        $dataFitur = [];
 
        foreach ($grouped as $userId => $transaksi) {
            $n = $transaksi->count();
            if ($n < self::MIN_TRANSAKSI) continue;
 
            // F1 — Frekuensi peminjaman
            $f1 = $n;
 
            // F2 — Rata-rata durasi (hari)
            $totalDurasi = $transaksi->sum(function ($t) {
                return Carbon::parse($t->tgl_pinjam)
                    ->diffInDays(Carbon::parse($t->tgl_kembali_aktual));
            });
            $f2 = round($totalDurasi / $n, 1);
 
            // F3 — Tingkat keterlambatan (%)
            $terlambat = $transaksi->filter(function ($t) {
                return Carbon::parse($t->tgl_kembali_aktual)
                    ->gt(Carbon::parse($t->tgl_rencana_kembali));
            })->count();
            $f3 = round($terlambat / $n * 100, 1);
 
            // F4 — Variasi jenis aset
            $f4 = $transaksi->pluck('kategori')->unique()->count();
 
            // F5 — Total nilai aset dipinjam (Rp)
            $f5 = (float) $transaksi->sum('nilai_perolehan');
 
            $dataFitur[] = [
                'user_id' => $userId,
                'nama'    => $transaksi->first()->nama,
                'f1'      => $f1,
                'f2'      => $f2,
                'f3'      => $f3,
                'f4'      => $f4,
                'f5'      => $f5,
            ];
        }
 
        return $dataFitur;
    }
}
