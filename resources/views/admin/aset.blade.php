@extends('layouts.admin')

@section('title', 'Manajemen Data Aset — Admin')

@section('page-title', 'Manajemen data aset')
@section('page-subtitle', 'Admin Aset')

@section('topbar-actions')
    <button type="button" class="btn-tambah" data-bs-toggle="modal" data-bs-target="#modalAset">
        <i class="bi bi-plus-lg"></i> Tambah aset
    </button>
@endsection

@section('content')

    {{-- Flash messages --}}
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

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Total aset</div>
                <div class="stat-value dark">{{ $statistik['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Tersedia</div>
                <div class="stat-value green">{{ $statistik['tersedia'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Dipinjam</div>
                <div class="stat-value blue">{{ $statistik['dipinjam'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Perbaikan</div>
                <div class="stat-value orange">{{ $statistik['perbaikan'] }}</div>
            </div>
        </div>
    </div>

    {{-- Tabel Aset --}}
    <div class="panel-card">
        <div class="filter-bar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama / kode aset...">
            </div>
            <select class="filter-select" id="filterKategori">
                <option value="">Semua kategori</option>
                @foreach ($kategoriList as $kat)
                    <option>{{ $kat }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterStatus">
                <option value="">Semua status</option>
                <option>Tersedia</option>
                <option>Dipinjam</option>
                <option>Perbaikan</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table table-custom" id="tabelAset">
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
                    @forelse ($asetList as $row)
                    <tr data-nama="{{ strtolower($row->nama_aset) }}"
                        data-kode="{{ strtolower($row->kode_aset) }}"
                        data-kategori="{{ $row->kategori }}"
                        data-status="{{ $row->status }}">
                        <td class="fw-semibold" style="font-size:12px; color:#6b6b6b;">{{ $row->kode_aset }}</td>
                        <td>{{ $row->nama_aset }}</td>
                        <td>{{ $row->kategori }}</td>
                        <td>{{ number_format($row->nilai_perolehan, 0, ',', '.') }}</td>
                        <td>
                            @if ($row->kondisi === 'Baik')
                                <span class="badge-pill badge-baik">{{ $row->kondisi }}</span>
                            @elseif ($row->kondisi === 'Rusak')
                                <span class="badge-pill badge-perbaikan">{{ $row->kondisi }}</span>
                            @else
                                <span class="badge-pill badge-perawatan">{{ $row->kondisi }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($row->status === 'Dipinjam')
                                <span class="badge-pill badge-dipinjam">{{ $row->status }}</span>
                            @elseif ($row->status === 'Tersedia')
                                <span class="badge-pill badge-tersedia">{{ $row->status }}</span>
                            @else
                                <span class="badge-pill badge-perbaikan">{{ $row->status }}</span>
                            @endif
                        </td>
                        <td>
                            {{-- Tombol Edit --}}
                            <button type="button" class="action-btn" title="Edit"
                                data-bs-toggle="modal" data-bs-target="#modalAset"
                                data-edit-id="{{ $row->id_aset }}"
                                data-edit-kode="{{ $row->kode_aset }}"
                                data-edit-nama="{{ $row->nama_aset }}"
                                data-edit-kategori="{{ $row->kategori }}"
                                data-edit-nilai="{{ $row->nilai_perolehan }}"
                                data-edit-kondisi="{{ $row->kondisi }}"
                                data-edit-lokasi="{{ $row->lokasi }}"
                                data-edit-deskripsi="{{ $row->deskripsi }}">
                                <i class="bi bi-pencil"></i>
                            </button>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('admin.aset.hapus', $row->id_aset) }}"
                                  method="POST" style="display:inline;"
                                  onsubmit="return confirm('Hapus aset {{ $row->nama_aset }}? Tindakan ini tidak bisa dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada data aset. <button type="button" class="btn btn-sm btn-outline-dark ms-2"
                                data-bs-toggle="modal" data-bs-target="#modalAset">Tambah sekarang</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            Menampilkan {{ $asetList->count() }} dari {{ $statistik['total'] }} aset
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $asetList->links() }}
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         MODAL TAMBAH / EDIT ASET
    ════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalAset" tabindex="-1" aria-labelledby="modalAsetLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; border:none;">
                <div class="modal-header" style="border-bottom:1px solid #f0f2f5; padding:20px 24px;">
                    <h5 class="modal-title fw-semibold" id="modalAsetLabel">
                        <i class="bi bi-box-seam me-2"></i>
                        <span id="modalAsetTitle">Tambah Aset Baru</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formAset" method="POST" action="{{ route('admin.aset.simpan') }}">
                    @csrf
                    {{-- Field method override untuk PUT (edit) --}}
                    <span id="methodField"></span>

                    <div class="modal-body" style="padding:24px;">
                        <div class="row g-3">

                            {{-- Kode aset --}}
                            <div class="col-md-4">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Kode Aset <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="field_kode_aset" name="kode_aset"
                                       class="form-control form-control-sm"
                                       placeholder="Cth: LAP-001" maxlength="20" required>
                                <div class="form-text" id="kodeHint" style="font-size:11px;">
                                    Kode unik, maks 20 karakter.
                                </div>
                            </div>

                            {{-- Nama aset --}}
                            <div class="col-md-8">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Nama Aset <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="field_nama_aset" name="nama_aset"
                                       class="form-control form-control-sm"
                                       placeholder="Nama lengkap aset" maxlength="150" required>
                            </div>

                            {{-- Kategori (enum DB) --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select id="field_kategori" name="kategori" class="form-select form-select-sm" required>
                                    <option value="">— Pilih kategori —</option>
                                    <option value="Kamera & Foto">Kamera &amp; Foto</option>
                                    <option value="Laptop & Komputer">Laptop &amp; Komputer</option>
                                    <option value="Mikrofon & Audio">Mikrofon &amp; Audio</option>
                                    <option value="Drone & Video">Drone &amp; Video</option>
                                    <option value="Tripod & Aksesori">Tripod &amp; Aksesori</option>
                                    <option value="Kendaraan">Kendaraan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            {{-- Nilai perolehan --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Nilai Perolehan (Rp) <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="field_nilai_perolehan" name="nilai_perolehan"
                                       class="form-control form-control-sm"
                                       placeholder="0" min="0" step="1000" required>
                            </div>

                            {{-- Kondisi --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Kondisi <span class="text-danger">*</span>
                                </label>
                                <select id="field_kondisi" name="kondisi" class="form-select form-select-sm" required>
                                    <option value="">— Pilih kondisi —</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Perlu Perawatan">Perlu Perawatan</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>

                            {{-- Lokasi --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Lokasi Penyimpanan
                                </label>
                                <input type="text" id="field_lokasi" name="lokasi"
                                       class="form-control form-control-sm"
                                       placeholder="Cth: Ruang IT Lt.2" maxlength="100">
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#6b6b6b;">
                                    Deskripsi
                                </label>
                                <textarea id="field_deskripsi" name="deskripsi"
                                          class="form-control form-control-sm" rows="3"
                                          placeholder="Spesifikasi atau catatan tambahan (opsional)"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer" style="border-top:1px solid #f0f2f5; padding:16px 24px; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-dark" id="btnSubmitAset">
                            <i class="bi bi-floppy me-1"></i>
                            <span id="submitLabel">Simpan Aset</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ── Client-side search & filter ──────────────────────────────────
    const searchInput     = document.getElementById('searchInput');
    const filterKategori  = document.getElementById('filterKategori');
    const filterStatus    = document.getElementById('filterStatus');
    const rows            = document.querySelectorAll('#tabelAset tbody tr[data-nama]');

    function applyFilter() {
        const q  = searchInput.value.toLowerCase();
        const kat = filterKategori.value;
        const st  = filterStatus.value;

        rows.forEach(row => {
            const matchQ   = !q  || row.dataset.nama.includes(q) || row.dataset.kode.includes(q);
            const matchKat = !kat || row.dataset.kategori === kat;
            const matchSt  = !st  || row.dataset.status === st;
            row.style.display = (matchQ && matchKat && matchSt) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilter);
    filterKategori.addEventListener('change', applyFilter);
    filterStatus.addEventListener('change', applyFilter);

    // ── Modal tambah / edit ──────────────────────────────────────────
    const modalEl     = document.getElementById('modalAset');
    const form        = document.getElementById('formAset');
    const methodField = document.getElementById('methodField');
    const titleEl     = document.getElementById('modalAsetTitle');
    const submitLabel = document.getElementById('submitLabel');
    const kodeField   = document.getElementById('field_kode_aset');
    const kodeHint    = document.getElementById('kodeHint');

    // Reset ke mode "Tambah"
    modalEl.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;

        if (btn && btn.dataset.editId) {
            // Mode Edit
            titleEl.textContent = 'Edit Aset';
            submitLabel.textContent = 'Simpan Perubahan';

            const id = btn.dataset.editId;
            form.action = `/admin/aset/${id}`;
            methodField.innerHTML = `<input type="hidden" name="_method" value="PUT">`;

            // Isi field dengan data aset
            document.getElementById('field_kode_aset').value       = btn.dataset.editKode;
            document.getElementById('field_nama_aset').value       = btn.dataset.editNama;
            document.getElementById('field_nilai_perolehan').value = btn.dataset.editNilai;
            document.getElementById('field_kondisi').value         = btn.dataset.editKondisi;
            document.getElementById('field_lokasi').value          = btn.dataset.editLokasi ?? '';
            document.getElementById('field_deskripsi').value       = btn.dataset.editDeskripsi ?? '';

            // Set select kategori (cocokkan nilai enum)
            const katSel = document.getElementById('field_kategori');
            katSel.value = btn.dataset.editKategori;
            // fallback jika tidak cocok
            if (!katSel.value) katSel.value = '';

            // Kode tidak bisa diubah saat edit
            kodeField.setAttribute('readonly', true);
            kodeHint.textContent = 'Kode aset tidak dapat diubah.';

        } else {
            // Mode Tambah
            titleEl.textContent = 'Tambah Aset Baru';
            submitLabel.textContent = 'Simpan Aset';
            form.action = "{{ route('admin.aset.simpan') }}";
            methodField.innerHTML = '';
            form.reset();

            kodeField.removeAttribute('readonly');
            kodeHint.textContent = 'Kode unik, maks 20 karakter.';
        }
    });
</script>
@endpush
