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
                        class="form-control form-control-custom"
                        value="{{ old('tanggal_pinjam', $form['tanggal_pinjam']) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="tanggal_kembali" class="form-label-custom">Estimasi tanggal kembali</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali"
                        class="form-control form-control-custom"
                        value="{{ old('tanggal_kembali', $form['tanggal_kembali']) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="keperluan" class="form-label-custom">Keterangan keperluan peminjaman</label>
                <textarea id="keperluan" name="keperluan" class="form-control form-note" rows="4"
                    placeholder="Jelaskan keperluan peminjaman aset..." required>{{ old('keperluan', $form['keperluan']) }}</textarea>
            </div>

            <div class="info-alert mb-4" id="infoAlert">
                <i class="bi bi-info-circle"></i>
                <span id="infoText">{{ $info_teks }}</span>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-action d-flex align-items-center gap-2">
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
    const asetSelect = document.getElementById('aset_id');
    const kategoriSelect = document.getElementById('kategori_aset');
    const tanggalPinjam = document.getElementById('tanggal_pinjam');
    const tanggalKembali = document.getElementById('tanggal_kembali');
    const infoText = document.getElementById('infoText');

    const allAsetOptions = Array.from(asetSelect.options).map(opt => ({
        value: opt.value,
        text: opt.text,
        kategori: opt.dataset.kategori,
    }));

    function filterAsetByKategori() {
        const kategoriId = kategoriSelect.value;
        const selected = asetSelect.value;

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
        if (stillValid) {
            asetSelect.value = selected;
        }

        updateInfoAlert();
    }

    function hitungDurasi() {
        const mulai = new Date(tanggalPinjam.value);
        const selesai = new Date(tanggalKembali.value);

        if (!tanggalPinjam.value || !tanggalKembali.value || selesai <= mulai) {
            return null;
        }

        const diff = Math.round((selesai - mulai) / (1000 * 60 * 60 * 24));
        return diff;
    }

    function updateInfoAlert() {
        const asetNama = asetSelect.options[asetSelect.selectedIndex]?.text.split(' — ')[0] || 'Aset';
        const durasi = hitungDurasi();

        if (durasi) {
            infoText.textContent = `${asetNama} tersedia. Durasi peminjaman: ${durasi} hari. Permintaan akan diteruskan ke HR untuk disetujui.`;
        } else {
            infoText.textContent = `${asetNama} tersedia. Permintaan akan diteruskan ke HR untuk disetujui.`;
        }
    }

    kategoriSelect.addEventListener('change', filterAsetByKategori);
    asetSelect.addEventListener('change', updateInfoAlert);
    tanggalPinjam.addEventListener('change', updateInfoAlert);
    tanggalKembali.addEventListener('change', updateInfoAlert);

    filterAsetByKategori();
</script>
@endpush
