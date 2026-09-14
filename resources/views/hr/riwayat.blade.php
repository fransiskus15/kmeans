@extends('layouts.hr')

@section('title', 'Riwayat Peminjaman — HR')

@section('page-title', 'Riwayat Peminjaman')
@section('page-subtitle', 'HR / Kepala Divisi')

@section('content')

@push('styles')
<style>
    /* ── Ringkasan stat cards ─────────────────────────────── */
    .riwayat-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .rstat-card {
        flex: 1;
        min-width: 110px;
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        padding: 14px 18px;
        text-align: center;
    }
    .rstat-card .rstat-val {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .rstat-card .rstat-label {
        font-size: 11px;
        color: #9aa0a6;
        font-weight: 500;
    }
    .rstat-card.rs-total    .rstat-val { color: #1a1a1a; }
    .rstat-card.rs-setujui  .rstat-val { color: #065F46; }
    .rstat-card.rs-dipinjam .rstat-val { color: #1e40af; }
    .rstat-card.rs-kembali  .rstat-val { color: #0369a1; }
    .rstat-card.rs-tolak    .rstat-val { color: #991B1B; }

    /* ── Filter bar ───────────────────────────────────────── */
    .filter-bar {
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 18px;
    }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-group.grow { flex: 1; min-width: 160px; }
    .filter-bar label {
        font-size: 11px;
        font-weight: 600;
        color: #9aa0a6;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .filter-bar input,
    .filter-bar select {
        border: 1px solid #dde0e5;
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 13px;
        color: #1a1a1a;
        background: #fafafa;
        outline: none;
        transition: border-color .15s;
    }
    .filter-bar input:focus,
    .filter-bar select:focus { border-color: #2b6cb0; background: #fff; }
    .btn-filter {
        background: #2b6cb0; color: #fff; border: none;
        border-radius: 7px; padding: 7px 18px; font-size: 13px;
        font-weight: 600; cursor: pointer; transition: background .15s;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-filter:hover { background: #1e4e8c; }
    .btn-reset {
        background: #f5f6f8; color: #4a4a4a;
        border: 1px solid #dde0e5; border-radius: 7px;
        padding: 7px 14px; font-size: 13px; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        transition: background .15s;
    }
    .btn-reset:hover { background: #e8eaed; color: #1a1a1a; }

    /* ── Badge status ─────────────────────────────────────── */
    .badge-status {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 600;
        padding: 3px 9px; border-radius: 20px; white-space: nowrap;
    }
    .bs-disetujui    { background:#D1FAE5; color:#065F46; }
    .bs-dipinjam     { background:#DBEAFE; color:#1e40af; }
    .bs-dikembalikan { background:#E0F2FE; color:#0369a1; }
    .bs-ditolak      { background:#FEE2E2; color:#991B1B; }
    .bs-lainnya      { background:#F3F4F6; color:#374151; }

    /* ── Terlambat badge ──────────────────────────────────── */
    .badge-terlambat {
        font-size: 10px; background:#FEF3C7; color:#92400E;
        padding: 2px 7px; border-radius: 12px; font-weight: 600;
    }

    /* ── Tabel ────────────────────────────────────────────── */
    .table-riwayat th {
        font-size: 11px; font-weight: 600; color: #9aa0a6;
        text-transform: uppercase; letter-spacing: 0.05em;
        padding: 10px 12px; border-bottom: 1px solid #e8eaed;
        background: #fafafa; white-space: nowrap;
    }
    .table-riwayat td {
        padding: 11px 12px; border-bottom: 1px solid #f0f1f3;
        vertical-align: middle; font-size: 13px;
    }
    .table-riwayat tr:last-child td { border-bottom: none; }
    .table-riwayat tr:hover td { background: #f9fafc; }
    .catatan-cell {
        max-width: 150px; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
        font-size: 12px; color: #6b6b6b; display: block;
    }

    /* ── Empty state ──────────────────────────────────────── */
    .empty-riwayat { text-align: center; padding: 60px 24px; color: #9AA0A6; }
    .empty-riwayat .empty-icon { font-size: 52px; opacity: .35; margin-bottom: 14px; }
</style>
@endpush

    {{-- Flash --}}
    @if (session('success'))
    <div style="background:#D1FAE5;border:1px solid #6EE7B7;border-radius:8px;padding:10px 16px;font-size:13px;color:#065F46;display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Ringkasan --}}
    <div class="riwayat-stats">
        <div class="rstat-card rs-total">
            <div class="rstat-val">{{ $ringkasan['total'] }}</div>
            <div class="rstat-label">Total</div>
        </div>
        <div class="rstat-card rs-setujui">
            <div class="rstat-val">{{ $ringkasan['disetujui'] }}</div>
            <div class="rstat-label">Disetujui</div>
        </div>
        <div class="rstat-card rs-dipinjam">
            <div class="rstat-val">{{ $ringkasan['dipinjam'] }}</div>
            <div class="rstat-label">Dipinjam</div>
        </div>
        <div class="rstat-card rs-kembali">
            <div class="rstat-val">{{ $ringkasan['dikembalikan'] }}</div>
            <div class="rstat-label">Dikembalikan</div>
        </div>
        <div class="rstat-card rs-tolak">
            <div class="rstat-val">{{ $ringkasan['ditolak'] }}</div>
            <div class="rstat-label">Ditolak</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('hr.riwayat') }}" class="filter-bar">
        <div class="filter-group grow">
            <label for="cari-input">Cari Peminjam / Aset</label>
            <input type="text" id="cari-input" name="cari"
                   value="{{ request('cari') }}"
                   placeholder="Nama atau nama aset...">
        </div>
        <div class="filter-group">
            <label for="status-select">Status</label>
            <select id="status-select" name="status" style="min-width:150px;">
                <option value="">Semua Status</option>
                @foreach (['Disetujui','Dipinjam','Dikembalikan','Ditolak'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="bulan-input">Bulan</label>
            <input type="month" id="bulan-input" name="bulan"
                   value="{{ request('bulan') }}" style="min-width:140px;">
        </div>
        <div style="display:flex;gap:6px;align-items:flex-end;">
            <button type="submit" class="btn-filter">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="{{ route('hr.riwayat') }}" class="btn-reset">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    </form>

    {{-- Tabel Riwayat --}}
    <div class="panel-card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 18px 12px;border-bottom:1px solid #e8eaed;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div class="panel-title" style="margin:0;">Semua Riwayat Peminjaman</div>
            <span style="font-size:12px;color:#9aa0a6;">
                {{ $riwayat->total() }} data ditemukan
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-riwayat mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Divisi</th>
                        <th>Aset</th>
                        <th>Tgl Ajuan</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Kembali Aktual</th>
                        <th>Cluster</th>
                        <th>Status</th>
                        <th>Catatan HR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $p)
                    @php
                        $cl        = $p->peminjam?->profilCluster?->label_cluster ?? '?';
                        $terlambat = $p->isTerlambat();
                        $statusKey = strtolower(str_replace(' ', '', $p->status ?? ''));
                        $badgeMap  = [
                            'disetujui'    => 'bs-disetujui',
                            'dipinjam'     => 'bs-dipinjam',
                            'dikembalikan' => 'bs-dikembalikan',
                            'ditolak'      => 'bs-ditolak',
                        ];
                        $badgeClass = $badgeMap[$statusKey] ?? 'bs-lainnya';
                        $catatan    = $p->approval?->catatan ?? '-';
                    @endphp
                    <tr>
                        <td style="font-size:12px;color:#9aa0a6;font-weight:600;">#{{ $p->id_peminjaman }}</td>
                        <td class="fw-semibold">{{ $p->peminjam?->nama ?? '-' }}</td>
                        <td style="font-size:12px;color:#6b6b6b;">{{ $p->peminjam?->divisi ?? '-' }}</td>
                        <td>{{ $p->aset?->nama_aset ?? '-' }}</td>
                        <td style="font-size:12px;color:#9aa0a6;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->format('d M Y') }}
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p->tgl_pinjam)->format('d M Y') }}
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p->tgl_rencana_kembali)->format('d M Y') }}
                            @if ($terlambat && !$p->tgl_kembali_aktual)
                                <span class="badge-terlambat">Terlambat</span>
                            @endif
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            @if ($p->tgl_kembali_aktual)
                                {{ \Carbon\Carbon::parse($p->tgl_kembali_aktual)->format('d M Y') }}
                                @if ($terlambat)
                                    <span class="badge-terlambat">Terlambat</span>
                                @endif
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="cluster-dot cluster-{{ strtolower($cl) }}">{{ $cl }}</span>
                        </td>
                        <td>
                            <span class="badge-status {{ $badgeClass }}">{{ $p->status }}</span>
                        </td>
                        <td>
                            <span class="catatan-cell" title="{{ $catatan }}">{{ $catatan }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-riwayat">
                                <div class="empty-icon"><i class="bi bi-clock-history"></i></div>
                                <p class="fw-semibold mb-1">Belum ada riwayat peminjaman.</p>
                                <p style="font-size:13px;">Coba ubah filter atau belum ada data yang tersedia.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
        <div style="padding:14px 18px;border-top:1px solid #e8eaed;">
            {{ $riwayat->links() }}
        </div>
        @endif
    </div>

@endsection

