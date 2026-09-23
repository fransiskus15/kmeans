<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaryawanController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // HELPER — data karyawan dari user yang sedang login
    // ══════════════════════════════════════════════════════════════════════
    private function karyawanData(): array
    {
        $user        = Auth::user();
        $unreadCount = Notifikasi::where('pengguna_id', Auth::id())
                          ->where('dibaca', false)
                          ->count();

        return [
            'nama'              => $user->nama ?? '-',
            'role'              => 'Karyawan',
            'inisial'           => strtoupper(substr($user->nama ?? 'K', 0, 2)),
            'unread_notifikasi' => $unreadCount,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD — semua data dari database berdasarkan user login
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard()
    {
        $userId = Auth::id();

        // ── 1. Stat Cards ──────────────────────────────────────────────────
        $stats = [
            'sedang_dipinjam'   => Peminjaman::where('pengguna_id', $userId)
                                      ->whereIn('status', ['Disetujui', 'Dipinjam'])
                                      ->count(),
            'menunggu_approval' => Peminjaman::where('pengguna_id', $userId)
                                      ->where('status', 'Menunggu Persetujuan')
                                      ->count(),
            'total_peminjaman'  => Peminjaman::where('pengguna_id', $userId)
                                      ->count(),
        ];

        // ── 2. Peminjaman aktif (Disetujui / Dipinjam) ────────────────────
        $peminjaman_aktif = Peminjaman::with('aset')
            ->where('pengguna_id', $userId)
            ->whereIn('status', ['Disetujui', 'Dipinjam'])
            ->orderBy('tgl_rencana_kembali')
            ->get()
            ->map(function ($p) {
                $batas     = Carbon::parse($p->tgl_rencana_kembali);
                $terlambat = !$p->tgl_kembali_aktual && Carbon::now()->gt($batas);

                return [
                    'aset'          => $p->aset->nama_aset ?? '-',
                    'batas_kembali' => $batas->format('d M Y'),
                    'status'        => $terlambat
                                        ? 'Terlambat ' . Carbon::now()->diffInDays($batas) . ' hr'
                                        : 'Aktif',
                ];
            });

        // ── 3. Menunggu approval ───────────────────────────────────────────
        $menunggu_approval = Peminjaman::with('aset')
            ->where('pengguna_id', $userId)
            ->where('status', 'Menunggu Persetujuan')
            ->orderBy('tgl_pengajuan', 'desc')
            ->get()
            ->map(fn($p) => [
                'aset'       => $p->aset->nama_aset ?? '-',
                'tgl_ajukan' => Carbon::parse($p->tgl_pengajuan)->format('d M Y'),
                'status'     => 'Menunggu',
            ]);

        // ── 4. Riwayat terakhir (5 peminjaman selesai terbaru) ────────────
        $riwayat_terakhir = Peminjaman::with('aset')
            ->where('pengguna_id', $userId)
            ->whereIn('status', ['Dikembalikan', 'Dikembalikan Terlambat'])
            ->orderBy('tgl_kembali_aktual', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                $isTerlambat = $p->status === 'Dikembalikan Terlambat'
                    || (
                        $p->tgl_kembali_aktual
                        && Carbon::parse($p->tgl_kembali_aktual)->gt(Carbon::parse($p->tgl_rencana_kembali))
                    );

                $hariTerlambat = 0;
                if ($isTerlambat && $p->tgl_kembali_aktual) {
                    $hariTerlambat = Carbon::parse($p->tgl_rencana_kembali)
                        ->diffInDays(Carbon::parse($p->tgl_kembali_aktual));
                }

                return [
                    'aset'   => $p->aset->nama_aset ?? '-',
                    'kembali'=> $p->tgl_kembali_aktual
                                    ? Carbon::parse($p->tgl_kembali_aktual)->format('d M Y')
                                    : '-',
                    'ket'    => $isTerlambat
                                    ? 'Terlambat ' . $hariTerlambat . ' hr'
                                    : 'Tepat waktu',
                ];
            });

        // ── 5. Profil peminjaman (statistik perilaku) ─────────────────────
        $selesai      = Peminjaman::where('pengguna_id', $userId)
                            ->whereIn('status', ['Dikembalikan', 'Dikembalikan Terlambat'])
                            ->get();
        $totalSelesai = $selesai->count();

        // Rata-rata durasi (hari pinjam → hari kembali aktual)
        $rataDurasi = 0;
        if ($totalSelesai > 0) {
            $totalHari = $selesai->sum(function ($p) {
                if ($p->tgl_pinjam && $p->tgl_kembali_aktual) {
                    return Carbon::parse($p->tgl_pinjam)
                        ->diffInDays(Carbon::parse($p->tgl_kembali_aktual));
                }
                return 0;
            });
            $rataDurasi = round($totalHari / $totalSelesai, 1);
        }

        // Tingkat keterlambatan (% dari total selesai)
        $totalTerlambat = $selesai->where('status', 'Dikembalikan Terlambat')->count();
        $pctTerlambat   = $totalSelesai > 0
            ? round($totalTerlambat / $totalSelesai * 100, 1)
            : 0;

        $keterangan = match (true) {
            $pctTerlambat === 0.0 => 'Sempurna',
            $pctTerlambat <= 10.0 => 'Baik',
            $pctTerlambat <= 30.0 => 'Cukup',
            default               => 'Perlu perbaikan',
        };

        $profil = [
            'total_peminjaman'      => $stats['total_peminjaman'],
            'rata_durasi'           => $rataDurasi,
            'tingkat_keterlambatan' => $pctTerlambat . '%',
            'keterangan'            => $keterangan,
        ];

        $karyawan = $this->karyawanData();

        return view('karyawan.dashboard', compact(
            'karyawan', 'stats', 'peminjaman_aktif',
            'menunggu_approval', 'riwayat_terakhir', 'profil'
        ) + ['activeMenu' => 'dashboard']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // PENGAJUAN PEMINJAMAN
    // ══════════════════════════════════════════════════════════════════════
    public function pengajuan()
    {
        // Ambil kategori unik dari aset yang tersedia
        $kategoriList = \App\Models\Aset::where('status', 'Tersedia')
            ->distinct()
            ->pluck('kategori')
            ->sort()
            ->values();

        $kategori_aset = $kategoriList->map(fn($k) => ['id' => $k, 'nama' => $k])->values()->toArray();

        $asetDb = \App\Models\Aset::where('status', 'Tersedia')
            ->orderBy('kategori')
            ->orderBy('nama_aset')
            ->get();

        $aset_tersedia = $asetDb->map(fn($a) => [
            'id'          => $a->id_aset,
            'kategori_id' => $a->kategori,
            'nama'        => $a->nama_aset,
            'status'      => $a->status,
        ])->toArray();

        $user     = Auth::user();
        $karyawan = [
            'nama'              => $user->nama,
            'role'              => 'Karyawan',
            'inisial'           => strtoupper(substr($user->nama, 0, 2)),
            'unread_notifikasi' => Notifikasi::where('pengguna_id', Auth::id())->where('dibaca', false)->count(),
        ];

        return view('karyawan.pengajuan', [
            'karyawan'      => $karyawan,
            'activeMenu'    => 'pengajuan',
            'kategori_aset' => $kategori_aset,
            'aset_tersedia' => $aset_tersedia,
            'form'          => [
                'kategori_aset'   => old('kategori_aset', $kategori_aset[0]['id'] ?? ''),
                'aset_id'         => old('aset_id', ''),
                'tanggal_pinjam'  => old('tanggal_pinjam', now()->format('Y-m-d')),
                'tanggal_kembali' => old('tanggal_kembali', now()->addDays(7)->format('Y-m-d')),
                'keperluan'       => old('keperluan', ''),
            ],
            'info_teks' => count($aset_tersedia) > 0
                ? 'Pilih aset dan isi form di atas. Permintaan akan diteruskan ke HR untuk disetujui.'
                : 'Saat ini tidak ada aset yang tersedia untuk dipinjam.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SIMPAN PENGAJUAN PEMINJAMAN
    // ══════════════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_aset'   => ['required', 'string'],
            'aset_id'         => ['required', 'integer', 'exists:aset,id_aset'],
            'tanggal_pinjam'  => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali' => ['required', 'date', 'after:tanggal_pinjam'],
            'keperluan'       => ['required', 'string', 'max:1000'],
        ], [
            'aset_id.exists'                => 'Aset yang dipilih tidak valid.',
            'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh sebelum hari ini.',
            'tanggal_kembali.after'         => 'Tanggal kembali harus setelah tanggal pinjam.',
        ]);

        // Validasi: durasi peminjaman maksimal 7 hari
        $tglPinjam  = Carbon::parse($validated['tanggal_pinjam']);
        $tglKembali = Carbon::parse($validated['tanggal_kembali']);
        $durasi     = $tglPinjam->diffInDays($tglKembali);

        if ($durasi > 7) {
            return back()
                ->withInput()
                ->withErrors(['tanggal_kembali' => "Durasi peminjaman maksimal 7 hari. Durasi yang Anda masukkan: {$durasi} hari."]);
        }

        // Pastikan aset masih tersedia
        $aset = \App\Models\Aset::where('id_aset', $validated['aset_id'])
            ->where('status', 'Tersedia')
            ->lockForUpdate()
            ->first();

        if (!$aset) {
            return back()
                ->withInput()
                ->withErrors(['aset_id' => 'Aset yang dipilih sudah tidak tersedia atau sedang dipesan oleh karyawan lain.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($aset, $validated) {
            // Buat record peminjaman
            \App\Models\Peminjaman::create([
                'pengguna_id'         => Auth::id(),
                'aset_id'             => $aset->id_aset,
                'tgl_pengajuan'       => now()->toDateString(),
                'tgl_pinjam'          => $validated['tanggal_pinjam'],
                'tgl_rencana_kembali' => $validated['tanggal_kembali'],
                'tgl_kembali_aktual'  => null,
                'status'              => 'Menunggu Persetujuan',
                'keterangan'          => $validated['keperluan'],
            ]);

            // Langsung tandai aset sebagai "Dipesan" agar tidak bisa diajukan
            // oleh karyawan lain selama menunggu persetujuan HR
            $aset->update(['status' => 'Dipesan']);
        });

        return redirect()
            ->route('karyawan.status')
            ->with('success', 'Permintaan peminjaman berhasil diajukan dan sedang menunggu persetujuan HR.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // NOTIFIKASI — ambil dari tabel notifikasi berdasarkan user login
    // ══════════════════════════════════════════════════════════════════════
    public function notifikasi()
    {
        $userId = Auth::id();

        $notifikasiRaw = Notifikasi::where('pengguna_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $notifikasi = $notifikasiRaw->map(fn($n) => [
            'id'       => $n->id_notifikasi,
            'type'     => $n->tipe,
            'kategori' => 'peminjaman',
            'judul'    => $n->judul,
            'pesan'    => $n->pesan,
            'waktu'    => Carbon::parse($n->created_at)->diffForHumans(),
            'dibaca'   => (bool) $n->dibaca,
        ])->toArray();

        $unreadCount = $notifikasiRaw->where('dibaca', false)->count();

        $user     = Auth::user();
        $karyawan = [
            'nama'              => $user->nama,
            'role'              => 'Karyawan',
            'inisial'           => strtoupper(substr($user->nama, 0, 2)),
            'unread_notifikasi' => $unreadCount,
        ];

        return view('karyawan.notifikasi', compact('karyawan', 'notifikasi', 'unreadCount')
            + ['activeMenu' => 'notifikasi']);
    }

    // Tandai SATU notifikasi sebagai dibaca (dipanggil via AJAX)
    public function tandaiDibaca($id)
    {
        $notif = Notifikasi::where('pengguna_id', Auth::user()->id_pengguna)
            ->findOrFail($id);

        $notif->update(['dibaca' => true, 'dibaca_pada' => now()]);

        return response()->json(['success' => true]);
    }

    // Tandai SEMUA notifikasi sebagai dibaca (dipanggil via AJAX)
    public function tandaiSemuaDibaca()
    {
        Notifikasi::where('pengguna_id', Auth::user()->id_pengguna)
            ->where('dibaca', false)
            ->update(['dibaca' => true, 'dibaca_pada' => now()]);

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // STATUS PEMINJAMAN — ambil dari database berdasarkan user login
    // ══════════════════════════════════════════════════════════════════════
    public function status(Request $request)
    {
        $userId = Auth::id();

        $tab   = $request->get('tab', 'semua');

        $query = Peminjaman::with(['aset'])
            ->where('pengguna_id', $userId)
            ->orderBy('tgl_pengajuan', 'desc');

        if ($tab === 'aktif') {
            $query->whereIn('status', ['Disetujui', 'Dipinjam']);
        } elseif ($tab === 'menunggu') {
            $query->where('status', 'Menunggu Persetujuan');
        } elseif ($tab === 'selesai') {
            $query->whereIn('status', ['Dikembalikan', 'Dikembalikan Terlambat', 'Ditolak']);
        }

        $peminjamanRaw = $query->paginate(10);

        $peminjaman = $peminjamanRaw->map(function ($p) {
            $batas         = Carbon::parse($p->tgl_rencana_kembali);
            $terlambat     = !$p->tgl_kembali_aktual && Carbon::now()->gt($batas);
            $terlambatHari = $terlambat ? Carbon::now()->diffInDays($batas) : 0;

            $statusLabel = match ($p->status) {
                'Menunggu Persetujuan'   => 'Menunggu',
                'Disetujui'              => 'Disetujui',
                'Dipinjam'               => $terlambat ? 'Terlambat ' . $terlambatHari . ' hr' : 'Dipinjam',
                'Dikembalikan'           => 'Dikembalikan',
                'Dikembalikan Terlambat' => 'Terlambat',
                'Ditolak'                => 'Ditolak',
                default                  => $p->status,
            };

            $statusType = match (true) {
                in_array($p->status, ['Menunggu Persetujuan'])   => 'menunggu',
                in_array($p->status, ['Disetujui'])              => 'disetujui',
                $p->status === 'Dipinjam' && !$terlambat         => 'aktif',
                $p->status === 'Dipinjam' && $terlambat          => 'terlambat',
                in_array($p->status, ['Dikembalikan'])           => 'selesai',
                in_array($p->status, ['Dikembalikan Terlambat']) => 'terlambat_selesai',
                in_array($p->status, ['Ditolak'])                => 'ditolak',
                default                                          => 'lain',
            };

            return [
                'id'             => $p->id_peminjaman,
                'aset'           => $p->aset->nama_aset ?? '-',
                'kategori'       => $p->aset->kategori ?? '-',
                'tgl_pengajuan'  => Carbon::parse($p->tgl_pengajuan)->format('d M Y'),
                'tgl_pinjam'     => $p->tgl_pinjam
                                        ? Carbon::parse($p->tgl_pinjam)->format('d M Y')
                                        : '-',
                'batas_kembali'  => $batas->format('d M Y'),
                'tgl_kembali'    => $p->tgl_kembali_aktual
                                        ? Carbon::parse($p->tgl_kembali_aktual)->format('d M Y')
                                        : '-',
                'durasi'         => $p->tgl_pinjam && $p->tgl_kembali_aktual
                                        ? Carbon::parse($p->tgl_pinjam)->diffInDays($p->tgl_kembali_aktual) . ' hari'
                                        : '-',
                'keterangan'     => $p->keterangan ?? '-',
                'status_label'   => $statusLabel,
                'status_type'    => $statusType,
                'terlambat_hari' => $terlambatHari,
            ];
        });

        $statistik = [
            'total'    => Peminjaman::where('pengguna_id', $userId)->count(),
            'aktif'    => Peminjaman::where('pengguna_id', $userId)
                            ->whereIn('status', ['Disetujui', 'Dipinjam'])->count(),
            'menunggu' => Peminjaman::where('pengguna_id', $userId)
                            ->where('status', 'Menunggu Persetujuan')->count(),
            'selesai'  => Peminjaman::where('pengguna_id', $userId)
                            ->whereIn('status', ['Dikembalikan', 'Dikembalikan Terlambat'])->count(),
        ];

        $user     = Auth::user();
        $karyawan = [
            'nama'              => $user->nama,
            'role'              => 'Karyawan',
            'inisial'           => strtoupper(substr($user->nama, 0, 2)),
            'unread_notifikasi' => Notifikasi::where('pengguna_id', $userId)->where('dibaca', false)->count(),
        ];

        return view('karyawan.status', compact(
            'peminjaman', 'peminjamanRaw', 'statistik', 'karyawan', 'tab'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // RIWAYAT PEMINJAMAN — semua peminjaman milik karyawan yang login
    // ══════════════════════════════════════════════════════════════════════
    public function riwayat(Request $request)
    {
        $userId = Auth::id();

        $query = Peminjaman::with(['aset'])
            ->where('pengguna_id', $userId)
            ->orderBy('tgl_pengajuan', 'desc');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian nama aset
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas('aset', fn($q) => $q->where('nama_aset', 'like', "%$cari%"));
        }

        // Filter bulan/tahun
        if ($request->filled('bulan')) {
            [$tahun, $bln] = explode('-', $request->bulan);
            $query->whereYear('tgl_pengajuan', $tahun)->whereMonth('tgl_pengajuan', $bln);
        }

        $riwayat = $query->paginate(10)->withQueryString();

        // Ringkasan statistik milik karyawan ini
        $base = Peminjaman::where('pengguna_id', $userId);
        $ringkasan = [
            'total'         => (clone $base)->count(),
            'menunggu'      => (clone $base)->where('status', 'Menunggu Persetujuan')->count(),
            'aktif'         => (clone $base)->whereIn('status', ['Disetujui', 'Dipinjam'])->count(),
            'dikembalikan'  => (clone $base)->whereIn('status', ['Dikembalikan', 'Dikembalikan Terlambat'])->count(),
            'ditolak'       => (clone $base)->where('status', 'Ditolak')->count(),
        ];

        $karyawan = $this->karyawanData();

        return view('karyawan.riwayat', compact('karyawan', 'riwayat', 'ringkasan')
            + ['activeMenu' => 'riwayat']);
    }
}
