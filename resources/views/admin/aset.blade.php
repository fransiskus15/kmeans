@extends('layouts.admin')

@section('title', 'Manajemen Data Aset — Admin')

@section('page-title', 'Manajemen data aset')
@section('page-subtitle', 'Admin Aset')

@section('topbar-actions')
    <a href="#" class="btn-tambah">
        <i class="bi bi-plus-lg"></i> Tambah aset
    </a>
@endsection

@section('content')

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Total aset</div>
                <div class="stat-value dark">{{ $stats['total_aset'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Tersedia</div>
                <div class="stat-value green">{{ $stats['tersedia'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Dipinjam</div>
                <div class="stat-value blue">{{ $stats['dipinjam'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Perbaikan</div>
                <div class="stat-value orange">{{ $stats['perbaikan'] }}</div>
            </div>
        </div>
    </div>

    {{-- Tabel Aset --}}
    <div class="panel-card">
        <div class="filter-bar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" placeholder="Cari nama / kode aset...">
            </div>
            <select class="filter-select">
                <option>Semua kategori</option>
                @foreach ($kategori as $kat)
                    <option>{{ $kat }}</option>
                @endforeach
            </select>
            <select class="filter-select">
                <option>Semua status</option>
                <option>Tersedia</option>
                <option>Dipinjam</option>
                <option>Perbaikan</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama aset</th>
                        <th>Kategori</th>
                        <th>Nilai (Rp)</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aset as $row)
                    <tr>
                        <td>{{ $row['kode'] }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td>{{ $row['kategori'] }}</td>
                        <td>{{ number_format($row['nilai'], 0, ',', '.') }}</td>
                        <td>
                            @if ($row['kondisi'] === 'Baik')
                                <span class="badge-pill badge-baik">{{ $row['kondisi'] }}</span>
                            @else
                                <span class="badge-pill badge-perawatan">{{ $row['kondisi'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['status'] === 'Dipinjam')
                                <span class="badge-pill badge-dipinjam">{{ $row['status'] }}</span>
                            @elseif ($row['status'] === 'Tersedia')
                                <span class="badge-pill badge-tersedia">{{ $row['status'] }}</span>
                            @else
                                <span class="badge-pill badge-perbaikan">{{ $row['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="action-btn delete" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            Menampilkan {{ count($aset) }} dari {{ $stats['total_aset'] }} aset
        </div>
    </div>

@endsection
