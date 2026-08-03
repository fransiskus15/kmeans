<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Karyawan — Batam Pos')</title>

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

        .sidebar-badge {
            margin-left: auto;
            background-color: #e53e3e;
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
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
        }

        .topbar-subtitle {
            font-size: 12px;
            color: #6b6b6b;
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
            background-color: #dce8f5;
            color: #2b6cb0;
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

        .stat-value.blue { color: #2b6cb0; }
        .stat-value.orange { color: #d97706; }
        .stat-value.dark { color: #1a1a1a; }

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

        .badge-status {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .badge-aktif { background-color: #fef3cd; color: #856404; }
        .badge-terlambat { background-color: #fde8e8; color: #9b2c2c; }
        .badge-menunggu { background-color: #f0f2f5; color: #4a4a4a; border: 1px solid #d9d9d4; }
        .badge-tepat { background-color: #d4edda; color: #155724; }
        .badge-tepat-terlambat { background-color: #fde8e8; color: #9b2c2c; }

        .profile-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
            font-size: 13px;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-label {
            color: #6b6b6b;
        }

        .profile-value {
            font-weight: 500;
        }

        .profile-good {
            color: #2d9f6f;
            font-weight: 500;
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
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .info-alert i {
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
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

        .btn-cancel {
            background: #ffffff;
            border: 1px solid #d9d9d4;
            color: #4a4a4a;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .btn-cancel:hover {
            background: #f5f5f3;
            color: #1a1a1a;
            border-color: #b8b8b3;
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="dashboard-layout">

        <aside class="sidebar">
            @include('karyawan.partials.sidebar')
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
                        <a href="{{ route('karyawan.notifikasi') }}" class="notif-btn" aria-label="Notifikasi">
                            <i class="bi bi-bell"></i>
                            @if (($karyawan['unread_notifikasi'] ?? 0) > 0)
                                <span class="dot"></span>
                            @endif
                        </a>
                        <div class="user-profile">
                           <i class="bi bi-person-circle"></i> {{ auth()->user()->nama }}
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
    @stack('scripts')
</body>

</html>
