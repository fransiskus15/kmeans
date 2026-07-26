@extends('layouts.hr')

@section('title', 'Detail Permintaan Peminjaman — HR')

@section('page-title', 'Detail Permintaan Peminjaman')
@section('page-subtitle', 'Persetujuan HR / Kepala Divisi')

@section('content')
    <div class="row g-3">

        {{-- Kolom kiri: Detail & Keputusan --}}
        <div class="col-lg-6">
            <div class="panel-card mb-3">
                <div class="panel-title">Detail permintaan</div>

                <div class="detail-row">
                    <span class="detail-label">Peminjam</span>
                    <span class="detail-value fw-semibold">{{ $permintaan['peminjam'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Aset diminta</span>
                    <span class="detail-value">{{ $permintaan['aset'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal pinjam</span>
                    <span class="detail-value">{{ $permintaan['tanggal_pinjam'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Batas kembali</span>
                    <span class="detail-value">{{ $permintaan['batas_kembali'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Keperluan</span>
                    <span class="detail-value">{{ $permintaan['keperluan'] }}</span>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-title">Keputusan</div>

                <textarea class="form-control form-note mb-3" rows="4" placeholder="Catatan (opsional)..."></textarea>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-action d-flex align-items-center gap-2">
                        Setujui
                        <i class="bi bi-arrow-up-right"></i>
                    </button>
                    <button type="button" class="btn btn-action">
                        Tolak
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: Profil perilaku peminjam --}}
        <div class="col-lg-6">
            <div class="profile-card">
                <div class="panel-title mb-3">Profil perilaku peminjam</div>

                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="cluster-badge">{{ $profil['cluster'] }}</div>
                    <div>
                        <div class="profile-headline">{{ $profil['judul'] }}</div>
                        <div class="profile-sub">{{ $profil['subjudul'] }}</div>
                        <div class="profile-rekom">{{ $profil['rekomendasi'] }}</div>
                    </div>
                </div>

                <div class="radar-wrap">
                    <canvas id="radarChart"></canvas>
                </div>

                <div class="table-responsive">
                    <table class="table fitur-table">
                        <thead>
                            <tr>
                                <th>Var</th>
                                <th>Nilai</th>
                                <th>Ket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fitur as $row)
                            <tr>
                                <td>{{ $row['var'] }}</td>
                                <td>{{ $row['nilai'] }}</td>
                                <td>
                                    <span class="badge-ket badge-{{ $row['badge'] }}">{{ $row['ket'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const radarData = @json($radar);

    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [{
                data: radarData.values,
                backgroundColor: 'rgba(45, 159, 111, 0.25)',
                borderColor: '#2d9f6f',
                borderWidth: 2,
                pointBackgroundColor: '#2d9f6f',
                pointRadius: 4,
            }]
        },
        options: {
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { display: false },
                    grid: { color: 'rgba(45, 159, 111, 0.2)' },
                    angleLines: { color: 'rgba(45, 159, 111, 0.2)' },
                    pointLabels: {
                        font: { size: 12, weight: '500' },
                        color: '#2d6a4f',
                    },
                }
            },
            plugins: {
                legend: { display: false },
            },
            maintainAspectRatio: false,
        }
    });
</script>
@endpush
