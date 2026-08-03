@extends('layouts.hr')

@section('title', 'Persetujuan Peminjaman — HR')

@section('page-title', 'Persetujuan peminjaman')
@section('page-subtitle', 'HR / Kepala Divisi')

@section('content')

@push('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #6b6b6b;
        text-decoration: none;
        margin-bottom: 16px;
        transition: color .15s;
    }
    .back-link:hover { color: #1a1a1a; }

    /* ── Daftar permintaan (mode tanpa ?id) ─────────────── */
    .empty-approval {
        text-align: center;
        padding: 56px 24px;
        color: #9AA0A6;
    }
    .empty-approval .empty-icon { font-size: 48px; opacity: .4; margin-bottom: 12px; }

    /* ── Badge status keputusan ─────────────────────────── */
    .badge-disetujui { background:#D1FAE5; color:#065F46; }
    .badge-ditolak   { background:#FEE2E2; color:#991B1B; }
    .badge-menunggu  { background:#FEF3C7; color:#92400E; }

    /* ── Alert bar ──────────────────────────────────────── */
    .alert-success-bar {
        background: #D1FAE5; border: 1px solid #6EE7B7;
        border-radius: 8px; padding: 10px 16px;
        font-size: 13px; color: #065F46;
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 16px;
    }
    .alert-error-bar {
        background: #FEE2E2; border: 1px solid #FCA5A5;
        border-radius: 8px; padding: 10px 16px;
        font-size: 13px; color: #991B1B;
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 16px;
    }

    /* ── Tombol keputusan ───────────────────────────────── */
    .btn-setujui {
        background: #10B981; color: #fff;
        border: none; border-radius: 8px;
        padding: 10px 24px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        transition: background .15s;
    }
    .btn-setujui:hover { background: #059669; }
    .btn-tolak {
        background: #fff; color: #EF4444;
        border: 2px solid #EF4444; border-radius: 8px;
        padding: 10px 24px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        transition: all .15s;
    }
    .btn-tolak:hover { background: #FEF2F2; }

    /* ── Sudah diproses banner ──────────────────────────── */
    .processed-banner {
        background: #F9FAFB; border: 1px solid #E5E7EB;
        border-radius: 10px; padding: 20px 24px;
        text-align: center; color: #6B7280; font-size: 14px;
    }
</style>
@endpush

    {{-- Flash messages --}}
    @if (session('success'))
    <div class="alert-success-bar">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="alert-error-bar">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════
         MODE A: Tidak ada ?id → tampilkan daftar semua permintaan menunggu
    ══════════════════════════════════════════════════ --}}
    @if (!$permintaan)

    <div class="panel-card">
        <div class="panel-title mb-3">Semua permintaan menunggu persetujuan</div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Aset</th>
                        <th>Tgl Ajuan</th>
                        <th>Cluster</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjaman as $p)
                    <tr>
                        <td class="fw-semibold" style="font-size:12px;color:#9aa0a6;">#{{ $p->id_peminjaman }}</td>
                        <td>{{ $p->peminjam->nama ?? '-' }}</td>
                        <td>{{ $p->aset->nama_aset ?? '-' }}</td>
                        <td style="font-size:12px;color:#9aa0a6;">
                            {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->format('d M Y') }}
                        </td>
                        <td>
                            @php $cl = $p->peminjam->profilCluster->label_cluster ?? '?' @endphp
                            <span class="cluster-dot cluster-{{ strtolower($cl) }}">{{ $cl }}</span>
                        </td>
                        <td>
                            <a href="{{ route('hr.approval', ['id' => $p->id_peminjaman]) }}" class="link-tinjau">
                                Tinjau <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-approval">
                                <div class="empty-icon"><i class="bi bi-check-all"></i></div>
                                <p class="fw-semibold mb-1">Tidak ada permintaan yang menunggu.</p>
                                <p style="font-size:13px;">Semua peminjaman sudah diproses.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($peminjaman) && $peminjaman->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $peminjaman->links() }}
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════
         MODE B: Ada ?id → tampilkan detail + form keputusan
    ══════════════════════════════════════════════════ --}}
    @else

    <a href="{{ route('hr.approval') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar permintaan
    </a>

    <div class="row g-3">

        {{-- Kolom kiri: Detail & Form Keputusan --}}
        <div class="col-lg-6">

            {{-- Detail permintaan --}}
            <div class="panel-card mb-3">
                <div class="panel-title">Detail permintaan #{{ $permintaan['id'] }}</div>

                <div class="detail-row">
                    <span class="detail-label">Peminjam</span>
                    <span class="detail-value fw-semibold">{{ $permintaan['peminjam'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Divisi</span>
                    <span class="detail-value">{{ $permintaan['divisi'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Aset diminta</span>
                    <span class="detail-value">{{ $permintaan['aset'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kategori aset</span>
                    <span class="detail-value">{{ $permintaan['kategori_aset'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal pinjam</span>
                    <span class="detail-value">{{ $permintaan['tanggal_pinjam'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Batas kembali</span>
                    <span class="detail-value">{{ $permintaan['batas_kembali'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal ajuan</span>
                    <span class="detail-value">{{ $permintaan['tgl_ajuan'] }}</span>
                </div>
                <div class="detail-row" style="border-bottom:none;">
                    <span class="detail-label">Keperluan</span>
                    <span class="detail-value" style="max-width:200px;text-align:right;word-break:break-word;">
                        {{ $permintaan['keperluan'] }}
                    </span>
                </div>
            </div>

            {{-- Form Keputusan --}}
            <div class="panel-card">
                <div class="panel-title mb-3">Keputusan HR</div>

                @if ($permintaan['status'] !== 'Menunggu Persetujuan')
                    {{-- Sudah diproses --}}
                    <div class="processed-banner">
                        <i class="bi bi-info-circle me-2"></i>
                        Permintaan ini sudah diproses sebelumnya dengan status:
                        <strong>{{ $permintaan['status'] }}</strong>
                    </div>
                @else
                    <form action="{{ route('hr.approval.proses') }}" method="POST" id="formApproval">
                        @csrf
                        <input type="hidden" name="peminjaman_id" value="{{ $permintaan['id'] }}">
                        <input type="hidden" name="keputusan" id="inputKeputusan" value="">

                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                Catatan (opsional)
                            </label>
                            <textarea name="catatan" class="form-control form-note" rows="4"
                                placeholder="Alasan penolakan atau catatan tambahan..."></textarea>
                        </div>

                        @error('catatan')
                            <div class="text-danger" style="font-size:12px;margin-bottom:8px;">{{ $message }}</div>
                        @enderror

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn-setujui"
                                onclick="submitKeputusan('setujui')">
                                <i class="bi bi-check-circle-fill"></i> Setujui
                            </button>
                            <button type="button" class="btn-tolak"
                                onclick="submitKeputusan('tolak')">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        </div>
                    </form>
                @endif
            </div>

        </div>

        {{-- Kolom kanan: Profil Perilaku Peminjam --}}
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="panel-title mb-3">Profil perilaku peminjam</div>

                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="cluster-badge">{{ $profil['cluster'] }}</div>
                    <div>
                        <div class="profile-headline">{{ $profil['judul'] }}</div>
                        <div class="profile-sub">{{ $profil['subjudul'] }}</div>
                        <div class="profile-rekom">{{ $profil['rekomendasi'] }}</div>
                    </div>
                </div>

                <div class="radar-wrap">
                    <canvas id="radarChart"></canvas>
                </div>

                <div class="table-responsive">
                    <table class="table fitur-table">
                        <thead>
                            <tr>
                                <th>Variabel</th>
                                <th>Nilai</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fitur as $row)
                            <tr>
                                <td>{{ $row['var'] }}</td>
                                <td class="fw-semibold">{{ $row['nilai'] }}</td>
                                <td>
                                    <span class="badge-ket badge-{{ $row['badge'] }}">{{ $row['ket'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    @endif

@endsection

@push('scripts')
<script>
    // ── Form submit dengan konfirmasi ─────────────────────────────────────
    function submitKeputusan(keputusan) {
        const label  = keputusan === 'setujui' ? 'menyetujui' : 'menolak';
        const catatan = document.querySelector('textarea[name="catatan"]')?.value || '';

        if (keputusan === 'tolak' && !catatan.trim()) {
            if (!confirm('Apakah Anda yakin ingin menolak tanpa catatan alasan?')) return;
        } else {
            if (!confirm(`Anda yakin ingin ${label} peminjaman ini?`)) return;
        }

        document.getElementById('inputKeputusan').value = keputusan;
        document.getElementById('formApproval').submit();
    }

    // ── Radar Chart (hanya jika mode detail) ─────────────────────────────
    @if ($permintaan && isset($radar))
    const radarData = @json($radar);

    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [{
                data: radarData.values,
                backgroundColor: 'rgba(45, 159, 111, 0.25)',
                borderColor: '#2d9f6f',
                borderWidth: 2,
                pointBackgroundColor: '#2d9f6f',
                pointRadius: 4,
            }]
        },
        options: {
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { display: false },
                    grid: { color: 'rgba(45, 159, 111, 0.2)' },
                    angleLines: { color: 'rgba(45, 159, 111, 0.2)' },
                    pointLabels: {
                        font: { size: 12, weight: '500' },
                        color: '#2d6a4f',
                    },
                }
            },
            plugins: { legend: { display: false } },
            maintainAspectRatio: false,
        }
    });
    @endif
</script>
@endpush
