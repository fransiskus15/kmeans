@extends('layouts.admin')

@section('title', 'Laporan — Admin Aset')

@section('page-title', 'Laporan Admin Aset')

@section('content')
<div class="container-fluid">

    {{-- ── Filter Form ─────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h6 class="fw-bold mb-3">
                Filter laporan
            </h6>

            <form method="GET" action="{{ route('laporan.index') }}">

                <div class="row g-2">

                    <div class="col-md-3">
                        <label class="form-label">Periode dari</label>
                        <input type="date" name="periode_from" class="form-control"
                               value="{{ $periodeFrom }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Periode sampai</label>
                        <input type="date" name="periode_to" class="form-control"
                               value="{{ $periodeTo }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kategori aset</label>
                        <select name="kategori" class="form-select">
                            <option value="all" {{ $kategori === 'all' ? 'selected' : '' }}>Semua kategori</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all"        {{ $status === 'all'        ? 'selected' : '' }}>Semua status</option>
                            <option value="tepat_waktu"{{ $status === 'tepat_waktu'? 'selected' : '' }}>Tepat waktu</option>
                            <option value="terlambat"  {{ $status === 'terlambat'  ? 'selected' : '' }}>Terlambat</option>
                            <option value="menunggu"   {{ $status === 'menunggu'   ? 'selected' : '' }}>Menunggu</option>
                        </select>
                    </div>

                </div>

                <div class="mt-3 d-flex gap-2 flex-wrap">

                    <button type="submit" class="btn btn-outline-dark">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>

                    <a href="{{ route('laporan.pdf') }}" class="btn btn-outline-dark" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
                    </a>

                    <a href="{{ route('laporan.excel') }}" class="btn btn-outline-dark">
                        <i class="bi bi-file-earmark-excel"></i> Ekspor Excel
                    </a>

                </div>

            </form>

        </div>
    </div>


    {{-- ── Statistik Cards ──────────────────────────────────────────── --}}
    <div class="row mt-4 g-3">

        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <small class="text-muted">Total transaksi</small>
                    <h3 class="fw-bold mb-0">{{ $statistik['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <small class="text-muted">Tepat waktu</small>
                    <h3 class="text-success fw-bold mb-0">{{ $statistik['tepat_waktu'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <small class="text-muted">Terlambat</small>
                    <h3 class="text-danger fw-bold mb-0">{{ $statistik['terlambat'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <small class="text-muted">Tingkat terlambat</small>
                    <h3 class="text-warning fw-bold mb-0">{{ $statistik['pct_terlambat'] }}%</h3>
                </div>
            </div>
        </div>

    </div>


    {{-- ── Tabel Laporan ────────────────────────────────────────────── --}}
    <div class="card mt-4 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Peminjam</th>
                            <th>Aset</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Durasi (hari)</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($peminjaman as $item)
                        <tr>
                            {{-- Nama peminjam melalui relasi --}}
                            <td>{{ $item->peminjam->nama ?? '-' }}</td>

                            {{-- Nama aset melalui relasi --}}
                            <td>{{ $item->aset->nama_aset ?? '-' }}</td>

                            {{-- Tanggal pinjam --}}
                            <td>
                                {{ $item->tgl_pengajuan
                                    ? \Carbon\Carbon::parse($item->tgl_pengajuan)->format('d M Y')
                                    : '-' }}
                            </td>

                            {{-- Tanggal kembali aktual --}}
                            <td>
                                {{ $item->tgl_kembali_aktual
                                    ? \Carbon\Carbon::parse($item->tgl_kembali_aktual)->format('d M Y')
                                    : '-' }}
                            </td>

                            {{-- Durasi hari --}}
                            <td>
                                @if ($item->tgl_kembali_aktual && $item->tgl_pengajuan)
                                    {{ \Carbon\Carbon::parse($item->tgl_pengajuan)
                                        ->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_aktual)) }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Badge status terlambat/tepat waktu --}}
                            <td>
                                @php
                                    $terlambat = $item->tgl_kembali_aktual &&
                                        \Carbon\Carbon::parse($item->tgl_kembali_aktual)
                                            ->gt(\Carbon\Carbon::parse($item->tgl_rencana_kembali));
                                @endphp

                                @if ($terlambat)
                                    <span class="badge bg-danger">Terlambat</span>
                                @elseif ($item->tgl_kembali_aktual)
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @else
                                    <span class="badge bg-secondary">Belum Kembali</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data untuk periode dan filter yang dipilih.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Menampilkan {{ $peminjaman->count() }} dari {{ $peminjaman->total() }} transaksi
            </span>
            {{ $peminjaman->withQueryString()->links() }}
        </div>

    </div>

</div>
@endsection