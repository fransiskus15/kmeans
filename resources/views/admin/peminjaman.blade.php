@extends('layouts.admin')

@section('title', 'Konfirmasi Pengembalian Aset — Admin')

@section('page-title', 'Konfirmasi pengembalian aset')
@section('page-subtitle', 'Admin Aset — FR-06')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-3" style="font-size:13px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabel peminjaman berlangsung --}}
    <div class="panel-card mb-4">
        <div class="panel-title">Peminjaman sedang berlangsung — pilih untuk dikonfirmasi</div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Aset</th>
                        <th>Tgl pinjam</th>
                        <th>Batas kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peminjaman as $row)
                    <tr class="@if ($row['terlambat_hari'] > 0 && $row['status'] !== 'Dikembalikan') row-terlambat @endif @if ($selected && $selected['id'] === $row['id']) row-selected @endif">
                        <td class="fw-semibold">{{ $row['id'] }}</td>
                        <td>{{ $row['peminjam'] }}</td>
                        <td>{{ $row['aset'] }}</td>
                        <td>{{ $row['tanggal_pinjam'] }}</td>
                        <td>{{ $row['batas_kembali'] }}</td>
                        <td>
                            @if ($row['status'] === 'Dikembalikan')
                                <span class="badge-pill badge-dikembalikan">{{ $row['status'] }}</span>
                            @elseif ($row['terlambat_hari'] > 0)
                                <span class="status-terlambat-text">Terlambat {{ $row['terlambat_hari'] }}hr</span>
                            @else
                                <span class="badge-pill badge-dipinjam">{{ $row['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['status'] === 'Dikembalikan')
                                <span class="text-selesai">Sudah selesai</span>
                            @else
                                <a href="{{ route('admin.peminjaman', ['pilih' => $row['id']]) }}" class="link-action">
                                    Konfirmasi kembali
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-legend">
            Baris merah = peminjaman yang sudah melewati batas waktu pengembalian
        </div>
    </div>

    {{-- Form konfirmasi pengembalian --}}
    @if ($selected)
    <div class="panel-card">
        <div class="panel-title mb-3">
            Form konfirmasi pengembalian — {{ $selected['id'] }} ({{ $selected['peminjam'] }})
        </div>

        <form action="{{ route('admin.peminjaman.konfirmasi') }}" method="POST">
            @csrf
            <input type="hidden" name="peminjaman_id" value="{{ $selected['id'] }}">

            <div class="row g-4">
                {{-- Detail peminjaman --}}
                <div class="col-lg-6">
                    <div class="detail-section-title">Detail peminjaman</div>

                    <div class="detail-row">
                        <span class="detail-label">ID Peminjaman</span>
                        <span class="detail-value">{{ $selected['id'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Peminjam</span>
                        <span class="detail-value">{{ $selected['peminjam'] }} — {{ $selected['divisi'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Aset dipinjam</span>
                        <span class="detail-value">{{ $selected['aset'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nilai aset</span>
                        <span class="detail-value">Rp {{ number_format($selected['nilai_aset'], 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tanggal pinjam</span>
                        <span class="detail-value">{{ $selected['tanggal_pinjam'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Batas kembali</span>
                        <span class="detail-value">{{ $selected['batas_kembali'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Cluster peminjam</span>
                        <span class="detail-value">
                            <span class="badge-cluster">{{ $selected['cluster'] }}</span>
                        </span>
                    </div>

                    <div class="mt-3">
                        @if ($selected['terlambat_hari'] > 0)
                            <div class="warning-alert">
                                Pengembalian terlambat {{ $selected['terlambat_hari'] }} hari. Data F3 (keterlambatan) peminjam akan diperbarui otomatis setelah konfirmasi diklik.
                            </div>
                        @else
                            <div class="success-alert">
                                Pengembalian tepat waktu. Data F2 (durasi) dan F3 (keterlambatan) peminjam akan diperbarui otomatis setelah konfirmasi diklik.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Data pengembalian aktual --}}
                <div class="col-lg-6">
                    <div class="detail-section-title">Data pengembalian aktual</div>

                    <div class="mb-3">
                        <label for="tanggal_kembali_aktual" class="form-label-custom">Tanggal kembali aktual</label>
                        <input type="date" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual"
                            class="form-control form-control-custom"
                            value="{{ old('tanggal_kembali_aktual', $selected['tanggal_kembali_default']) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="kondisi_aset" class="form-label-custom">Kondisi aset saat dikembalikan</label>
                        <select id="kondisi_aset" name="kondisi_aset" class="form-select form-select-custom" required>
                            <option value="Baik — tidak ada kerusakan" {{ old('kondisi_aset', 'Baik — tidak ada kerusakan') === 'Baik — tidak ada kerusakan' ? 'selected' : '' }}>
                                Baik — tidak ada kerusakan
                            </option>
                            <option value="Perlu perawatan" {{ old('kondisi_aset') === 'Perlu perawatan' ? 'selected' : '' }}>
                                Perlu perawatan
                            </option>
                            <option value="Rusak" {{ old('kondisi_aset') === 'Rusak' ? 'selected' : '' }}>
                                Rusak
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="catatan_pengembalian" class="form-label-custom">Catatan pengembalian (opsional)</label>
                        <textarea id="catatan_pengembalian" name="catatan_pengembalian" class="form-control form-note" rows="4"
                            placeholder="catatan kondisi atau keterangan tambahan dari Admin Aset...">{{ old('catatan_pengembalian') }}</textarea>
                    </div>

                    <div class="info-alert mb-3">
                        Setelah konfirmasi: status aset kembali menjadi 'Tersedia' dan status peminjaman menjadi 'Dikembalikan'.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-konfirmasi">
                            Konfirmasi pengembalian
                        </button>
                        <a href="{{ route('admin.peminjaman') }}" class="btn-cancel">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endif
@endsection
