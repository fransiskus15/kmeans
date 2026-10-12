@extends('layouts.admin')

<blade
    section|(%26%2339%3Btitle%26%2339%3B%2C%20%26%2339%3BAnalisis%20K-Means%20Clustering%20%E2%80%94%20Batam%20Pos%26%2339%3B) />

@section('page-title', 'Analisis K-Means Clustering')

@section('page-subtitle')
K={{ $meta['k'] }} · Silhouette {{ $meta['silhouette'] }} · Run
{{ $meta['run_date'] }}
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

        <blade keyframes|%20spin-anim%20%7B>from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
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
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
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

        .summary-item.accent-a {
            border-left: 4px solid #2d9f6f;
        }

        .summary-item.accent-b {
            border-left: 4px solid #e6b800;
        }

        .summary-item.accent-c {
            border-left: 4px solid #c0392b;
        }

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

        .summary-value.green {
            color: #2d9f6f;
        }

        .summary-value.orange {
            color: #d97706;
        }

        .summary-value.red {
            color: #c0392b;
        }

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

        <blade media|%20(max-width%3A%20991px)%20%7B>.summary-row {
            grid-template-columns: repeat(2, 1fr);
        }
        }

        <blade media|%20(max-width%3A%20575px)%20%7B>.summary-row {
            grid-template-columns: 1fr;
        }
        }

        .font-elbow {
            font-size: 12px;
        }

        /* ── Clickable cluster card ───────────────────────────────── */
        .summary-item-clickable {
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .summary-item-clickable:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,.10);
        }
        .summary-item-clickable:active { transform: translateY(0); }
        .summary-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .summary-info-icon {
            font-size: 13px;
            color: #9aa0a6;
            transition: color .15s;
        }
        .summary-item-clickable:hover .summary-info-icon { color: #2b6cb0; }
        .summary-hint {
            font-size: 10.5px;
            color: #b0b0b0;
            margin-top: 6px;
        }

        /* ── Modal overlay ────────────────────────────────────────── */
        .modal-cluster-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }
        .modal-cluster-overlay.open { display: flex; }
        .modal-cluster-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 16px 48px rgba(0,0,0,.22);
            animation: slideUpModal .25s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes slideUpModal {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-cluster-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px 14px;
            border-bottom: 1px solid #f0f2f5;
        }
        .modal-cluster-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 8px;
            font-size: 12px; font-weight: 700;
            margin-right: 10px;
        }
        .modal-cluster-badge.badge-a { background:#d4edda; color:#155724; }
        .modal-cluster-badge.badge-b { background:#fff3cd; color:#856404; }
        .modal-cluster-badge.badge-c { background:#fde8e8; color:#9b2c2c; }
        .modal-cluster-title { font-size:15px; font-weight:700; color:#1a1a1a; }
        .modal-cluster-close {
            background:none; border:none;
            font-size:22px; color:#9aa0a6;
            cursor:pointer; line-height:1; padding:0 4px;
            transition: color .15s;
        }
        .modal-cluster-close:hover { color:#1a1a1a; }
        .modal-cluster-subinfo {
            display:flex; align-items:center; gap:6px;
            padding:10px 20px;
            font-size:13px; color:#6b6b6b;
            background:#f8f9fa;
            border-bottom:1px solid #f0f2f5;
        }
        .modal-cluster-body { padding:18px 20px 20px; }
        .modal-section-label {
            font-size:12px; font-weight:600;
            color:#9aa0a6; text-transform:uppercase;
            letter-spacing:.5px; margin-bottom:14px;
        }
        .modal-var-list { display:flex; flex-direction:column; gap:14px; }
        .modal-var-item {
            background:#f8f9fa;
            border-radius:10px;
            padding:12px 14px;
        }
        .modal-var-header {
            display:flex; align-items:center;
            justify-content:space-between;
            margin-bottom:4px;
        }
        .modal-var-name {
            display:flex; align-items:center; gap:8px;
            font-size:13px; font-weight:600; color:#2d2d2d;
        }
        .modal-var-badge {
            display:inline-flex; align-items:center; justify-content:center;
            width:24px; height:24px; border-radius:6px;
            font-size:10px; font-weight:700;
            background:#e8eaed; color:#4a4a4a; flex-shrink:0;
        }
        .modal-var-value { font-size:14px; font-weight:700; color:#1a1a1a; }
        .modal-var-desc { font-size:11.5px; color:#9aa0a6; margin-bottom:8px; }
        .modal-var-bar-track {
            height:6px; background:#e8eaed;
            border-radius:99px; overflow:hidden;
        }
        .modal-var-bar {
            height:100%; border-radius:99px;
            transition: width .6s cubic-bezier(.4,0,.2,1);
            width:0;
        }

        /* ── Member list ───────────────────────────────────────── */
        .modal-members-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .member-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 10px;
            padding: 10px 14px;
            transition: border-color .15s, box-shadow .15s;
        }
        .member-row:hover {
            border-color: #b8d4f0;
            box-shadow: 0 2px 8px rgba(43,108,176,.08);
        }
        .member-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            flex-shrink: 0;
            margin-right: 10px;
        }
        .member-info { flex: 1; min-width: 0; }
        .member-name {
            font-size: 13px; font-weight: 600;
            color: #1a1a1a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .member-sub {
            font-size: 11px; color: #9aa0a6; margin-top: 1px;
        }
        .member-detail-btn {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600;
            border: 1px solid currentColor;
            border-radius: 7px;
            padding: 5px 11px;
            background: none;
            cursor: pointer;
            transition: background .15s, color .15s;
            flex-shrink: 0;
            margin-left: 10px;
        }
        .member-detail-btn:hover { opacity: .8; }

        /* ── Panel detail individu ─────────────────────────────── */
        .modal-detail-panel {
            display: none;
        }
        .modal-detail-panel.active {
            display: block;
        }
        .modal-overview-panel.hidden {
            display: none;
        }
        .modal-back-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px;
            border-radius: 8px;
            border: 1px solid #e8eaed;
            background: #f8f9fa;
            color: #4a4a4a;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            flex-shrink: 0;
        }
        .modal-back-btn:hover { background:#e8eaed; border-color:#ccc; }

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

    {{-- Cluster A — clickable --}}
    <div class="summary-item accent-a summary-item-clickable"
         onclick="bukaModalCluster('a')"
         data-label="{{ $summary['cluster_a']['label'] }}"
         data-jumlah="{{ $summary['cluster_a']['jumlah'] }}"
         data-persen="{{ $summary['cluster_a']['persen'] }}"
         data-f1="{{ $summary['cluster_a']['f1'] }}"
         data-f2="{{ $summary['cluster_a']['f2'] }}"
         data-f3="{{ $summary['cluster_a']['f3'] }}"
         data-f4="{{ $summary['cluster_a']['f4'] }}"
         data-f5="{{ $summary['cluster_a']['f5'] }}"
         data-warna="green"
         data-kode="A">
        <div class="summary-label-row">
            <span class="summary-label">{{ $summary['cluster_a']['label'] }}</span>
            <i class="bi bi-info-circle summary-info-icon" title="Lihat detail variabel"></i>
        </div>
        <div class="summary-value green">
            {{ $summary['cluster_a']['jumlah'] }} ({{ $summary['cluster_a']['persen'] }}%)
        </div>
        <div class="summary-hint">Klik untuk detail variabel</div>
    </div>

    {{-- Cluster B — clickable --}}
    <div class="summary-item accent-b summary-item-clickable"
         onclick="bukaModalCluster('b')"
         data-label="{{ $summary['cluster_b']['label'] }}"
         data-jumlah="{{ $summary['cluster_b']['jumlah'] }}"
         data-persen="{{ $summary['cluster_b']['persen'] }}"
         data-f1="{{ $summary['cluster_b']['f1'] }}"
         data-f2="{{ $summary['cluster_b']['f2'] }}"
         data-f3="{{ $summary['cluster_b']['f3'] }}"
         data-f4="{{ $summary['cluster_b']['f4'] }}"
         data-f5="{{ $summary['cluster_b']['f5'] }}"
         data-warna="orange"
         data-kode="B">
        <div class="summary-label-row">
            <span class="summary-label">{{ $summary['cluster_b']['label'] }}</span>
            <i class="bi bi-info-circle summary-info-icon" title="Lihat detail variabel"></i>
        </div>
        <div class="summary-value orange">
            {{ $summary['cluster_b']['jumlah'] }} ({{ $summary['cluster_b']['persen'] }}%)
        </div>
        <div class="summary-hint">Klik untuk detail variabel</div>
    </div>

    {{-- Cluster C — clickable --}}
    <div class="summary-item accent-c summary-item-clickable"
         onclick="bukaModalCluster('c')"
         data-label="{{ $summary['cluster_c']['label'] }}"
         data-jumlah="{{ $summary['cluster_c']['jumlah'] }}"
         data-persen="{{ $summary['cluster_c']['persen'] }}"
         data-f1="{{ $summary['cluster_c']['f1'] }}"
         data-f2="{{ $summary['cluster_c']['f2'] }}"
         data-f3="{{ $summary['cluster_c']['f3'] }}"
         data-f4="{{ $summary['cluster_c']['f4'] }}"
         data-f5="{{ $summary['cluster_c']['f5'] }}"
         data-warna="red"
         data-kode="C">
        <div class="summary-label-row">
            <span class="summary-label">{{ $summary['cluster_c']['label'] }}</span>
            <i class="bi bi-info-circle summary-info-icon" title="Lihat detail variabel"></i>
        </div>
        <div class="summary-value red">
            {{ $summary['cluster_c']['jumlah'] }} ({{ $summary['cluster_c']['persen'] }}%)
        </div>
        <div class="summary-hint">Klik untuk detail variabel</div>
    </div>
</div>

{{-- ── MODAL DETAIL CLUSTER ──────────────────────────────────────────── --}}
<div id="modalClusterDetail" class="modal-cluster-overlay" onclick="tutupModalCluster(event)">
    <div class="modal-cluster-box">
        {{-- Header --}}
        <div class="modal-cluster-header" id="modalClusterHeader">
            <div>
                <span class="modal-cluster-badge" id="modalClusterBadge"></span>
                <span class="modal-cluster-title" id="modalClusterTitle"></span>
            </div>
            <button class="modal-cluster-close" onclick="tutupModalCluster()"
                    aria-label="Tutup">&times;</button>
        </div>

        {{-- Sub-info jumlah peminjam --}}
        <div class="modal-cluster-subinfo">
            <i class="bi bi-people-fill"></i>
            <span id="modalClusterJumlah"></span> peminjam
            (<span id="modalClusterPersen"></span>% dari total)
        </div>

        {{-- Variabel F1–F5 --}}
        <div class="modal-cluster-body">
            <p class="modal-section-label">Rata-rata nilai variabel cluster ini:</p>

            <div class="modal-var-list">
                {{-- F1 --}}
                <div class="modal-var-item">
                    <div class="modal-var-header">
                        <div class="modal-var-name">
                            <span class="modal-var-badge" id="badgeF1">F1</span>
                            Frekuensi Peminjaman
                        </div>
                        <span class="modal-var-value" id="valF1"></span>
                    </div>
                    <div class="modal-var-desc">Rata-rata jumlah transaksi peminjaman yang dilakukan</div>
                    <div class="modal-var-bar-track">
                        <div class="modal-var-bar" id="barF1"></div>
                    </div>
                </div>

                {{-- F2 --}}
                <div class="modal-var-item">
                    <div class="modal-var-header">
                        <div class="modal-var-name">
                            <span class="modal-var-badge" id="badgeF2">F2</span>
                            Rata-rata Durasi Pinjam
                        </div>
                        <span class="modal-var-value" id="valF2"></span>
                    </div>
                    <div class="modal-var-desc">Rata-rata lama hari aset dipinjam per transaksi</div>
                    <div class="modal-var-bar-track">
                        <div class="modal-var-bar" id="barF2"></div>
                    </div>
                </div>

                {{-- F3 --}}
                <div class="modal-var-item">
                    <div class="modal-var-header">
                        <div class="modal-var-name">
                            <span class="modal-var-badge" id="badgeF3">F3</span>
                            Tingkat Keterlambatan
                        </div>
                        <span class="modal-var-value" id="valF3"></span>
                    </div>
                    <div class="modal-var-desc">Persentase transaksi yang dikembalikan melebihi batas waktu</div>
                    <div class="modal-var-bar-track">
                        <div class="modal-var-bar" id="barF3"></div>
                    </div>
                </div>

                {{-- F4 --}}
                <div class="modal-var-item">
                    <div class="modal-var-header">
                        <div class="modal-var-name">
                            <span class="modal-var-badge" id="badgeF4">F4</span>
                            Variasi Jenis Aset
                        </div>
                        <span class="modal-var-value" id="valF4"></span>
                    </div>
                    <div class="modal-var-desc">Rata-rata jumlah kategori aset berbeda yang pernah dipinjam</div>
                    <div class="modal-var-bar-track">
                        <div class="modal-var-bar" id="barF4"></div>
                    </div>
                </div>

                {{-- F5 --}}
                <div class="modal-var-item">
                    <div class="modal-var-header">
                        <div class="modal-var-name">
                            <span class="modal-var-badge" id="badgeF5">F5</span>
                            Total Nilai Aset
                        </div>
                        <span class="modal-var-value" id="valF5"></span>
                    </div>
                    <div class="modal-var-desc">Akumulasi nilai (Rp) aset yang pernah dipinjam</div>
                    <div class="modal-var-bar-track">
                        <div class="modal-var-bar" id="barF5"></div>
                    </div>
                </div>
            </div>

            {{-- ── Daftar Anggota Cluster ── --}}
            <div class="modal-members-section" id="modalMembersSection">
                <p class="modal-section-label" style="margin-top:20px;">Anggota cluster ini:</p>
                <div class="modal-members-list" id="modalMembersList"></div>
            </div>
        </div>

        {{-- ── PANEL 2: Detail Individu ── --}}
        <div class="modal-detail-panel" id="modalDetailPanel">
            {{-- Header panel detail --}}
            <div class="modal-cluster-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <button class="modal-back-btn" id="btnKembaliCluster"
                            onclick="kembaliKeCluster()" title="Kembali ke daftar cluster">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <div>
                        <div style="font-size:13px;color:#9aa0a6;line-height:1;"
                             id="detailClusterLabel"></div>
                        <div class="modal-cluster-title" id="detailNamaPeminjam"></div>
                    </div>
                </div>
                <button class="modal-cluster-close" onclick="tutupModalCluster()"
                        aria-label="Tutup">&times;</button>
            </div>

            {{-- Badge cluster pada panel detail --}}
            <div class="modal-cluster-subinfo">
                <span class="modal-cluster-badge" id="detailClusterBadge"
                      style="margin-right:0;"></span>
                <span id="detailClusterNama"></span>
            </div>

            {{-- Variabel individu F1–F5 --}}
            <div class="modal-cluster-body">
                <p class="modal-section-label">Nilai variabel individu:</p>
                <div class="modal-var-list">
                    <div class="modal-var-item">
                        <div class="modal-var-header">
                            <div class="modal-var-name">
                                <span class="modal-var-badge" id="dBadgeF1">F1</span>
                                Frekuensi Peminjaman
                            </div>
                            <span class="modal-var-value" id="dValF1"></span>
                        </div>
                        <div class="modal-var-desc">Jumlah transaksi peminjaman yang dilakukan</div>
                        <div class="modal-var-bar-track">
                            <div class="modal-var-bar" id="dBarF1"></div>
                        </div>
                    </div>
                    <div class="modal-var-item">
                        <div class="modal-var-header">
                            <div class="modal-var-name">
                                <span class="modal-var-badge" id="dBadgeF2">F2</span>
                                Rata-rata Durasi Pinjam
                            </div>
                            <span class="modal-var-value" id="dValF2"></span>
                        </div>
                        <div class="modal-var-desc">Rata-rata lama hari aset dipinjam per transaksi</div>
                        <div class="modal-var-bar-track">
                            <div class="modal-var-bar" id="dBarF2"></div>
                        </div>
                    </div>
                    <div class="modal-var-item">
                        <div class="modal-var-header">
                            <div class="modal-var-name">
                                <span class="modal-var-badge" id="dBadgeF3">F3</span>
                                Tingkat Keterlambatan
                            </div>
                            <span class="modal-var-value" id="dValF3"></span>
                        </div>
                        <div class="modal-var-desc">Persentase transaksi yang dikembalikan melebihi batas waktu</div>
                        <div class="modal-var-bar-track">
                            <div class="modal-var-bar" id="dBarF3"></div>
                        </div>
                    </div>
                    <div class="modal-var-item">
                        <div class="modal-var-header">
                            <div class="modal-var-name">
                                <span class="modal-var-badge" id="dBadgeF4">F4</span>
                                Variasi Jenis Aset
                            </div>
                            <span class="modal-var-value" id="dValF4"></span>
                        </div>
                        <div class="modal-var-desc">Jumlah kategori aset berbeda yang pernah dipinjam</div>
                        <div class="modal-var-bar-track">
                            <div class="modal-var-bar" id="dBarF4"></div>
                        </div>
                    </div>
                    <div class="modal-var-item">
                        <div class="modal-var-header">
                            <div class="modal-var-name">
                                <span class="modal-var-badge" id="dBadgeF5">F5</span>
                                Total Nilai Aset
                            </div>
                            <span class="modal-var-value" id="dValF5"></span>
                        </div>
                        <div class="modal-var-desc">Akumulasi nilai (Rp) aset yang pernah dipinjam</div>
                        <div class="modal-var-bar-track">
                            <div class="modal-var-bar" id="dBarF5"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Charts row --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="chart-panel">
            <div class="chart-panel-title">Elbow method - inertia per K</div>

            @if(count($elbow) > 0)
                @php
                    $maxInertia = max(array_column($elbow, 'inertia')) ?: 1;
                @endphp

                <div class="elbow-chart" id="elbowChart">
                    @foreach($elbow as $item)
                        @php
                            $heightPct = ($item['inertia'] / $maxInertia) * 100;
                            $isOptimal = !empty($item['optimal']);
                        @endphp
                        <div class="elbow-bar-wrap" data-k="{{ $item['k'] }}">
                            @if($isOptimal)
                                <div class="elbow-marker" style="left: 50%;">
                                    <span class="elbow-marker-label">siku
                                        K={{ $item['k'] }}</span>
                                </div>
                            @endif
                            <div class="elbow-bar {{ $isOptimal ? 'optimal' : '' }}"
                                style="height: {{ $heightPct }}%;">
                                @if($isOptimal)
                                    <span class="elbow-bar-star"><i class="bi bi-star-fill"></i></span>
                                @endif
                            </div>
                            <div class="elbow-label">K{{ $item['k'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="chart-panel-footer">
                    Sistem menggunakan K={{ $kDipakai }}. <br>
                </div>
                <div class="chart-panel-footer,color: black">
                    <div class="font-elbow">
                        <b>Elbow method adalah teknik atau pendekatan heuristik yang digunakan dalam machine learning
                            dan
                            analisis data untuk menentukan jumlah klaster optimal (k).</b>
                        <br>
                        <br>
                        <b>Grafik ini membantu sistem menentukan jumlah kelompok (cluster) yang paling pas untuk
                            mengelompokkan peminjam. Titik "siku" pada grafik menunjukkan jumlah kelompok yang paling
                            optimal. <br>
                            <br>
                            Elbow Method adalah cara untuk mencari tahu berapa jumlah kelompok (cluster) yang paling
                            tepat
                            digunakan saat mengelompokkan peminjam berdasarkan perilakunya. Grafik ini menghitung nilai
                            inertia —
                            yaitu seberapa rapat anggota-anggota di dalam satu kelompok — untuk setiap kemungkinan
                            jumlah
                            kelompok (K=2, K=3, K=4, dan seterusnya). Semakin banyak jumlah kelompok, nilai inertia akan
                            semakin kecil, tapi penurunannya akan melambat setelah titik tertentu. Titik di mana
                            penurunan
                            mulai
                            melambat inilah yang disebut "titik siku" (elbow point), dan itu menjadi jumlah kelompok
                            yang
                            direkomendasikan — cukup banyak untuk membedakan pola perilaku peminjam, tapi tidak
                            berlebihan
                            sehingga tetap mudah dipahami.</b>
                    </div>
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

            @if(count($silhouette) > 0)
                <div class="silhouette-chart">
                    @foreach($silhouette as $item)
                        @php
                            $isTertinggi = !empty($item['optimal']); // Silhouette tertinggi
                            $isDipakai = !empty($item['dipakai']); // K yang dipakai sistem
                            $widthPct = max(3, $item['score']); // minimal 3% agar label terlihat
                        @endphp
                        <div class="silhouette-row">
                            <div
                                class="silhouette-k {{ $isDipakai ? 'fw-bold text-primary' : '' }}">
                                K={{ $item['k'] }}
                            </div>
                            <div class="silhouette-track">
                                <div class="silhouette-fill {{ $isDipakai ? 'optimal' : '' }}"
                                    style="width: {{ $widthPct }}%;">
                                    <span class="silhouette-score">
                                        {{ $item['score'] }}%
                                        @if($isTertinggi)
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
                    @if($memenuhiTarget)
                        <span class="good"><i class="bi bi-check-circle-fill"></i> memenuhi target</span>
                    @else
                        <span style="color:#c0392b;font-weight:500;">
                            <i class="bi bi-exclamation-circle-fill"></i> belum memenuhi target
                        </span>
                    @endif

                    @if($kOptimalSilhouette && $kOptimalSilhouette != $kDipakai)
                        <br>
                        <span style="color:#9aa0a6;font-size:11.5px;">
                            <i class="bi bi-star-fill"></i> Silhouette tertinggi terdapat pada
                            K={{ $kOptimalSilhouette }}, namun sistem tetap menggunakan
                            K={{ $kDipakai }} sesuai batasan penelitian.
                            <br>
                        </span>
                    @endif

                    {{-- Penjelasan selalu ditampilkan, tidak bergantung pada kondisi K optimal --}}
                    <br>
                    <span style="color: black; font-size:11.5px;">
                        <b>Silhouette score adalah metrik evaluasi yang digunakan untuk mengukur kualitas dan validitas
                            hasil pengelompokan (clustering) pada data.</b>
                        <br>
                        <b>Angka ini menunjukkan seberapa baik pemisahan antar kelompok peminjam. Semakin tinggi
                            persentasenya (mendekati 100%), semakin jelas perbedaan antar kelompok.</b>
                        <br><br>
                        <b>Silhouette Score mengukur kualitas hasil pengelompokan dengan melihat dua hal sekaligus:
                            seberapa
                            dekat seorang peminjam dengan anggota lain di kelompoknya sendiri,
                            dan seberapa jauh ia dari kelompok lain. Nilainya berkisar dari -100% sampai 100%. Semakin
                            tinggi nilainya, semakin jelas dan tegas pemisahan antar
                            kelompok - artinya peminjam dalam satu kelompok memang benar-benar mirip satu sama lain, dan
                            berbeda jauh dari kelompok lainnya. Sebaliknya, nilai yang rendah
                            menandakan batas antar kelompok masih kabur, sehingga ada peminjam yang polanya berada di
                            antara
                            dua kelompok sekaligus.</b>
                    </span>
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
                @foreach($indikator as $item)
                    <div class="indikator-item">
                        <div>
                            <div class="indikator-label">{{ $item['label'] }}</div>
                            <div class="indikator-target">(target {{ $item['target'] }})</div>
                        </div>
                        <div class="indikator-value">
                            {{ $item['nilai'] }}

                            @if(!is_null($item['tercapai']))
                                @if($item['tercapai'])
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
                <b>Davies-Bouldin adalah angka yang menunjukkan seberapa mirip satu kelompok dengan kelompok lainnya.
                    Semakin kecil angkanya (mendekati 0), semakin baik - artinya tiap kelompok punya ciri khas yang
                    berbeda-beda.</b>
                <br>
                <b>Davies-Bouldin Index adalah ukuran lain untuk menilai kualitas pengelompokan, dengan cara kerja yang
                    berkebalikan dari Silhouette Score: di sini, semakin kecil nilainya, semakin baik hasilnya. Index
                    ini menghitung rata-rata tingkat "kemiripan" antara satu kelompok dengan kelompok tetangganya yang
                    paling mirip. Jika nilainya kecil (mendekati 0), berarti setiap kelompok memiliki karakteristik yang
                    cukup berbeda dan mudah dibedakan satu sama lain. Jika nilainya besar, berarti ada kelompok-kelompok
                    yang karakteristiknya tumpang tindih, sehingga sulit dibedakan secara jelas.</b>
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
                        @forelse($peminjam as $row)
                            <tr>
                                <td>{{ $row['nama'] }}</td>
                                <td>
                                    <span
                                        class="cluster-pill cluster-pill-{{ strtolower($row['cluster']) }}">
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
            const btn = document.getElementById('btnJalankanCluster');
            const btnText = document.getElementById('btnJalankanText');
            const btnIcon = document.getElementById('btnJalankanIcon');
            const toast = document.getElementById('toastCluster');

            function showToast(message, type) {
                toast.textContent = message;
                toast.className = 'toast-cluster ' + type;
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 6000);
            }

            btn.addEventListener('click', function () {
                // Ubah tombol ke status loading
                btn.disabled = true;
                btnText.textContent = 'Menjalankan...';
                btnIcon.classList.add('spin');

                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfMeta) {
                    showToast(
                        '✕ CSRF token tidak ditemukan. Pastikan layout memiliki <meta name="csrf-token">.',
                        'error');
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
                    .then(response => response.json().then(data => ({
                        status: response.status,
                        body: data
                    })))
                    .then(({
                        status,
                        body
                    }) => {
                        if (status === 200 && body.success) {
                            showToast('✓ ' + body.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            showToast('✕ ' + (body.message || 'Clustering gagal dijalankan.'),
                                'error');
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

        /* ══════════════════════════════════════════════════════════════
           DATA PEMINJAM PER CLUSTER (dari PHP → JSON)
        ══════════════════════════════════════════════════════════════ */
        const clusterData = @json($semuaPeminjamCluster);
        // clusterData = { "A": [...], "B": [...], "C": [...] }

        /* ══════════════════════════════════════════════════════════════
           KONFIGURASI WARNA PER CLUSTER
        ══════════════════════════════════════════════════════════════ */
        const clusterWarna = {
            a: { bar: '#2d9f6f', bg: '#d4edda', text: '#155724' },  // hijau
            b: { bar: '#d97706', bg: '#fff3cd', text: '#856404' },  // oranye
            c: { bar: '#c0392b', bg: '#fde8e8', text: '#9b2c2c' },  // merah
        };

        // Track cluster yang sedang dibuka (untuk kembali dari detail)
        let _activeKode = null;

        /* ══════════════════════════════════════════════════════════════
           BUKA MODAL CLUSTER (Panel 1 — Overview)
        ══════════════════════════════════════════════════════════════ */
        function bukaModalCluster(kode) {
            const card = document.querySelector(
                `.summary-item-clickable[data-kode="${kode.toUpperCase()}"]`
            );
            if (!card) return;

            _activeKode = kode.toLowerCase();
            const cfg   = clusterWarna[_activeKode] || clusterWarna.a;
            const k     = _activeKode;
            const warna = cfg.bar;

            const label  = card.dataset.label;
            const jumlah = card.dataset.jumlah;
            const persen = card.dataset.persen;
            const f1 = parseFloat(card.dataset.f1) || 0;
            const f2 = parseFloat(card.dataset.f2) || 0;
            const f3 = parseFloat(card.dataset.f3) || 0;
            const f4 = parseFloat(card.dataset.f4) || 0;
            const f5 = parseFloat(card.dataset.f5) || 0;

            // ── Header ────────────────────────────────────────────────
            const badge = document.getElementById('modalClusterBadge');
            badge.textContent = kode.toUpperCase();
            badge.className   = `modal-cluster-badge badge-${k}`;

            document.getElementById('modalClusterTitle').textContent  = label;
            document.getElementById('modalClusterJumlah').textContent = jumlah;
            document.getElementById('modalClusterPersen').textContent = persen;

            // ── Warna badge F1–F5 ─────────────────────────────────────
            ['F1','F2','F3','F4','F5'].forEach(fn => {
                const el = document.getElementById('badge' + fn);
                if (el) { el.style.background = warna + '22'; el.style.color = warna; }
            });

            // ── Nilai rata-rata ───────────────────────────────────────
            document.getElementById('valF1').textContent = f1 + ' transaksi';
            setBar('barF1', Math.min(f1 / 30 * 100, 100), warna);

            document.getElementById('valF2').textContent = f2 + ' hari';
            setBar('barF2', Math.min(f2 / 60 * 100, 100), warna);

            document.getElementById('valF3').textContent = f3 + '%';
            setBar('barF3', Math.min(f3, 100), warna);

            document.getElementById('valF4').textContent = f4 + ' kategori';
            setBar('barF4', Math.min(f4 / 10 * 100, 100), warna);

            document.getElementById('valF5').textContent = 'Rp ' + Number(f5).toLocaleString('id-ID');
            setBar('barF5', Math.min(f5 / 50000000 * 100, 100), warna);

            // ── Daftar anggota ────────────────────────────────────────
            renderMemberList(kode.toUpperCase(), warna, cfg);

            // ── Pastikan panel overview aktif ─────────────────────────
            showOverviewPanel();

            // ── Tampilkan modal ───────────────────────────────────────
            document.getElementById('modalClusterDetail').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        /* ══════════════════════════════════════════════════════════════
           RENDER DAFTAR ANGGOTA CLUSTER
        ══════════════════════════════════════════════════════════════ */
        function renderMemberList(kodeUpper, warna, cfg) {
            const list = document.getElementById('modalMembersList');
            list.innerHTML = '';

            const anggota = clusterData[kodeUpper] || [];

            if (anggota.length === 0) {
                list.innerHTML = `<div style="text-align:center;color:#9aa0a6;
                    font-size:13px;padding:20px 0;">
                    Belum ada anggota di cluster ini.
                </div>`;
                return;
            }

            anggota.forEach(p => {
                const inisial = (p.nama || '?').charAt(0).toUpperCase();
                const row = document.createElement('div');
                row.className = 'member-row';
                row.innerHTML = `
                    <div class="member-avatar"
                         style="background:${cfg.bg};color:${cfg.text};">
                        ${inisial}
                    </div>
                    <div class="member-info">
                        <div class="member-name">${p.nama}</div>
                        <div class="member-sub">
                            F3 Terlambat: ${p.f3}% &middot; F1: ${p.f1} transaksi
                        </div>
                    </div>
                    <button class="member-detail-btn"
                            style="color:${warna};"
                            onclick="bukaDetailPeminjam(${p.id})">
                        <i class="bi bi-eye"></i> Detail
                    </button>
                `;
                list.appendChild(row);
            });
        }

        /* ══════════════════════════════════════════════════════════════
           BUKA DETAIL INDIVIDU (Panel 2)
        ══════════════════════════════════════════════════════════════ */
        window.bukaDetailPeminjam = function(id) {
            // Cari peminjam dari semua cluster
            let orang = null;
            for (const k of Object.keys(clusterData)) {
                orang = clusterData[k].find(p => p.id == id);
                if (orang) break;
            }
            if (!orang) return;

            const kodeUpper = orang.cluster;
            const kl        = kodeUpper.toLowerCase();
            const cfg       = clusterWarna[kl] || clusterWarna.a;
            const warna     = cfg.bar;

            // ── Isi header panel detail ───────────────────────────────
            document.getElementById('detailClusterLabel').textContent = orang.nama_cluster;
            document.getElementById('detailNamaPeminjam').textContent = orang.nama;

            const dBadge = document.getElementById('detailClusterBadge');
            dBadge.textContent = kodeUpper;
            dBadge.className   = `modal-cluster-badge badge-${kl}`;
            document.getElementById('detailClusterNama').textContent = orang.nama_cluster;

            // ── Warna badge F1–F5 individu ────────────────────────────
            ['F1','F2','F3','F4','F5'].forEach(fn => {
                const el = document.getElementById('dBadge' + fn);
                if (el) { el.style.background = warna + '22'; el.style.color = warna; }
            });

            // ── Nilai individu ────────────────────────────────────────
            document.getElementById('dValF1').textContent = orang.f1 + ' transaksi';
            setBar('dBarF1', Math.min(orang.f1 / 30 * 100, 100), warna);

            document.getElementById('dValF2').textContent = orang.f2 + ' hari';
            setBar('dBarF2', Math.min(orang.f2 / 60 * 100, 100), warna);

            document.getElementById('dValF3').textContent = orang.f3 + '%';
            setBar('dBarF3', Math.min(orang.f3, 100), warna);

            document.getElementById('dValF4').textContent = orang.f4 + ' kategori';
            setBar('dBarF4', Math.min(orang.f4 / 10 * 100, 100), warna);

            document.getElementById('dValF5').textContent = 'Rp ' + Number(orang.f5).toLocaleString('id-ID');
            setBar('dBarF5', Math.min(orang.f5 / 50000000 * 100, 100), warna);

            // ── Tampilkan panel detail ────────────────────────────────
            showDetailPanel();

            // Scroll modal ke atas
            document.querySelector('.modal-cluster-box').scrollTop = 0;
        };

        /* ══════════════════════════════════════════════════════════════
           NAVIGASI PANEL
        ══════════════════════════════════════════════════════════════ */
        function showOverviewPanel() {
            document.getElementById('modalDetailPanel').classList.remove('active');
            // Semua elemen sebelum panel detail (header asli, subinfo, body)
            const box = document.querySelector('.modal-cluster-box');
            // Tampilkan semua child kecuali detail panel
            Array.from(box.children).forEach(el => {
                if (el.id === 'modalDetailPanel') {
                    el.classList.remove('active');
                } else {
                    el.style.display = '';
                }
            });
        }

        function showDetailPanel() {
            const box = document.querySelector('.modal-cluster-box');
            Array.from(box.children).forEach(el => {
                if (el.id === 'modalDetailPanel') {
                    el.classList.add('active');
                } else {
                    el.style.display = 'none';
                }
            });
        }

        window.kembaliKeCluster = function() {
            showOverviewPanel();
            document.querySelector('.modal-cluster-box').scrollTop = 0;
        };

        /* ══════════════════════════════════════════════════════════════
           HELPER: SET BAR ANIMASI
        ══════════════════════════════════════════════════════════════ */
        function setBar(id, pct, warna) {
            const bar = document.getElementById(id);
            if (!bar) return;
            bar.style.width      = '0';
            bar.style.background = warna;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { bar.style.width = pct + '%'; });
            });
        }

        /* ══════════════════════════════════════════════════════════════
           BUKA / TUTUP MODAL
        ══════════════════════════════════════════════════════════════ */
        function tutupModalCluster(e) {
            if (e && e.target !== document.getElementById('modalClusterDetail')) return;
            _tutupModal();
        }

        window.tutupModalCluster = function(e) {
            if (e instanceof Event) { tutupModalCluster(e); } else { _tutupModal(); }
        };
        window.bukaModalCluster = bukaModalCluster;

        function _tutupModal() {
            document.getElementById('modalClusterDetail').classList.remove('open');
            document.body.style.overflow = '';
            // Reset ke panel overview saat modal ditutup
            setTimeout(showOverviewPanel, 200);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') _tutupModal();
        });

    </script>
@endpush
