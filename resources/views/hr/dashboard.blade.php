@extends('layouts.hr')

@section('title', 'Dashboard HR — Batam Pos')

@section('page-title', 'Dashboard HR / Kepala Divisi')

@section('content')

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Menunggu approval</div>
                <div class="stat-value orange">{{ $stats['menunggu_approval'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Disetujui bulan ini</div>
                <div class="stat-value green">{{ $stats['disetujui_bulan_ini'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Ditolak bulan ini</div>
                <div class="stat-value red">{{ $stats['ditolak_bulan_ini'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Aset terlambat</div>
                <div class="stat-value dark-red">{{ $stats['aset_terlambat'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Tabel permintaan --}}
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-title">Permintaan perlu ditinjau</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Aset</th>
                                <th>Cluster</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permintaan as $row)
                            <tr>
                                <td>{{ $row['peminjam'] }}</td>
                                <td>{{ $row['aset'] }}</td>
                                <td>
                                    <span class="cluster-dot cluster-{{ strtolower($row['cluster']) }}">
                                        {{ $row['cluster'] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('hr.approval') }}" class="link-tinjau">
                                        Tinjau
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Chart cluster --}}
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-title">Distribusi cluster & akurasi</div>

                <div class="chart-wrap">
                    <canvas id="clusterChart"></canvas>
                    <div class="chart-center-label">
                        <div class="total">{{ array_sum(array_column($cluster, 'value')) }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    @foreach ($cluster as $item)
                    <div class="legend-item">
                        <span class="legend-dot" style="background-color: {{ $item['color'] }}"></span>
                        {{ $item['label'] }}: {{ $item['value'] }} ({{ $item['persen'] }}%)
                    </div>
                    @endforeach
                </div>

                <div class="metric-row">
                    <span class="metric-label">Akurasi klasifikasi</span>
                    <span class="metric-value metric-good">
                        {{ $akurasi['klasifikasi'] }}
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Silhouette Score</span>
                    <span class="metric-value metric-good">
                        {{ $akurasi['silhouette'] }}
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Terakhir diperbarui</span>
                    <span class="metric-value">{{ $akurasi['terakhir_diperbarui'] }}</span>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const clusterData = @json($cluster);

    new Chart(document.getElementById('clusterChart'), {
        type: 'doughnut',
        data: {
            labels: clusterData.map(c => c.label),
            datasets: [{
                data: clusterData.map(c => c.value),
                backgroundColor: clusterData.map(c => c.color),
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed} (${clusterData[ctx.dataIndex].persen}%)`
                    }
                }
            },
            maintainAspectRatio: false,
        }
    });
</script>
@endpush
