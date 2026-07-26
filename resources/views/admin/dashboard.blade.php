@extends('layouts.admin')

@section('title', 'Dashboard Admin Aset — Batam Pos')

@section('page-title', 'Dashboard Admin Aset')

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
                <div class="stat-label">Sedang dipinjam</div>
                <div class="stat-value blue">{{ $stats['sedang_dipinjam'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Menunggu approval</div>
                <div class="stat-value gold">{{ $stats['menunggu_approval'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Melewati batas</div>
                <div class="stat-value red">{{ $stats['melewati_batas'] }}</div>
            </div>
        </div>
    </div>

    {{-- Table + Chart --}}
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-title">Peminjaman aktif</div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Aset</th>
                                <th>Batas kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($peminjaman_aktif as $row)
                            <tr>
                                <td>{{ $row['peminjam'] }}</td>
                                <td>{{ $row['aset'] }}</td>
                                <td>{{ $row['batas_kembali'] }}</td>
                                <td>
                                    @if ($row['status'] === 'Terlambat')
                                        <span class="badge-pill badge-terlambat">{{ $row['status'] }}</span>
                                    @else
                                        <span class="badge-pill badge-aktif">{{ $row['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-title">Distribusi cluster peminjam</div>

                <div class="chart-wrap">
                    <canvas id="clusterChart"></canvas>
                    <div class="chart-center-label">
                        <div class="total">{{ array_sum(array_column($cluster, 'value')) }}</div>
                        <div class="unit">org</div>
                    </div>
                </div>

                <div class="mt-3">
                    @foreach ($cluster as $item)
                    <div class="legend-item">
                        <span class="legend-dot" style="background-color: {{ $item['color'] }}"></span>
                        {{ $item['label'] }} — {{ $item['value'] }}
                    </div>
                    @endforeach
                </div>

                <div class="cluster-footer">
                    Silhouette: 0.68 — Run 01 Jul 2025
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
                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`
                    }
                }
            },
            maintainAspectRatio: false,
        }
    });
</script>
@endpush
