@extends('layouts.karyawan')

@section('title', 'Notifikasi — Batam Pos')

@section('page-title', 'Notifikasi')

@section('page-subtitle')
    <span id="unreadSubtitle">{{ $unreadCount }} belum dibaca</span>
@endsection

@section('topbar-actions')
    <button type="button" class="btn btn-mark-all" id="btnMarkAllRead">Tandai semua dibaca</button>
    <div class="user-avatar">{{ $karyawan['inisial'] }}</div>
@endsection

@push('styles')
<style>
    .btn-mark-all {
        background: #ffffff;
        border: 1px solid #d9d9d4;
        color: #4a4a4a;
        font-weight: 500;
        font-size: 13px;
        border-radius: 8px;
        padding: 7px 16px;
    }

    .btn-mark-all:hover {
        background: #f5f5f3;
        color: #1a1a1a;
        border-color: #b8b8b3;
    }

    .notif-panel {
        background: #ffffff;
        border: 1px solid #e8eaed;
        border-radius: 12px;
        overflow: hidden;
    }

    .notif-filters {
        display: flex;
        gap: 8px;
        padding: 16px 18px 0;
        flex-wrap: wrap;
    }

    .notif-filter-btn {
        background: #ffffff;
        border: 1px solid #d9d9d4;
        color: #4a4a4a;
        font-size: 13px;
        font-weight: 500;
        border-radius: 20px;
        padding: 6px 16px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .notif-filter-btn:hover {
        border-color: #b8b8b3;
        color: #1a1a1a;
    }

    .notif-filter-btn.active {
        border-color: #2b6cb0;
        color: #2b6cb0;
        background-color: #f0f7ff;
    }

    .notif-list {
        padding: 8px 0;
    }

    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid #f0f2f5;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background-color: #fafbfc;
    }

    .notif-item.unread {
        background-color: #f8fbff;
    }

    .notif-item.unread:hover {
        background-color: #f0f7ff;
    }

    .notif-item.hidden {
        display: none;
    }

    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .notif-icon.approved {
        background-color: #d4edda;
        color: #2d9f6f;
    }

    .notif-icon.reminder {
        background-color: #fef3cd;
        color: #d97706;
    }

    .notif-icon.rejected {
        background-color: #fde8e8;
        color: #e53e3e;
    }

    .notif-icon.returned {
        background-color: #e8f0fe;
        color: #2b6cb0;
    }

    .notif-body {
        flex: 1;
        min-width: 0;
    }

    .notif-title {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .notif-message {
        font-size: 13px;
        color: #6b6b6b;
        line-height: 1.45;
    }

    .notif-time {
        font-size: 12px;
        color: #9aa0a6;
        white-space: nowrap;
        flex-shrink: 0;
        padding-top: 2px;
    }

    .notif-empty {
        padding: 40px 18px;
        text-align: center;
        color: #9aa0a6;
        font-size: 13px;
        display: none;
    }

    .notif-empty.show {
        display: block;
    }
</style>
@endpush

@section('content')
    <div class="notif-panel">
        <div class="notif-filters">
            <button type="button" class="notif-filter-btn active" data-filter="semua">Semua</button>
            <button type="button" class="notif-filter-btn" data-filter="belum-dibaca">Belum dibaca</button>
            <button type="button" class="notif-filter-btn" data-filter="peminjaman">Peminjaman</button>
        </div>

        <div class="notif-list" id="notifList">
            @foreach ($notifikasi as $item)
                @php
                    $iconClass = match ($item['type']) {
                        'approved' => 'bi-check-lg',
                        'reminder' => 'bi-clock',
                        'rejected' => 'bi-x-lg',
                        'returned' => 'bi-check-lg',
                        default => 'bi-bell',
                    };
                @endphp
                <div class="notif-item {{ $item['dibaca'] ? '' : 'unread' }}"
                    data-id="{{ $item['id'] }}"
                    data-read="{{ $item['dibaca'] ? '1' : '0' }}"
                    data-kategori="{{ $item['kategori'] }}">
                    <div class="notif-icon {{ $item['type'] }}">
                        <i class="bi {{ $iconClass }}"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-title">{{ $item['judul'] }}</div>
                        <div class="notif-message">{{ $item['pesan'] }}</div>
                    </div>
                    <div class="notif-time">{{ $item['waktu'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="notif-empty" id="notifEmpty">Tidak ada notifikasi.</div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const filterBtns = document.querySelectorAll('.notif-filter-btn');
        const notifItems = document.querySelectorAll('.notif-item');
        const notifEmpty = document.getElementById('notifEmpty');
        const btnMarkAll = document.getElementById('btnMarkAllRead');
        const unreadSubtitle = document.getElementById('unreadSubtitle');
        const sidebarBadge = document.querySelector('.sidebar-badge');

        let activeFilter = 'semua';

        function getUnreadCount() {
            return document.querySelectorAll('.notif-item[data-read="0"]').length;
        }

        function updateUnreadUI() {
            const count = getUnreadCount();
            if (unreadSubtitle) {
                unreadSubtitle.textContent = count + ' belum dibaca';
            }
            if (sidebarBadge) {
                if (count > 0) {
                    sidebarBadge.textContent = count;
                    sidebarBadge.style.display = '';
                } else {
                    sidebarBadge.style.display = 'none';
                }
            }
        }

        function applyFilter() {
            let visibleCount = 0;

            notifItems.forEach(function (item) {
                const isUnread = item.dataset.read === '0';
                const kategori = item.dataset.kategori;
                let show = true;

                if (activeFilter === 'belum-dibaca') {
                    show = isUnread;
                } else if (activeFilter === 'peminjaman') {
                    show = kategori === 'peminjaman';
                }

                item.classList.toggle('hidden', !show);
                if (show) visibleCount++;
            });

            notifEmpty.classList.toggle('show', visibleCount === 0);
        }

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeFilter = btn.dataset.filter;
                applyFilter();
            });
        });

        notifItems.forEach(function (item) {
            item.addEventListener('click', function () {
                if (item.dataset.read === '0') {
                    item.dataset.read = '1';
                    item.classList.remove('unread');
                    updateUnreadUI();
                    applyFilter();
                }
            });
        });

        if (btnMarkAll) {
            btnMarkAll.addEventListener('click', function () {
                notifItems.forEach(function (item) {
                    item.dataset.read = '1';
                    item.classList.remove('unread');
                });
                updateUnreadUI();
                applyFilter();
            });
        }
    })();
</script>
@endpush
