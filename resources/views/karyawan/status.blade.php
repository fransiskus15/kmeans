@extends('layouts.karyawan')

@section('title', 'Status Peminjaman — Karyawan')

@section('page-title', 'Status peminjaman saya')

@section('content')

@push('styles')
<style>
    /* ── Tab Filter ─────────────────────────────────────── */
    .tab-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 7px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #e0e0db;
        background: #ffffff;
        color: #4a4a4a;
        text-decoration: none;
        transition: all .15s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .tab-btn:hover { background: #f5f5f3; color: #1a1a1a; }
    .tab-btn.active {
        background: #1a1a1a;
        color: #ffffff;
        border-color: #1a1a1a;
    }
    .tab-count {
        background: rgba(255,255,255,.25);
        border-radius: 10px;
        padding: 0 7px;
        font-size: 11px;
        font-weight: 600;
    }
    .tab-btn:not(.active) .tab-count {
        background: #f0f0eb;
        color: #6b6b6b;
    }

    /* ── Status Badges ──────────────────────────────────── */
    .status-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }
    .pill-menunggu    { background:#FEF3C7; color:#92400E; }
    .pill-disetujui   { background:#D1FAE5; color:#065F46; }
    .pill-aktif       { background:#DBEAFE; color:#1E40AF; }
    .pill-terlambat   { background:#FEE2E2; color:#991B1B; }
    .pill-selesai     { background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; }
    .pill-terlambat_selesai { background:#FFF7ED; color:#C2410C; }
    .pill-ditolak     { background:#F3F4F6; color:#6B7280; }
    .pill-lain        { background:#F3F4F6; color:#6B7280; }

    /* ── Timeline step (detail row) ─────────────────────── */
    .timeline-steps {
        display: flex;
        gap: 0;
        align-items: stretch;
        margin-bottom: 20px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .tl-step {
        flex: 1;
        min-width: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    .tl-step::after {
        content: '';
        position: absolute;
        top: 14px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #E5E7EB;
        z-index: 0;
    }
    .tl-step:last-child::after { display: none; }
    .tl-dot {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #E5E7EB;
        border: 3px solid #fff;
        display: flex; align-items: center; justify-content: center;
        z-index: 1;
        font-size: 13px;
        color: #9CA3AF;
        box-shadow: 0 0 0 2px #E5E7EB;
        transition: all .2s;
    }
    .tl-dot.done   { background: #10B981; color: #fff; box-shadow: 0 0 0 2px #D1FAE5; }
    .tl-dot.active { background: #3B82F6; color: #fff; box-shadow: 0 0 0 2px #DBEAFE; }
    .tl-dot.late   { background: #EF4444; color: #fff; box-shadow: 0 0 0 2px #FEE2E2; }
    .tl-dot.reject { background: #9CA3AF; color: #fff; }
    .tl-label {
        font-size: 11px;
        color: #6B7280;
        margin-top: 6px;
        text-align: center;
    }
    .tl-date {
        font-size: 10px;
        color: #9CA3AF;
        text-align: center;
        margin-top: 2px;
    }

    /* ── Detail card ─────────────────────────────────────── */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #F3F4F6;
        gap: 16px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label {
        font-size: 12px;
        color: #9AA0A6;
        flex-shrink: 0;
    }
    .detail-value {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
        text-align: right;
    }

    /* ── Warning banner ─────────────────────────────────── */
    .warning-banner {
        background: #FEF2F2;
        border: 1px solid #FCA5A5;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 12.5px;
        color: #991B1B;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .info-banner {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 12.5px;
        color: #1E40AF;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    /* ── Collapse expand ─────────────────────────────────── */
    .collapse-toggle {
        background: none;
        border: none;
        cursor: pointer;
        color: #6B7280;
        font-size: 12px;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .collapse-toggle:hover { color: #1a1a1a; }

    /* ── Empty state ─────────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 56px 24px;
        color: #9AA0A6;
    }
    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: .4;
    }
    .empty-state p { font-size: 14px; margin: 0; }
</style>
@endpush

    {{-- ── Stat Cards ──────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total pengajuan</div>
                <div class="stat-value dark">{{ $statistik['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Sedang dipinjam</div>
                <div class="stat-value blue">{{ $statistik['aktif'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Menunggu persetujuan</div>
                <div class="stat-value orange">{{ $statistik['menunggu'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Telah dikembalikan</div>
                <div class="stat-value green">{{ $statistik['selesai'] }}</div>
            </div>
        </div>
    </div>

    {{-- ── Tab Filter ───────────────────────────────────────── --}}
    <div class="tab-bar">
        <a href="{{ route('karyawan.status', ['tab' => 'semua']) }}"
           class="tab-btn {{ $tab === 'semua' ? 'active' : '' }}">
            Semua <span class="tab-count">{{ $statistik['total'] }}</span>
        </a>
        <a href="{{ route('karyawan.status', ['tab' => 'menunggu']) }}"
           class="tab-btn {{ $tab === 'menunggu' ? 'active' : '' }}">
            <i class="bi bi-clock"></i> Menunggu
            <span class="tab-count">{{ $statistik['menunggu'] }}</span>
        </a>
        <a href="{{ route('karyawan.status', ['tab' => 'aktif']) }}"
           class="tab-btn {{ $tab === 'aktif' ? 'active' : '' }}">
            <i class="bi bi-arrow-repeat"></i> Dipinjam
            <span class="tab-count">{{ $statistik['aktif'] }}</span>
        </a>
        <a href="{{ route('karyawan.status', ['tab' => 'selesai']) }}"
           class="tab-btn {{ $tab === 'selesai' ? 'active' : '' }}">
            <i class="bi bi-check-circle"></i> Selesai
            <span class="tab-count">{{ $statistik['selesai'] }}</span>
        </a>
    </div>

    {{-- ── Daftar Peminjaman ────────────────────────────────── --}}
    @forelse ($peminjaman as $item)

    @php
        $dotClass = match($item['status_type']) {
            'menunggu'           => '',
            'disetujui'          => 'done',
            'aktif'              => 'active',
            'terlambat'          => 'late',
            'selesai'            => 'done',
            'terlambat_selesai'  => 'late',
            'ditolak'            => 'reject',
            default              => '',
        };
        $pillClass = 'pill-' . $item['status_type'];
    @endphp

    <div class="panel-card mb-3">
        {{-- Header card --}}
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
            <div>
                <div class="fw-semibold" style="font-size:15px;">
                    {{ $item['aset'] }}
                </div>
                <div style="font-size:12px; color:#9AA0A6; margin-top:2px;">
                    {{ $item['kategori'] }} &nbsp;·&nbsp; ID #{{ $item['id'] }}
                    &nbsp;·&nbsp; Diajukan {{ $item['tgl_pengajuan'] }}
                </div>
            </div>
            <span class="status-pill {{ $pillClass }}">
                {{ $item['status_label'] }}
            </span>
        </div>

        {{-- Warning / info banner --}}
        @if ($item['status_type'] === 'terlambat')
        <div class="warning-banner">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Peminjaman terlambat {{ $item['terlambat_hari'] }} hari dari batas kembali {{ $item['batas_kembali'] }}.
            Segera kembalikan ke Admin Aset.
        </div>
        @elseif ($item['status_type'] === 'disetujui')
        <div class="info-banner">
            <i class="bi bi-info-circle-fill"></i>
            Pengajuan disetujui. Silakan ambil aset ke Admin Aset dan sebutkan ID peminjaman <strong>#{{ $item['id'] }}</strong>.
        </div>
        @endif

        {{-- Timeline progress --}}
        <div class="timeline-steps mb-3">
            {{-- Langkah 1: Pengajuan --}}
            <div class="tl-step">
                <div class="tl-dot done"><i class="bi bi-check"></i></div>
                <div class="tl-label">Diajukan</div>
                <div class="tl-date">{{ $item['tgl_pengajuan'] }}</div>
            </div>

            {{-- Langkah 2: Persetujuan HR --}}
            <div class="tl-step">
                @if (in_array($item['status_type'], ['disetujui','aktif','terlambat','selesai','terlambat_selesai']))
                    <div class="tl-dot done"><i class="bi bi-check"></i></div>
                    <div class="tl-label">Disetujui HR</div>
                @elseif ($item['status_type'] === 'ditolak')
                    <div class="tl-dot reject"><i class="bi bi-x"></i></div>
                    <div class="tl-label">Ditolak</div>
                @else
                    <div class="tl-dot"><i class="bi bi-clock"></i></div>
                    <div class="tl-label">Menunggu HR</div>
                @endif
                <div class="tl-date">&nbsp;</div>
            </div>

            {{-- Langkah 3: Pengambilan --}}
            <div class="tl-step">
                @if (in_array($item['status_type'], ['aktif','terlambat','selesai','terlambat_selesai']))
                    <div class="tl-dot done"><i class="bi bi-check"></i></div>
                    <div class="tl-label">Diambil</div>
                    <div class="tl-date">{{ $item['tgl_pinjam'] }}</div>
                @else
                    <div class="tl-dot"><i class="bi bi-arrow-down-circle"></i></div>
                    <div class="tl-label">Pengambilan</div>
                    <div class="tl-date">&nbsp;</div>
                @endif
            </div>

            {{-- Langkah 4: Pengembalian --}}
            <div class="tl-step">
                @if ($item['status_type'] === 'selesai')
                    <div class="tl-dot done"><i class="bi bi-check"></i></div>
                    <div class="tl-label">Dikembalikan</div>
                    <div class="tl-date">{{ $item['tgl_kembali'] }}</div>
                @elseif ($item['status_type'] === 'terlambat_selesai')
                    <div class="tl-dot late"><i class="bi bi-exclamation"></i></div>
                    <div class="tl-label">Terlambat</div>
                    <div class="tl-date">{{ $item['tgl_kembali'] }}</div>
                @elseif ($item['status_type'] === 'terlambat')
                    <div class="tl-dot late"><i class="bi bi-exclamation"></i></div>
                    <div class="tl-label">Lewat batas</div>
                    <div class="tl-date">{{ $item['batas_kembali'] }}</div>
                @else
                    <div class="tl-dot"><i class="bi bi-arrow-return-left"></i></div>
                    <div class="tl-label">Kembali rencana</div>
                    <div class="tl-date">{{ $item['batas_kembali'] }}</div>
                @endif
            </div>
        </div>

        {{-- Detail info --}}
        <div class="row g-3" style="font-size:13px;">
            <div class="col-sm-6">
                <div class="detail-row">
                    <span class="detail-label">Tanggal pinjam</span>
                    <span class="detail-value">{{ $item['tgl_pinjam'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Batas kembali</span>
                    <span class="detail-value">{{ $item['batas_kembali'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tgl kembali aktual</span>
                    <span class="detail-value">{{ $item['tgl_kembali'] }}</span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="detail-row">
                    <span class="detail-label">Durasi pinjam</span>
                    <span class="detail-value">{{ $item['durasi'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Keterangan</span>
                    <span class="detail-value" style="max-width:180px; word-break:break-word;">
                        {{ Str::limit($item['keterangan'], 60, '…') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @empty
    <div class="panel-card">
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
            <p class="fw-semibold mb-1">Belum ada data peminjaman</p>
            <p>
                @if ($tab !== 'semua')
                    Tidak ada peminjaman dengan status ini.
                    <a href="{{ route('karyawan.status') }}">Lihat semua</a>
                @else
                    Anda belum pernah mengajukan peminjaman.
                    <a href="{{ route('karyawan.pengajuan') }}">Ajukan sekarang</a>
                @endif
            </p>
        </div>
    </div>
    @endforelse

    {{-- ── Pagination ───────────────────────────────────────── --}}
    @if ($peminjamanRaw->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $peminjamanRaw->appends(['tab' => $tab])->links() }}
    </div>
    @endif

@endsection
