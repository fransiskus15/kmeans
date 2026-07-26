<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Peminjaman;
use App\Models\ProfilCluster;
use App\Models\Pengguna;
use Carbon\Carbon;

class ClusteringController extends Controller
{
    // URL Flask API Python
    const PYTHON_API = 'http://localhost:5000';

    // CATATAN: Middleware 'auth' dan 'role:admin_aset' TIDAK didefinisikan
    // di sini karena Laravel 11+ menghapus method middleware() dari
    // base Controller. Middleware didefinisikan di routes/web.php
    // atau routes/api.php, contoh:
    //
    //   Route::middleware(['auth', 'role:admin_aset'])->group(function () {
    //       Route::get('/clustering', [ClusteringController::class, 'index']);
    //   });

    // ══════════════════════════════════════════════════════════════════════
    // INDEX — Halaman analisis cluster
    // ══════════════════════════════════════════════════════════════════════
    public function index()
    {
        // Cek status API Python
        $apiStatus = $this->cekStatusApi();

        // Ambil hasil clustering terakhir dari database
        $hasilCluster = ProfilCluster::with('pengguna')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy('label_cluster');

        // Statistik distribusi
        $distribusi = ProfilCluster::selectRaw(
                'label_cluster,
                 nama_cluster,
                 COUNT(*) as jumlah,
                 ROUND(AVG(nilai_f1), 2) as rata_f1,
                 ROUND(AVG(nilai_f2), 2) as rata_f2,
                 ROUND(AVG(nilai_f3), 2) as rata_f3,
                 ROUND(AVG(nilai_f4), 2) as rata_f4,
                 ROUND(AVG(nilai_f5), 0) as rata_f5',
                []
            )
            ->groupBy('label_cluster', 'nama_cluster')
            ->orderBy('label_cluster')
            ->get();

        // Metrik evaluasi terakhir
        $metrik = DB::table('clustering_log')
            ->orderBy('created_at', 'desc')
            ->first();

        // Data elbow untuk grafik
        $elbowData = $metrik ? json_decode($metrik->elbow_data, true) : null;

        // Total peminjam aktif
        $totalPeminjam = Pengguna::where('role', 'karyawan')->count();
        $terklasifikasi = ProfilCluster::count();

        return view('clustering.index', compact(
            'apiStatus', 'hasilCluster', 'distribusi',
            'metrik', 'elbowData', 'totalPeminjam', 'terklasifikasi'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // JALANKAN CLUSTERING
    // ══════════════════════════════════════════════════════════════════════
    public function jalankan(Request $request)
    {
        try {
            // ── 1. Hitung F1–F5 dari data transaksi peminjaman ──────────
            $dataFitur = $this->hitungFiturF1F5();

            if (count($dataFitur) < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data belum cukup. Minimal 5 peminjam dengan ≥ 3 transaksi.',
                    'jumlah'  => count($dataFitur),
                ], 422);
            }

            // ── 2. Kirim ke Python Flask API ─────────────────────────────
            $response = Http::timeout(60)
                ->post(self::PYTHON_API . '/clustering', [
                    'data' => $dataFitur,
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Python API tidak merespons. Pastikan Flask berjalan di port 5000.',
                ], 502);
            }

            $hasil = $response->json();

            if (!$hasil['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $hasil['message'] ?? 'Clustering gagal.',
                ], 422);
            }

            // ── 3. Simpan hasil ke tabel profil_cluster ──────────────────
            DB::beginTransaction();

            foreach ($hasil['hasil'] as $item) {
                ProfilCluster::updateOrCreate(
                    ['pengguna_id' => $item['user_id']],
                    [
                        'label_cluster'  => $item['label'],
                        'nama_cluster'   => $item['nama_cluster'],
                        'nilai_f1'       => $item['nilai_f1'],
                        'nilai_f2'       => $item['nilai_f2'],
                        'nilai_f3'       => $item['nilai_f3'],
                        'nilai_f4'       => $item['nilai_f4'],
                        'nilai_f5'       => $item['nilai_f5'],
                        'tgl_diperbarui' => Carbon::now(),
                    ]
                );
            }

            // ── 4. Simpan log metrik evaluasi ─────────────────────────────
            DB::table('clustering_log')->insert([
                'k_optimal'           => $hasil['k_optimal'],
                'silhouette_score'    => $hasil['silhouette_score'],
                'silhouette_persen'   => $hasil['silhouette_persen'],
                'davies_bouldin'      => $hasil['davies_bouldin'],
                'akurasi_persen'      => $hasil['akurasi_persen'],
                'pct_terklasifikasi'  => $hasil['pct_terklasifikasi'],
                'total_peminjam'      => $hasil['total_peminjam'],
                'distribusi_a'        => $hasil['distribusi']['A'] ?? 0,
                'distribusi_b'        => $hasil['distribusi']['B'] ?? 0,
                'distribusi_c'        => $hasil['distribusi']['C'] ?? 0,
                'created_at'          => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => 'Clustering berhasil dijalankan.',
                'total_peminjam'    => $hasil['total_peminjam'],
                'silhouette_persen' => $hasil['silhouette_persen'],
                'akurasi_persen'    => $hasil['akurasi_persen'],
                'distribusi'        => $hasil['distribusi'],
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke Python API. Pastikan Flask berjalan: python app.py',
            ], 503);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // ELBOW METHOD — untuk grafik di dashboard
    // ══════════════════════════════════════════════════════════════════════
    public function elbow()
    {
        try {
            $dataFitur = $this->hitungFiturF1F5();

            $response = Http::timeout(60)
                ->post(self::PYTHON_API . '/elbow', [
                    'data' => $dataFitur,
                ]);

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // DETAIL PROFIL CLUSTER SATU PEMINJAM
    // ══════════════════════════════════════════════════════════════════════
    public function detail($userId)
    {
        $profil = ProfilCluster::with('pengguna')
            ->where('pengguna_id', $userId)
            ->first();

        if (!$profil) {
            return response()->json([
                'success'        => false,
                'has_cluster'    => false,
                'message'        => 'Peminjam baru — belum ada riwayat peminjaman yang cukup untuk analisis perilaku.',
                'min_transaksi'  => 3,
            ]);
        }

        return response()->json([
            'success'      => true,
            'has_cluster'  => true,
            'data'         => [
                'label'        => $profil->label_cluster,
                'nama_cluster' => $profil->nama_cluster,
                'nilai_f1'     => $profil->nilai_f1,
                'nilai_f2'     => $profil->nilai_f2,
                'nilai_f3'     => $profil->nilai_f3,
                'nilai_f4'     => $profil->nilai_f4,
                'nilai_f5'     => $profil->nilai_f5,
                'tgl_diperbarui' => $profil->tgl_diperbarui,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER: Hitung F1–F5 dari tabel peminjaman
    // ══════════════════════════════════════════════════════════════════════
    private function hitungFiturF1F5(): array
    {
        // Ambil semua pengguna yang memiliki >= 3 transaksi peminjaman
        $peminjaman = DB::table('peminjaman as p')
            ->join('pengguna as u', 'u.id', '=', 'p.pengguna_id')
            ->join('aset as a', 'a.id', '=', 'p.aset_id')
            ->select(
                'p.pengguna_id as user_id',
                'u.nama',
                'p.tgl_pengajuan',
                'p.tgl_kembali_aktual',
                'p.tgl_rencana_kembali',
                'a.kategori',
                'a.nilai_perolehan'
            )
            ->whereNotNull('p.tgl_kembali_aktual')
            ->orderBy('p.pengguna_id')
            ->get();

        // Kelompokkan per pengguna
        $grouped = $peminjaman->groupBy('user_id');

        $dataFitur = [];

        foreach ($grouped as $userId => $transaksi) {
            $n = $transaksi->count();

            // Minimal 3 transaksi
            if ($n < 3) continue;

            // F1 — Frekuensi peminjaman
            $f1 = $n;

            // F2 — Rata-rata durasi (hari)
            $totalDurasi = $transaksi->sum(function ($t) {
                $pinjam  = Carbon::parse($t->tgl_pengajuan);
                $kembali = Carbon::parse($t->tgl_kembali_aktual);
                return $pinjam->diffInDays($kembali);
            });
            $f2 = round($totalDurasi / $n, 1);

            // F3 — Tingkat keterlambatan (%)
            $terlambat = $transaksi->filter(function ($t) {
                return Carbon::parse($t->tgl_kembali_aktual)
                    ->gt(Carbon::parse($t->tgl_rencana_kembali));
            })->count();
            $f3 = round($terlambat / $n * 100, 1);

            // F4 — Variasi jenis aset (jumlah kategori unik)
            $f4 = $transaksi->pluck('kategori')->unique()->count();

            // F5 — Total nilai aset dipinjam (Rp)
            $f5 = $transaksi->sum('nilai_perolehan');

            $dataFitur[] = [
                'user_id' => $userId,
                'nama'    => $transaksi->first()->nama,
                'f1'      => $f1,
                'f2'      => $f2,
                'f3'      => $f3,
                'f4'      => $f4,
                'f5'      => (float) $f5,
            ];
        }

        return $dataFitur;
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER: Cek status Flask API
    // ══════════════════════════════════════════════════════════════════════
    private function cekStatusApi(): bool
    {
        try {
            $response = Http::timeout(3)->get(self::PYTHON_API . '/status');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}