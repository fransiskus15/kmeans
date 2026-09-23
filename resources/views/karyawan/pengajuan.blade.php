@extends('layouts.karyawan')

@section('title', 'Ajukan Permintaan Peminjaman Aset — Karyawan')

@section('page-title', 'Ajukan Permintaan Peminjaman Aset')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-3" style="font-size:13px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="panel-card">
        <div class="panel-title">Formulir pengajuan peminjaman aset</div>

        <form action="{{ route('karyawan.pengajuan.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="kategori_aset" class="form-label-custom">Kategori aset</label>
                    <select id="kategori_aset" name="kategori_aset" class="form-select form-select-custom" required>
                        @foreach ($kategori_aset as $kategori)
                            <option value="{{ $kategori['id'] }}"
                                {{ old('kategori_aset', $form['kategori_aset']) == $kategori['id'] ? 'selected' : '' }}>
                                {{ $kategori['nama'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="aset_id" class="form-label-custom">Pilih aset (hanya aset tersedia)</label>
                    <select id="aset_id" name="aset_id" class="form-select form-select-custom" required>
                        @foreach ($aset_tersedia as $aset)
                            <option value="{{ $aset['id'] }}"
                                data-kategori="{{ $aset['kategori_id'] }}"
                                {{ old('aset_id', $form['aset_id']) == $aset['id'] ? 'selected' : '' }}>
                                {{ $aset['nama'] }} — {{ $aset['status'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="tanggal_pinjam" class="form-label-custom">Tanggal pinjam</label>
                    <input type="date" id="tanggal_pinjam" name="tanggal_pinjam"
                        class="form-control form-control-custom {{ $errors->has('tanggal_pinjam') ? 'is-invalid' : '' }}"
                        value="{{ old('tanggal_pinjam', $form['tanggal_pinjam']) }}" required>
                    @error('tanggal_pinjam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="tanggal_kembali" class="form-label-custom">
                        Estimasi tanggal kembali
                        <span style="color:#e53e3e; font-weight:600; font-size:11px;">(maks. 7 hari)</span>
                    </label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali"
                        class="form-control form-control-custom {{ $errors->has('tanggal_kembali') ? 'is-invalid' : '' }}"
                        value="{{ old('tanggal_kembali', $form['tanggal_kembali']) }}" required>
                    @error('tanggal_kembali')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="keperluan" class="form-label-custom">Keterangan keperluan peminjaman</label>
                <textarea id="keperluan" name="keperluan" class="form-control form-note" rows="4"
                    placeholder="Jelaskan keperluan peminjaman aset..." required>{{ old('keperluan', $form['keperluan']) }}</textarea>
            </div>

            <div id="infoAlert" class="info-alert mb-4">
                <i class="bi bi-info-circle"></i>
                <span id="infoText">{{ $info_teks }}</span>
            </div>
            <div id="errorDurasi" class="mb-3" style="display:none;
                background:#fde8e8; border:1px solid #f5aca6; border-radius:8px;
                padding:12px 14px; font-size:13px; color:#9b2c2c;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <span id="errorDurasiText"></span>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" id="btnSubmit" class="btn btn-action d-flex align-items-center gap-2">
                    Ajukan permintaan
                    <i class="bi bi-arrow-up-right"></i>
                </button>
                <button type="reset" class="btn btn-cancel">Batal</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const MAKS_DURASI = 7; // maksimal hari peminjaman

    const asetSelect     = document.getElementById('aset_id');
    const kategoriSelect = document.getElementById('kategori_aset');
    const tanggalPinjam  = document.getElementById('tanggal_pinjam');
    const tanggalKembali = document.getElementById('tanggal_kembali');
    const infoText       = document.getElementById('infoText');
    const infoAlert      = document.getElementById('infoAlert');
    const errorDurasi    = document.getElementById('errorDurasi');
    const errorDurasiText= document.getElementById('errorDurasiText');
    const btnSubmit      = document.getElementById('btnSubmit');

    const allAsetOptions = Array.from(asetSelect.options).map(opt => ({
        value: opt.value,
        text: opt.text,
        kategori: opt.dataset.kategori,
    }));

    // ── Hitung durasi (hari) antara dua input tanggal ─────────────────────
    function hitungDurasi() {
        const mulai  = new Date(tanggalPinjam.value);
        const selesai= new Date(tanggalKembali.value);
        if (!tanggalPinjam.value || !tanggalKembali.value || selesai <= mulai) return null;
        return Math.round((selesai - mulai) / (1000 * 60 * 60 * 24));
    }

    // ── Atur batas max tanggal kembali = tanggal pinjam + 7 hari ─────────
    function updateMaxTanggalKembali() {
        if (!tanggalPinjam.value) return;
        const pinjam = new Date(tanggalPinjam.value);
        pinjam.setDate(pinjam.getDate() + MAKS_DURASI);
        const maxStr = pinjam.toISOString().split('T')[0];
        tanggalKembali.setAttribute('max', maxStr);

        // Jika tanggal kembali yang sudah dipilih melebihi max, reset ke max
        if (tanggalKembali.value && tanggalKembali.value > maxStr) {
            tanggalKembali.value = maxStr;
        }

        // Min tanggal kembali = tanggal pinjam + 1
        const minPinjam = new Date(tanggalPinjam.value);
        minPinjam.setDate(minPinjam.getDate() + 1);
        tanggalKembali.setAttribute('min', minPinjam.toISOString().split('T')[0]);
    }

    // ── Validasi durasi dan update UI info/error ──────────────────────────
    function updateInfoAlert() {
        const asetNama = asetSelect.options[asetSelect.selectedIndex]?.text.split(' — ')[0] || 'Aset';
        const durasi   = hitungDurasi();

        if (durasi !== null && durasi > MAKS_DURASI) {
            // Tampilkan error merah, sembunyikan info biru, disable tombol
            errorDurasiText.textContent = `Durasi peminjaman maksimal ${MAKS_DURASI} hari. Durasi yang Anda masukkan: ${durasi} hari. Harap ubah tanggal kembali.`;
            errorDurasi.style.display = 'block';
            infoAlert.style.display   = 'none';
            btnSubmit.disabled        = true;
            btnSubmit.style.opacity   = '0.5';
            btnSubmit.style.cursor    = 'not-allowed';
            tanggalKembali.style.borderColor = '#e53e3e';
        } else {
            // Normal: tampilkan info biru, sembunyikan error
            errorDurasi.style.display = 'none';
            infoAlert.style.display   = 'block';
            btnSubmit.disabled        = false;
            btnSubmit.style.opacity   = '';
            btnSubmit.style.cursor    = '';
            tanggalKembali.style.borderColor = '';

            if (durasi) {
                const sisa = MAKS_DURASI - durasi;
                infoText.textContent = `${asetNama} tersedia. Durasi: ${durasi} hari (sisa ${sisa} hari dari maks. ${MAKS_DURASI} hari). Permintaan akan diteruskan ke HR.`;
            } else {
                infoText.textContent = `${asetNama} tersedia. Maks. peminjaman ${MAKS_DURASI} hari. Permintaan akan diteruskan ke HR untuk disetujui.`;
            }
        }
    }

    // ── Filter aset berdasarkan kategori ─────────────────────────────────
    function filterAsetByKategori() {
        const kategoriId = kategoriSelect.value;
        const selected   = asetSelect.value;
        asetSelect.innerHTML = '';
        allAsetOptions
            .filter(opt => opt.kategori === kategoriId)
            .forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                option.dataset.kategori = opt.kategori;
                asetSelect.appendChild(option);
            });
        const stillValid = Array.from(asetSelect.options).some(opt => opt.value === selected);
        if (stillValid) asetSelect.value = selected;
        updateInfoAlert();
    }

    // ── Event listeners ───────────────────────────────────────────────────
    kategoriSelect.addEventListener('change', filterAsetByKategori);
    asetSelect.addEventListener('change', updateInfoAlert);

    tanggalPinjam.addEventListener('change', function () {
        updateMaxTanggalKembali();
        updateInfoAlert();
    });

    tanggalKembali.addEventListener('change', updateInfoAlert);

    // Init
    updateMaxTanggalKembali();
    filterAsetByKategori();
</script>
@endpush
