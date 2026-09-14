@extends('layouts.karyawan')

@section('title', 'Riwayat Peminjaman Saya')

@section('page-title', 'Riwayat Peminjaman Saya')

@section('content')

@push('styles')
<style>
    /* ── Stat cards ───────────────────────────────────────── */
    .rstat-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .rstat-card {
        flex: 1;
        min-width: 110px;
        background: #fff;
        border: 1px solid #e0e0db;
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
    }
    .rstat-val {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .rstat-label {
        font-size: 11px;
        color: #9aa0a6;
        font-weight: 500;
    }
    .rv-total   { color: #1a1a1a; }
    .rv-menunggu{ color: #92400E; }
    .rv-aktif   { color: #1e40af; }
    .rv-kembali { color: #065F46; }
    .rv-tolak   { color: #991B1B; }

    /* ── Filter bar ───────────────────────────────────────── */
    .filter-bar {
        background: #fff;
        border: 1px solid #e0e0db;
        border-radius: 12px;
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
        font-size: 11px; font-weight: 600;
        color: #9aa0a6; text-transform: uppercase; letter-spacing: 0.04em;
    }
    .filter-bar input,
    .filter-bar select {
        border: 1px solid #dde0e5; border-radius: 8px;
        padding: 7px 11px; font-size: 13px; color: #1a1a1a;
        background: #fafafa; outline: none; transition: border-color .15s;
    }
    .filter-bar input:focus,
    .filter-bar select:focus { border-color: #1a1a1a; background: #fff; }
    .btn-filter {
        background: #1a1a1a; color: #fff; border: none;
        border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .15s;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-filter:hover { background: #333; }
    .btn-reset {
        background: #f5f5f3; color: #4a4a4a;
        border: 1px solid #e0e0db; border-radius: 8px;
        padding: 8px 14px; font-size: 13px; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        transition: background .15s;
    }
    .btn-reset:hover { background: #ebebeb; color: #1a1a1a; }

    /* ── Status pill ──────────────────────────────────────── */
    .status-pill {
        display: inline-flex; align-items: center;
        padding: 4px 12px; border-radius: 20px;
        font-size: 11.5px; font-weight: 600; white-space: nowrap;
    }
    .pill-menunggu    { background:#FEF3C7; color:#92400E; }
    .pill-disetujui   { background:#D1FAE5; color:#065F46; }
    .pill-dipinjam    { background:#DBEAFE; color:#1e40af; }
    .pill-dikembalikan{ background:#E0F2FE; color:#0369a1; }
    .pill-ditolak     { background:#FEE2E2; color:#991B1B; }
    .pill-terlambat   { background:#FEE2E2; color:#991B1B; }
    .pill-lainnya     { background:#F3F4F6; color:#374151; }

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
        padding: 12px 12px; border-bottom: 1px solid #f0f1f3;
        vertical-align: middle; font-size: 13px;
    }
    .table-riwayat tr:last-child td { border-bottom: none; }
    .table-riwayat tr:hover td { background: #fafaf8; }

    /* ── Empty state ──────────────────────────────────────── */
    .empty-riwayat { text-align:center; padding: 64px 24px; color: #9AA0A6; }
    .empty-riwayat .ei { font-size: 54px; opacity: .3; margin-bottom: 14px; }
</style>
@endpush

    {{-- Flash --}}
    @if (session('success'))
    <div style="background:#D1FAE5;border:1px solid #6EE7B7;border-radius:10px;padding:10px 16px;font-size:13px;color:#065F46;display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Stat cards --}}
    <div class="rstat-row">
        <div class="rstat-card">
            <div class="rstat-val rv-total">{{ $ringkasan['total'] }}</div>
            <div class="rstat-label">Total</div>
        </div>
        <div class="rstat-card">
            <div class="rstat-val rv-menunggu">{{ $ringkasan['menunggu'] }}</div>
            <div class="rstat-label">Menunggu</div>
        </div>
        <div class="rstat-card">
            <div class="rstat-val rv-aktif">{{ $ringkasan['aktif'] }}</div>
            <div class="rstat-label">Aktif</div>
        </div>
        <div class="rstat-card">
            <div class="rstat-val rv-kembali">{{ $ringkasan['dikembalikan'] }}</div>
            <div class="rstat-label">Dikembalikan</div>
        </div>
        <div class="rstat-card">
            <div class="rstat-val rv-tolak">{{ $ringkasan['ditolak'] }}</div>
            <div class="rstat-label">Ditolak</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('karyawan.riwayat') }}" class="filter-bar">
        <div class="filter-group grow">
            <label for="cari-aset">Cari Nama Aset</label>
            <input type="text" id="cari-aset" name="cari"
                   value="{{ request('cari') }}"
                   placeholder="Cari nama aset...">
        </div>
        <div class="filter-group">
            <label for="filter-status">Status</label>
            <select id="filter-status" name="status" style="min-width:160px;">
                <option value="">Semua Status</option>
                @foreach (['Menunggu Persetujuan','Disetujui','Dipinjam','Dikembalikan','Dikembalikan Terlambat','Ditolak'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-bulan">Bulan</label>
            <input type="month" id="filter-bulan" name="bulan"
                   value="{{ request('bulan') }}" style="min-width:140px;">
        </div>
        <div style="display:flex;gap:6px;align-items:flex-end;">
            <button type="submit" class="btn-filter">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="{{ route('karyawan.riwayat') }}" class="btn-reset">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="panel-card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 18px 12px;border-bottom:1px solid #e8eaed;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div style="font-weight:600;font-size:14px;">Seluruh Riwayat Peminjaman Saya</div>
            <span style="font-size:12px;color:#9aa0a6;">
                {{ $riwayat->total() }} data ditemukan
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-riwayat mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Tgl Ajuan</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Kembali Aktual</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $i => $p)
                    @php
                        $batas     = \Carbon\Carbon::parse($p->tgl_rencana_kembali);
                        $terlambat = $p->isTerlambat();

                        $pillClass = match ($p->status) {
                            'Menunggu Persetujuan'   => 'pill-menunggu',
                            'Disetujui'              => 'pill-disetujui',
                            'Dipinjam'               => $terlambat ? 'pill-terlambat' : 'pill-dipinjam',
                            'Dikembalikan'           => 'pill-dikembalikan',
                            'Dikembalikan Terlambat' => 'pill-terlambat',
                            'Ditolak'                => 'pill-ditolak',
                            default                  => 'pill-lainnya',
                        };

                        $statusLabel = match ($p->status) {
                            'Dipinjam' => $terlambat
                                ? 'Terlambat ' . \Carbon\Carbon::now()->diffInDays($batas) . ' hr'
                                : 'Dipinjam',
                            default => $p->status,
                        };
                    @endphp
                    <tr>
                        <td style="font-size:12px;color:#9aa0a6;font-weight:600;">
                            {{ $riwayat->firstItem() + $i }}
                        </td>
                        <td class="fw-semibold">{{ $p->aset?->nama_aset ?? '-' }}</td>
                        <td style="font-size:12px;color:#6b6b6b;">{{ $p->aset?->kategori ?? '-' }}</td>
                        <td style="font-size:12px;color:#9aa0a6;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p->tgl_pengajuan)->format('d M Y') }}
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($p->tgl_pinjam)->format('d M Y') }}
                        </td>
                        <td style="font-size:12px;white-space:nowrap;">
                            {{ $batas->format('d M Y') }}
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
                        <td style="font-size:12px;">
                            @if ($p->tgl_pinjam && $p->tgl_kembali_aktual)
                                {{ \Carbon\Carbon::parse($p->tgl_pinjam)->diffInDays($p->tgl_kembali_aktual) }} hari
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-pill {{ $pillClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-riwayat">
                                <div class="ei"><i class="bi bi-clock-history"></i></div>
                                <p class="fw-semibold mb-1">Belum ada riwayat peminjaman.</p>
                                <p style="font-size:13px;">
                                    @if (request()->hasAny(['cari','status','bulan']))
                                        Tidak ada data yang cocok dengan filter yang dipilih.
                                    @else
                                        Anda belum pernah mengajukan peminjaman aset.
                                    @endif
                                </p>
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

