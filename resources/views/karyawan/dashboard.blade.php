@extends('layouts.karyawan')

@section('title', 'Dashboard Karyawan — Batam Pos')

@section('page-title', 'Dashboard karyawan')

@section('content')

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Sedang dipinjam</div>
                <div class="stat-value blue">{{ $stats['sedang_dipinjam'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Menunggu approval</div>
                <div class="stat-value orange">{{ $stats['menunggu_approval'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total peminjaman</div>
                <div class="stat-value dark">{{ $stats['total_peminjaman'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Kolom kiri --}}
        <div class="col-lg-6">

            {{-- Peminjaman Aktif --}}
            <div class="panel-card mb-3">
                <div class="panel-title">Peminjaman aktif saya</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Aset</th>
                                <th>Batas kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($peminjaman_aktif as $row)
                            <tr>
                                <td>{{ $row['aset'] }}</td>
                                <td>{{ $row['batas_kembali'] }}</td>
                                <td>
                                    @if (str_contains($row['status'], 'Terlambat'))
                                        <span class="badge-status badge-terlambat">{{ $row['status'] }}</span>
                                    @else
                                        <span class="badge-status badge-aktif">{{ $row['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3" style="font-size:13px;">
                                    Tidak ada peminjaman aktif saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Menunggu Approval --}}
            <div class="panel-card">
                <div class="panel-title">Menunggu approval</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Aset</th>
                                <th>Tgl ajukan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($menunggu_approval as $row)
                            <tr>
                                <td>{{ $row['aset'] }}</td>
                                <td>{{ $row['tgl_ajukan'] }}</td>
                                <td>
                                    <span class="badge-status badge-menunggu">{{ $row['status'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3" style="font-size:13px;">
                                    Tidak ada permintaan yang menunggu approval.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Kolom kanan --}}
        <div class="col-lg-6">

            {{-- Riwayat Terakhir --}}
            <div class="panel-card mb-3">
                <div class="panel-title">Riwayat terakhir</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Aset</th>
                                <th>Kembali</th>
                                <th>Ket.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayat_terakhir as $row)
                            <tr>
                                <td>{{ $row['aset'] }}</td>
                                <td>{{ $row['kembali'] }}</td>
                                <td>
                                    @if (str_contains($row['ket'], 'Terlambat'))
                                        <span class="badge-status badge-tepat-terlambat">{{ $row['ket'] }}</span>
                                    @else
                                        <span class="badge-status badge-tepat">{{ $row['ket'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3" style="font-size:13px;">
                                    Belum ada riwayat pengembalian.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Profil Peminjaman --}}
            <div class="profile-card">
                <div class="panel-title">Profil peminjaman saya</div>

                <div class="profile-row">
                    <span class="profile-label">Total peminjaman</span>
                    <span class="profile-value">{{ $profil['total_peminjaman'] }} kali</span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Rata-rata durasi</span>
                    <span class="profile-value">
                        {{ $profil['rata_durasi'] > 0 ? $profil['rata_durasi'] . ' hari' : '-' }}
                    </span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Tingkat keterlambatan</span>
                    <span class="profile-value">
                        {{ $profil['tingkat_keterlambatan'] }}
                        @if ($profil['total_peminjaman'] > 0)
                            <span class="profile-good ms-1">
                                <i class="bi bi-check-circle-fill"></i> {{ $profil['keterangan'] }}
                            </span>
                        @endif
                    </span>
                </div>
            </div>

        </div>
    </div>

@endsection
