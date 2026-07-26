<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HR — Batam Pos')</title>

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
            background-color: #ffffff;
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
            background-color: #f5f6f8;
        }

        .sidebar-item.active {
            background-color: #e8f0fe;
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
            gap: 16px;
        }

        .notif-btn {
            position: relative;
            background: none;
            border: none;
            color: #4a4a4a;
            font-size: 18px;
            padding: 4px;
            cursor: pointer;
        }

        .notif-btn .dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            background-color: #e53e3e;
            border-radius: 50%;
            border: 2px solid #ffffff;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #2d9f6f;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 500;
            color: #1a1a1a;
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

        .stat-value.orange { color: #d97706; }
        .stat-value.green { color: #2d9f6f; }
        .stat-value.red { color: #c0392b; }
        .stat-value.dark-red { color: #9b2c2c; }

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

        .cluster-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .cluster-a { background-color: #2d9f6f; }
        .cluster-b { background-color: #3b7dd8; }
        .cluster-c { background-color: #c0392b; }

        .link-tinjau {
            color: #2b6cb0;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .link-tinjau:hover {
            color: #1a4f8b;
            text-decoration: underline;
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
            font-size: 22px;
            font-weight: 600;
            line-height: 1.2;
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

        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-top: 1px solid #f0f2f5;
            font-size: 12px;
        }

        .metric-row:first-of-type {
            margin-top: 12px;
        }

        .metric-label {
            color: #6b6b6b;
        }

        .metric-value {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .metric-good {
            color: #2d9f6f;
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
            max-width: 55%;
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

        .btn-action {
            background: #ffffff;
            border: 1px solid #1a1a1a;
            color: #1a1a1a;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .btn-action:hover {
            background: #f5f5f3;
            color: #1a1a1a;
            border-color: #1a1a1a;
        }

        .profile-card {
            background-color: #e9f7ef;
            border: 1px solid #a8d5b8;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .cluster-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #2d9f6f;
            color: #ffffff;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-headline {
            font-size: 14px;
            font-weight: 600;
            color: #1a5c3a;
        }

        .profile-sub {
            font-size: 12px;
            color: #2d6a4f;
        }

        .profile-rekom {
            font-size: 12px;
            color: #4a4a4a;
            margin-top: 4px;
        }

        .radar-wrap {
            height: 200px;
            margin: 16px 0;
        }

        .fitur-table {
            font-size: 12px;
            margin-bottom: 0;
        }

        .fitur-table thead th {
            background-color: #2d9f6f;
            color: #ffffff;
            font-weight: 500;
            padding: 8px 10px;
            border: none;
            font-size: 11px;
        }

        .fitur-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #c8e6d0;
            background-color: #f4fbf7;
            vertical-align: middle;
        }

        .fitur-table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge-ket {
            font-size: 10px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .badge-tinggi { background: #fef3cd; color: #856404; }
        .badge-wajar { background: #d4edda; color: #155724; }
        .badge-rendah { background: #fff3cd; color: #b8860b; }
        .badge-beragam { background: #d1ecf1; color: #0c5460; }
        .badge-besar { background: #e8daef; color: #6c3483; }
    </style>

    @stack('styles')
</head>

<body>

    <div class="dashboard-layout">

        <aside class="sidebar">
            @include('hr.partials.sidebar')
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
                @hasSection('topbar-actions')
                    <div class="topbar-actions">
                        @yield('topbar-actions')
                    </div>
                @else
                    <div class="topbar-actions">
                        <button type="button" class="notif-btn" aria-label="Notifikasi">
                            <i class="bi bi-bell"></i>
                            <span class="dot"></span>
                        </button>
                        <div class="user-profile">
                            <div class="user-avatar">{{ $hr['inisial'] ?? 'SR' }}</div>
                            <span class="user-name">{{ $hr['nama'] ?? 'Siti Rahayu (HR)' }}</span>
                        </div>
                    </div>
                @endif
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
