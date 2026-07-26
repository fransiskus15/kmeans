<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Aset — Batam Pos')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6f8;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            color: #1a1a1a;
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 210px;
            min-width: 210px;
            background-color: #f0f2f5;
            border-right: 1px solid #e8eaed;
            padding: 20px 0;
        }

        .sidebar-label {
            font-size: 11px;
            font-weight: 600;
            color: #9aa0a6;
            letter-spacing: 0.06em;
            padding: 0 20px 10px;
        }

        .sidebar-label:not(:first-child) {
            margin-top: 16px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            font-size: 13px;
            color: #4a4a4a;
            text-decoration: none;
        }

        .sidebar-item:hover {
            color: #1a1a1a;
            background-color: #e8eaed;
        }

        .sidebar-item.active {
            background-color: #dce8f5;
            color: #2b6cb0;
            font-weight: 500;
        }

        .sidebar-item i {
            font-size: 16px;
            width: 18px;
            text-align: center;
        }

        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e8eaed;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #3b7dd8;
            flex-shrink: 0;
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.3;
        }

        .topbar-subtitle {
            font-size: 12px;
            color: #6b6b6b;
            font-weight: 400;
            margin-top: 2px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-tambah {
            background: #ffffff;
            border: 1px solid #1a1a1a;
            color: #1a1a1a;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            padding: 7px 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-tambah:hover {
            background: #f5f5f3;
            color: #1a1a1a;
            border-color: #1a1a1a;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #dce8f5;
            color: #2b6cb0;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .content-area {
            padding: 20px 24px;
            overflow-y: auto;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            padding: 18px 20px;
            height: 100%;
        }

        .stat-label {
            font-size: 12px;
            color: #6b6b6b;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 600;
            line-height: 1;
        }

        .stat-value.dark { color: #1a1a1a; }
        .stat-value.green { color: #2d9f6f; }
        .stat-value.blue { color: #2b6cb0; }
        .stat-value.orange { color: #d97706; }
        .stat-value.gold { color: #b8860b; }
        .stat-value.red { color: #9b2c2c; }

        .panel-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .panel-title {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa0a6;
            font-size: 14px;
        }

        .search-input {
            width: 100%;
            border: 1px solid #d9d9d4;
            border-radius: 8px;
            font-size: 13px;
            padding: 8px 12px 8px 36px;
            color: #1a1a1a;
        }

        .search-input:focus {
            border-color: #b8b8b3;
            box-shadow: none;
            outline: none;
        }

        .filter-select {
            border: 1px solid #d9d9d4;
            border-radius: 8px;
            font-size: 13px;
            padding: 8px 12px;
            color: #4a4a4a;
            min-width: 140px;
        }

        .filter-select:focus {
            border-color: #b8b8b3;
            box-shadow: none;
        }

        .table-custom {
            font-size: 13px;
            margin-bottom: 0;
        }

        .table-custom th {
            font-weight: 500;
            color: #6b6b6b;
            border-bottom: 1px solid #e8eaed;
            padding: 8px 12px;
            font-size: 12px;
        }

        .table-custom td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        .badge-pill {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .badge-baik { background-color: #d4edda; color: #155724; }
        .badge-perawatan { background-color: #fef3cd; color: #856404; }
        .badge-dipinjam { background-color: #fef3cd; color: #b45309; }
        .badge-tersedia { background-color: #d4edda; color: #155724; }
        .badge-perbaikan { background-color: #fde8e8; color: #9b2c2c; }
        .badge-aktif { background-color: #fef3cd; color: #856404; }
        .badge-terlambat { background-color: #fde8e8; color: #9b2c2c; }

        .action-btn {
            background: none;
            border: none;
            color: #6b6b6b;
            font-size: 15px;
            padding: 2px 6px;
            cursor: pointer;
        }

        .action-btn:hover {
            color: #1a1a1a;
        }

        .action-btn.delete:hover {
            color: #c0392b;
        }

        .table-footer {
            font-size: 12px;
            color: #6b6b6b;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0f2f5;
        }

        .chart-wrap {
            position: relative;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-center-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .chart-center-label .total {
            font-size: 18px;
            font-weight: 600;
            line-height: 1.2;
        }

        .chart-center-label .unit {
            font-size: 11px;
            color: #6b6b6b;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #4a4a4a;
            margin-bottom: 6px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cluster-footer {
            font-size: 11px;
            color: #6b6b6b;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0eeea;
        }

        .table-custom tr.row-terlambat td {
            background-color: #fdf2f2;
        }

        .table-custom tr.row-selected td {
            background-color: #f0f7ff;
        }

        .link-action {
            background: none;
            border: none;
            padding: 0;
            font-size: 13px;
            font-weight: 500;
            color: #2b6cb0;
            text-decoration: none;
            cursor: pointer;
        }

        .link-action:hover {
            color: #1e4f8a;
            text-decoration: underline;
        }

        .text-selesai {
            font-size: 13px;
            color: #9aa0a6;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0eeea;
            font-size: 13px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b6b6b;
        }

        .detail-value {
            font-weight: 500;
            text-align: right;
            max-width: 58%;
        }

        .detail-section-title {
            font-size: 12px;
            font-weight: 600;
            color: #4a4a4a;
            margin-bottom: 10px;
        }

        .form-label-custom {
            font-size: 12px;
            font-weight: 500;
            color: #4a4a4a;
            margin-bottom: 6px;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1px solid #d9d9d4;
            border-radius: 8px;
            font-size: 13px;
            padding: 8px 12px;
            color: #1a1a1a;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: #b8b8b3;
            box-shadow: none;
        }

        .form-note {
            border: 1px solid #d9d9d4;
            border-radius: 8px;
            font-size: 13px;
            resize: vertical;
        }

        .form-note:focus {
            border-color: #b8b8b3;
            box-shadow: none;
        }

        .info-alert {
            background-color: #e8f4fd;
            border: 1px solid #b8d4f0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #2b6cb0;
        }

        .success-alert {
            background-color: #e9f7ef;
            border: 1px solid #a8d5b8;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #2d6a4f;
        }

        .warning-alert {
            background-color: #fef3cd;
            border: 1px solid #f0d58c;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #856404;
        }

        .badge-cluster {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            background-color: #d4edda;
            color: #155724;
        }

        .badge-dikembalikan {
            background-color: #d4edda;
            color: #155724;
        }

        .status-terlambat-text {
            color: #9b2c2c;
            font-weight: 500;
            font-size: 13px;
        }

        .btn-konfirmasi {
            background: #ffffff;
            border: 1px solid #2d9f6f;
            color: #2d9f6f;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .btn-konfirmasi:hover {
            background: #e9f7ef;
            color: #2d9f6f;
            border-color: #2d9f6f;
        }

        .btn-cancel {
            background: #ffffff;
            border: 1px solid #d9d9d4;
            color: #4a4a4a;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            padding: 8px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: #f5f5f3;
            color: #1a1a1a;
            border-color: #b8b8b3;
        }

        .table-legend {
            font-size: 12px;
            color: #6b6b6b;
            margin-top: 12px;
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="dashboard-layout">

        <aside class="sidebar">
            @include('admin.partials.sidebar')
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-title-wrap">
                    <span class="topbar-dot"></span>
                    <div>
                        <div class="topbar-title">@yield('page-title')</div>
                        @hasSection('page-subtitle')
                        <div class="topbar-subtitle">@yield('page-subtitle')</div>
                        @endif
                    </div>
                </div>
                <div class="topbar-actions">
                    @yield('topbar-actions')
                    <div class="user-avatar">{{ $admin['inisial'] ?? 'AA' }}</div>
                </div>
            </header>

            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>

</html>
