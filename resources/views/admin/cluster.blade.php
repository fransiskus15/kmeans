@extends('layouts.admin')

@section('title', 'Analisis K-Means Clustering — Batam Pos')

@section('page-title', 'Analisis K-Means Clustering')

@section('page-subtitle')
    K={{ $meta['k'] }} · Silhouette {{ $meta['silhouette'] }} · Run {{ $meta['run_date'] }}
@endsection

@section('topbar-actions')
    <button type="button" class="btn btn-rerun" id="btnJalankanCluster">
        <span id="btnJalankanText">Jalankan ulang</span>
        <i class="bi bi-arrow-repeat" id="btnJalankanIcon"></i>
    </button>
@endsection

@push('styles')
<style>
    .btn-rerun {
        background: #ffffff;
        border: 1px solid #d9d9d4;
        color: #4a4a4a;
        font-weight: 500;
        font-size: 13px;
        border-radius: 8px;
        padding: 7px 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-rerun:hover {
        background: #f5f5f3;
        color: #1a1a1a;
        border-color: #b8b8b3;
    }

    .btn-rerun:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .spin {
        animation: spin-anim 0.8s linear infinite;
    }

    @keyframes spin-anim {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    .toast-cluster {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 320px;
        max-width: 420px;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 13px;
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
        display: none;
    }

    .toast-cluster.success {
        background: #eafaf1;
        border: 1px solid #2d9f6f;
        color: #1a6b47;
    }

    .toast-cluster.error {
        background: #fde8e8;
        border: 1px solid #c0392b;
        color: #7a2318;
    }

    .summary-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .summary-item {
        background: #ffffff;
        border: 1px solid #e8eaed;
        border-radius: 12px;
        padding: 16px 18px;
    }

    .summary-item.accent-a { border-left: 4px solid #2d9f6f; }
    .summary-item.accent-b { border-left: 4px solid #e6b800; }
    .summary-item.accent-c { border-left: 4px solid #c0392b; }

    .summary-label {
        font-size: 12px;
        color: #6b6b6b;
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 600;
        line-height: 1.2;
    }

    .summary-value.green { color: #2d9f6f; }
    .summary-value.orange { color: #d97706; }
    .summary-value.red { color: #c0392b; }

    .chart-panel {
        background: #ffffff;
        border: 1px solid #e8eaed;
        border-radius: 12px;
        padding: 18px;
        height: 100%;
    }

    .chart-panel-title {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .chart-panel-footer {
        font-size: 12px;
        color: #6b6b6b;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f0f2f5;
    }

    .chart-panel-footer .good {
        color: #2d9f6f;
        font-weight: 500;
    }

    .elbow-chart {
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 8px;
        height: 160px;
        padding: 0 4px 28px;
    }

    .elbow-bar-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        position: relative;
    }

    .elbow-bar {
        width: 100%;
        max-width: 36px;
        border-radius: 4px 4px 0 0;
        background-color: #b8d4f0;
        margin-top: auto;
        position: relative;
    }

    .elbow-bar.optimal {
        background-color: #2b6cb0;
    }

    .elbow-bar-star {
        position: absolute;
        top: -18px;
        left: 50%;
        transform: translateX(-50%);
        color: #2b6cb0;
        font-size: 12px;
    }

    .elbow-label {
        font-size: 11px;
        color: #6b6b6b;
        margin-top: 8px;
    }

    .elbow-marker {
        position: absolute;
        bottom: 28px;
        width: 0;
        height: calc(100% - 28px);
        border-left: 2px dashed #e53e3e;
        pointer-events: none;
    }

    .elbow-marker-label {
        position: absolute;
        top: 0;
        left: 6px;
        font-size: 10px;
        color: #e53e3e;
        white-space: nowrap;
        font-weight: 500;
    }

    .silhouette-chart {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .silhouette-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .silhouette-k {
        width: 28px;
        font-size: 12px;
        color: #6b6b6b;
        flex-shrink: 0;
    }

    .silhouette-track {
        flex: 1;
        height: 22px;
        background: #f0f2f5;
        border-radius: 4px;
        overflow: hidden;
    }

    .silhouette-fill {
        height: 100%;
        background-color: #b8d4f0;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 8px;
        min-width: 36px;
    }

    .silhouette-fill.optimal {
        background-color: #2b6cb0;
    }

    .silhouette-score {
        font-size: 11px;
        font-weight: 600;
        color: #ffffff;
        white-space: nowrap;
    }

    .indikator-list {
        display: flex;
        flex-direction: column;
    }

    .indikator-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f2f5;
        font-size: 13px;
    }

    .indikator-item:last-child {
        border-bottom: none;
    }

    .indikator-label {
        color: #4a4a4a;
    }

    .indikator-target {
        color: #9aa0a6;
        font-size: 12px;
    }

    .indikator-value {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .indikator-value .bi-check-circle-fill {
        color: #2d9f6f;
        font-size: 14px;
    }

    .cluster-pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .cluster-pill-a {
        background-color: #d4edda;
        color: #155724;
    }

    .cluster-pill-b {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .cluster-pill-c {
        background-color: #fde8e8;
        color: #9b2c2c;
    }

    @media (max-width: 991px) {
        .summary-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 575px) {
        .summary-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    {{-- Toast notifikasi hasil clustering --}}
    <div id="toastCluster" class="toast-cluster"></div>

    {{-- Summary stats --}}
    <div class="summary-row">
        <div class="summary-item">
            <div class="summary-label">Total peminjam</div>
            <div class="summary-value">{{ $summary['total_peminjam'] }}</div>
        </div>
        <div class="summary-item accent-a">
            <div class="summary-label">{{ $summary['cluster_a']['label'] }}</div>
            <div class="summary-value green">
                {{ $summary['cluster_a']['jumlah'] }} ({{ $summary['cluster_a']['persen'] }}%)
            </div>
        </div>
        <div class="summary-item accent-b">
            <div class="summary-label">{{ $summary['cluster_b']['label'] }}</div>
            <div class="summary-value orange">
                {{ $summary['cluster_b']['jumlah'] }} ({{ $summary['cluster_b']['persen'] }}%)
            </div>
        </div>
        <div class="summary-item accent-c">
            <div class="summary-label">{{ $summary['cluster_c']['label'] }}</div>
            <div class="summary-value red">
                {{ $summary['cluster_c']['jumlah'] }} ({{ $summary['cluster_c']['persen'] }}%)
            </div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="chart-panel">
    <div class="chart-panel-title">Elbow method - inertia per K</div>
 
    @if (count($elbow) > 0)
        @php
            $maxInertia = max(array_column($elbow, 'inertia')) ?: 1;
        @endphp
 
        <div class="elbow-chart" id="elbowChart">
            @foreach ($elbow as $item)
                @php
                    $heightPct = ($item['inertia'] / $maxInertia) * 100;
                    $isOptimal = !empty($item['optimal']);
                @endphp
                <div class="elbow-bar-wrap" data-k="{{ $item['k'] }}">
                    @if ($isOptimal)
                        <div class="elbow-marker" style="left: 50%;">
                            <span class="elbow-marker-label">siku K={{ $item['k'] }}</span>
                        </div>
                    @endif
                    <div class="elbow-bar {{ $isOptimal ? 'optimal' : '' }}"
                        style="height: {{ $heightPct }}%;">
                        @if ($isOptimal)
                            <span class="elbow-bar-star"><i class="bi bi-star-fill"></i></span>
                        @endif
                    </div>
                    <div class="elbow-label">K{{ $item['k'] }}</div>
                </div>
            @endforeach
        </div>
 
        <div class="chart-panel-footer">
            Sistem menggunakan K={{ $kDipakai }} sesuai batasan penelitian (Sub-bab 1.4). <br>
            <b>Elbow method adalah teknik atau pendekatan heuristik yang digunakan dalam machine learning dan analisis data untuk menentukan jumlah klaster optimal (k).</b>
            <br>

            <br>
            <b>Grafik ini membantu sistem menentukan jumlah kelompok (cluster) yang paling pas untuk mengelompokkan peminjam. Titik "siku" pada grafik menunjukkan jumlah kelompok yang paling optimal. <br>
            <br>
            Elbow Method adalah cara untuk mencari tahu berapa jumlah kelompok (cluster) yang paling tepat digunakan saat mengelompokkan peminjam berdasarkan perilakunya. Grafik ini menghitung nilai inertia — yaitu seberapa rapat anggota-anggota di dalam satu kelompok — untuk setiap kemungkinan jumlah kelompok (K=2, K=3, K=4, dan seterusnya). Semakin banyak jumlah kelompok, nilai inertia akan semakin kecil, tapi penurunannya akan melambat setelah titik tertentu. Titik di mana penurunan mulai melambat inilah yang disebut "titik siku" (elbow point), dan itu menjadi jumlah kelompok yang direkomendasikan — cukup banyak untuk membedakan pola perilaku peminjam, tapi tidak berlebihan sehingga tetap mudah dipahami.</b>
        </div>
    @else
        <div class="text-center text-muted py-5" style="font-size:13px;">
            <i class="bi bi-bar-chart" style="font-size:32px;opacity:.3;"></i>
            <p class="mt-2 mb-0">Belum ada data Elbow Method.</p>
            <p style="font-size:12px;">Klik "Jalankan ulang" untuk memulai analisis.</p>
        </div>
    @endif
</div>
        </div>

        <div class="col-lg-6">
            <div class="chart-panel">
    <div class="chart-panel-title">Silhouette score per nilai K</div>
 
    @if (count($silhouette) > 0)
        <div class="silhouette-chart">
            @foreach ($silhouette as $item)
                @php
                    $isTertinggi = !empty($item['optimal']);   // Silhouette tertinggi
                    $isDipakai   = !empty($item['dipakai']);   // K yang dipakai sistem
                    $widthPct    = max(3, $item['score']);     // minimal 3% agar label terlihat
                @endphp
                <div class="silhouette-row">
                    <div class="silhouette-k {{ $isDipakai ? 'fw-bold text-primary' : '' }}">
                        K={{ $item['k'] }}
                    </div>
                    <div class="silhouette-track">
                        <div class="silhouette-fill {{ $isDipakai ? 'optimal' : '' }}"
                            style="width: {{ $widthPct }}%;">
                            <span class="silhouette-score">
                                {{ $item['score'] }}%
                                @if ($isTertinggi)
                                    <i class="bi bi-star-fill" title="Silhouette tertinggi"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
 
        <div class="chart-panel-footer">
            @php
                $silDipakai = collect($silhouette)->firstWhere('dipakai', true);
                $skorDipakai = $silDipakai['score'] ?? 0;
                $memenuhiTarget = $skorDipakai >= 50;
            @endphp
 
            Target ≥ 50% - K={{ $kDipakai }} menghasilkan {{ $skorDipakai }}%
            @if ($memenuhiTarget)
                <span class="good"><i class="bi bi-check-circle-fill"></i> memenuhi target</span>
            @else
                <span style="color:#c0392b;font-weight:500;">
                    <i class="bi bi-exclamation-circle-fill"></i> belum memenuhi target
                </span>
            @endif
 
            @if ($kOptimalSilhouette && $kOptimalSilhouette != $kDipakai)
                <br>
                <span style="color:#9aa0a6;font-size:11.5px;">
                    <i class="bi bi-star-fill"></i> Silhouette tertinggi terdapat pada
                    K={{ $kOptimalSilhouette }}, namun sistem tetap menggunakan
                    K={{ $kDipakai }} sesuai batasan penelitian.
                    <br>
                    <br>
                </span>
                <b>Silhouette score adalah metrik evaluasi yang digunakan untuk mengukur kualitas dan validitas hasil pengelompokan (clustering) pada data.</b>
                <br>
                <b>Angka ini menunjukkan seberapa baik pemisahan antar kelompok peminjam. Semakin tinggi persentasenya (mendekati 100%), semakin jelas perbedaan antar kelompok.</b>
                <br>
                <br>
                <b>Silhouette Score mengukur kualitas hasil pengelompokan dengan melihat dua hal sekaligus: seberapa dekat seorang peminjam dengan anggota lain di kelompoknya sendiri, dan seberapa jauh ia dari kelompok lain. Nilainya berkisar dari -100% sampai 100%. Semakin tinggi nilainya, semakin jelas dan tegas pemisahan antar 
                    kelompok - artinya peminjam dalam satu kelompok memang benar-benar mirip satu sama lain, dan berbeda jauh dari kelompok lainnya. Sebaliknya, nilai yang rendah menandakan batas antar kelompok masih kabur, sehingga ada peminjam yang polanya berada di antara dua kelompok sekaligus.</b>
            @endif
        </div>
    @else
        <div class="text-center text-muted py-5" style="font-size:13px;">
            <i class="bi bi-graph-up" style="font-size:32px;opacity:.3;"></i>
            <p class="mt-2 mb-0">Belum ada data Silhouette Score.</p>
            <p style="font-size:12px;">Klik "Jalankan ulang" untuk memulai analisis.</p>
        </div>
    @endif
</div>
 
        </div>
    </div>

    {{-- Bottom row --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="chart-panel-title">Indikator akurasi clustering</div>
                <div class="indikator-list">
    @foreach ($indikator as $item)
        <div class="indikator-item">
            <div>
                <div class="indikator-label">{{ $item['label'] }}</div>
                <div class="indikator-target">(target {{ $item['target'] }})</div>
            </div>
            <div class="indikator-value">
                {{ $item['nilai'] }}
 
                @if (!is_null($item['tercapai']))
                    @if ($item['tercapai'])
                        <i class="bi bi-check-circle-fill" style="color:#2d9f6f;"
                           title="Target tercapai"></i>
                    @else
                        <i class="bi bi-exclamation-circle-fill" style="color:#c0392b;"
                           title="Target belum tercapai"></i>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
    <b>Davies-Bouldin adalah angka yang menunjukkan seberapa mirip satu kelompok dengan kelompok lainnya. Semakin kecil angkanya (mendekati 0), semakin baik - artinya tiap kelompok punya ciri khas yang berbeda-beda.</b>
    <br>
    <b>Davies-Bouldin Index adalah ukuran lain untuk menilai kualitas pengelompokan, dengan cara kerja yang berkebalikan dari Silhouette Score: di sini, semakin kecil nilainya, semakin baik hasilnya. Index ini menghitung rata-rata tingkat "kemiripan" antara satu kelompok dengan kelompok tetangganya yang paling mirip. Jika nilainya kecil (mendekati 0), berarti setiap kelompok memiliki karakteristik yang cukup berbeda dan mudah dibedakan satu sama lain. Jika nilainya besar, berarti ada kelompok-kelompok yang karakteristiknya tumpang tindih, sehingga sulit dibedakan secara jelas.</b>
</div>

            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="chart-panel-title">Daftar peminjam &amp; label cluster</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Cluster</th>
                                <th>F3 Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($peminjam as $row)
                                <tr>
                                    <td>{{ $row['nama'] }}</td>
                                    <td>
                                        <span class="cluster-pill cluster-pill-{{ strtolower($row['cluster']) }}">
                                            {{ $row['cluster_label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['terlambat'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Belum ada data cluster. Klik "Jalankan ulang" untuk memulai analisis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $peminjamRaw->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn      = document.getElementById('btnJalankanCluster');
    const btnText  = document.getElementById('btnJalankanText');
    const btnIcon  = document.getElementById('btnJalankanIcon');
    const toast    = document.getElementById('toastCluster');

    function showToast(message, type) {
        toast.textContent = message;
        toast.className   = 'toast-cluster ' + type;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 6000);
    }

    btn.addEventListener('click', function () {
        // Ubah tombol ke status loading
        btn.disabled = true;
        btnText.textContent = 'Menjalankan...';
        btnIcon.classList.add('spin');

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            showToast('✕ CSRF token tidak ditemukan. Pastikan layout memiliki <meta name="csrf-token">.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Jalankan ulang';
            btnIcon.classList.remove('spin');
            return;
        }

        fetch('{{ route("admin.cluster.jalankan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.getAttribute('content'),
            },
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                showToast('✓ ' + body.message, 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast('✕ ' + (body.message || 'Clustering gagal dijalankan.'), 'error');
                btn.disabled = false;
                btnText.textContent = 'Jalankan ulang';
                btnIcon.classList.remove('spin');
            }
        })
        .catch(err => {
            showToast('✕ Terjadi kesalahan jaringan: ' + err.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Jalankan ulang';
            btnIcon.classList.remove('spin');
        });
    });
});
</script>
@endpush