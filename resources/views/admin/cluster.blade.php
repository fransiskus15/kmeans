@extends('layouts.admin')

@section('title', 'Analisis K-Means Clustering — Batam Pos')

@section('page-title', 'Analisis K-Means Clustering')

@section('page-subtitle')
    K={{ $meta['k'] }} · Silhouette {{ $meta['silhouette'] }} · Run {{ $meta['run_date'] }}
@endsection

@section('topbar-actions')
    <button type="button" class="btn btn-rerun">
        Jalankan ulang
        <i class="bi bi-arrow-repeat"></i>
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
                <div class="chart-panel-title">Elbow method — inertia per K</div>

                @php
                    $maxInertia = max(array_column($elbow, 'inertia'));
                @endphp

                <div class="elbow-chart" id="elbowChart">
                    @foreach ($elbow as $index => $item)
                        @php
                            $heightPct = ($item['inertia'] / $maxInertia) * 100;
                            $isOptimal = !empty($item['optimal']);
                        @endphp
                        <div class="elbow-bar-wrap" data-k="{{ $item['k'] }}">
                            @if ($isOptimal)
                                <div class="elbow-marker" style="left: 50%;">
                                    <span class="elbow-marker-label">siku K=3</span>
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
                    Titik siku (elbow) terjadi pada K=3
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="chart-panel-title">Silhouette score per nilai K</div>

                <div class="silhouette-chart">
                    @foreach ($silhouette as $item)
                        @php
                            $isOptimal = !empty($item['optimal']);
                            $widthPct = ($item['score'] / 100) * 100;
                        @endphp
                        <div class="silhouette-row">
                            <div class="silhouette-k">K={{ $item['k'] }}</div>
                            <div class="silhouette-track">
                                <div class="silhouette-fill {{ $isOptimal ? 'optimal' : '' }}"
                                    style="width: {{ $widthPct }}%;">
                                    <span class="silhouette-score">
                                        {{ $item['score'] }}%
                                        @if ($isOptimal)
                                            <i class="bi bi-star-fill"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="chart-panel-footer">
                    Target &gt; 50% — K=3 = 68% <span class="good"><i class="bi bi-check-circle-fill"></i></span>
                </div>
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
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                    @endforeach
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
                                <th>Aksi</th>
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
                                    <td>
                                        <a href="{{ route('clustering.detail', $row['id']) }}" class="link-tinjau">
                                            Detail
                                            <i class="bi bi-arrow-up-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada data cluster.</td>
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
