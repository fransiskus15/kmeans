@extends('layouts.admin')

@section('title', 'Konfirmasi Pengembalian Aset — Admin')

@section('page-title', 'Konfirmasi pengembalian aset')
@section('page-subtitle', 'Admin Aset — FR-06')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-3" style="font-size:13px;border-radius:8px;">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-3" style="font-size:13px;border-radius:8px;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Tabel peminjaman berlangsung --}}
    <div class="panel-card mb-4">
        {{-- Baris 1: Judul + Filter Tanggal --}}
        <div class="d-flex align-items-center flex-wrap gap-3 mb-2">
            <div class="panel-title mb-0" style="flex-shrink:0;">Peminjaman sedang berlangsung — pilih untuk dikonfirmasi</div>
            <form method="GET" action="{{ route('admin.peminjaman') }}" id="formFilter"
                class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                @if(request('pilih'))
                    <input type="hidden" name="pilih" value="{{ request('pilih') }}">
                @endif
                <input type="hidden" name="peminjam" id="hiddenPeminjam" value="{{ $selectedPeminjam }}">
                <input type="date" name="tgl_dari" class="form-control form-control-custom"
                    value="{{ $tglDari }}" style="min-width:140px;font-size:12px;padding:5px 8px;">
                <span style="font-size:12px;color:#888;">s/d</span>
                <input type="date" name="tgl_sampai" class="form-control form-control-custom"
                    value="{{ $tglSampai }}" style="min-width:140px;font-size:12px;padding:5px 8px;">
                <button type="submit" class="btn btn-konfirmasi" style="padding:5px 14px;font-size:12px;">
                    <i class="bi bi-funnel-fill me-1"></i>Filter
                </button>
                @if($tglDari || $tglSampai || $selectedPeminjam)
                    <a href="{{ route('admin.peminjaman') }}" class="btn-cancel" style="padding:5px 14px;font-size:12px;">Reset</a>
                @endif
            </form>
        </div>

        {{-- Baris 2: Filter Peminjam + Ringkasan --}}
        <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <label style="font-size:12px;color:#555;white-space:nowrap;margin-bottom:0;">Filter peminjam:</label>
                <select id="selectPeminjam" class="form-select form-select-custom"
                    style="min-width:200px;font-size:12px;padding:5px 10px;"
                    onchange="document.getElementById('hiddenPeminjam').value=this.value; document.getElementById('formFilter').submit();">
                    <option value="">— Semua peminjam —</option>
                    @foreach($peminjamList as $nama)
                        <option value="{{ $nama }}" {{ $selectedPeminjam === $nama ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($selectedPeminjam)
                <div style="font-size:12px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:6px;padding:5px 12px;color:#3730a3;">
                    <i class="bi bi-person-fill me-1"></i>
                    <strong>{{ $selectedPeminjam }}</strong> — total
                    <strong>{{ $rawList->total() }}</strong> peminjaman ditemukan
                    @php
                        $aktifCount = $peminjaman->filter(fn($r) => in_array($r['status_raw'], ['Disetujui','Dipinjam']))->count();
                        $selesaiCount = $peminjaman->filter(fn($r) => $r['status'] === 'Dikembalikan')->count();
                    @endphp
                    <span class="ms-2" style="color:#059669;">✓ {{ $selesaiCount }} selesai</span>
                    @if($aktifCount > 0)
                        <span class="ms-1" style="color:#d97706;">● {{ $aktifCount }} aktif</span>
                    @endif
                </div>
            @else
                <div style="font-size:12px;color:#888;">
                    Total <strong>{{ $rawList->total() }}</strong> data peminjaman
                </div>
            @endif
        </div>

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
                    @forelse ($peminjaman as $row)
                    <tr class="@if ($row['terlambat_hari'] > 0 && $row['status'] !== 'Dikembalikan') row-terlambat @endif @if ($selected && $selected['id'] === $row['id']) row-selected @endif">
                        <td class="fw-semibold">{{ $row['id'] }}</td>
                        <td>{{ $row['peminjam'] }}</td>
                        <td>{{ $row['aset'] }}</td>
                        <td>{{ $row['tanggal_pinjam'] }}</td>
                        <td>{{ $row['batas_kembali'] }}</td>
                        <td>
                            @if ($row['status'] === 'Dikembalikan')
                                <span class="badge-pill badge-dikembalikan">{{ $row['status'] }}</span>
                            @elseif ($row['status_raw'] === 'Disetujui')
                                <span class="badge-pill" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;">Dipesan</span>
                            @elseif ($row['terlambat_hari'] > 0)
                                <span class="status-terlambat-text">Terlambat {{ $row['terlambat_hari'] }}hr</span>
                            @else
                                <span class="badge-pill badge-dipinjam">{{ $row['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['status'] === 'Dikembalikan')
                                <span class="text-selesai">Sudah selesai</span>
                            @elseif ($row['status_raw'] === 'Disetujui')
                                {{-- Aset sudah dipesan/disetujui HR, tunggu konfirmasi pengambilan oleh Admin --}}
                                <form method="POST" action="{{ route('admin.peminjaman.ambil', $row['id']) }}"
                                    onsubmit="return confirm('Konfirmasi bahwa karyawan sudah mengambil aset ini? Status aset akan berubah menjadi Dipinjam.')">
                                    @csrf
                                    <button type="submit" class="btn btn-konfirmasi" style="padding:4px 12px;font-size:12px;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Konfirmasi Pengambilan
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.peminjaman', array_filter(['pilih' => $row['id'], 'tgl_dari' => $tglDari, 'tgl_sampai' => $tglSampai])) }}" class="link-action">
                                    Konfirmasi kembali
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada peminjaman aktif saat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-legend">
            Baris merah = peminjaman yang sudah melewati batas waktu pengembalian
        </div>

        <div class="mt-3" style="font-size:13px;">
            <style>
                .pagination { font-size: 13px !important; }
                .pagination .page-link { padding: 4px 10px !important; font-size: 13px !important; }
                .pagination li:first-child,
                .pagination li:last-child { display: none !important; }
            </style>
            {{ $rawList->links() }}
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
                            <option value="Baik" {{ old('kondisi_aset', 'Baik') === 'Baik' ? 'selected' : '' }}>
                                Baik — tidak ada kerusakan
                            </option>
                            <option value="Perlu Perawatan" {{ old('kondisi_aset') === 'Perlu Perawatan' ? 'selected' : '' }}>
                                Perlu perawatan
                            </option>
                            <option value="Rusak" {{ old('kondisi_aset') === 'Rusak' ? 'selected' : '' }}>
                                Rusak
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label-custom">Catatan pengembalian (opsional)</label>
                        <textarea id="catatan" name="catatan" class="form-control form-note" rows="4"
                            placeholder="catatan kondisi atau keterangan tambahan dari Admin Aset...">{{ old('catatan') }}</textarea>
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
