@extends('layouts.admin')

@section('title', 'Notifikasi — Admin Aset')

@section('page-title', 'Notifikasi')
@section('page-subtitle')
    {{ $jumlahBelumDibaca }} notifikasi belum dibaca
@endsection

@section('topbar-actions')
    @if ($jumlahBelumDibaca > 0)
    <form action="{{ route('admin.notifikasi.baca-semua') }}" method="POST" style="margin:0;">
        @csrf
        <button type="submit" class="btn-tandai-semua">
            Tandai semua dibaca
        </button>
    </form>
    @endif
@endsection

@push('styles')
<style>
    .btn-tandai-semua {
        background: #ffffff;
        border: 1px solid #d9d9d4;
        color: #4a4a4a;
        font-weight: 500;
        font-size: 12.5px;
        border-radius: 8px;
        padding: 7px 16px;
        cursor: pointer;
    }
    .btn-tandai-semua:hover { background: #f5f5f3; color: #1a1a1a; }

    .notif-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }
    .notif-tab {
        font-size: 12.5px;
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid #e8eaed;
        background: #ffffff;
        color: #6b6b6b;
        text-decoration: none;
        display: inline-block;
    }
    .notif-tab.active {
        background: #e6f1fb;
        color: #185fa5;
        border-color: #185fa5;
        font-weight: 500;
    }

    .notif-item {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #f0f2f5;
        align-items: flex-start;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item.unread { background: #f7faff; }

    .notif-icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 16px;
    }
    .notif-icon.keterlambatan { background: #fde8e8; color: #c0392b; }
    .notif-icon.persetujuan   { background: #d4edda; color: #155724; }
    .notif-icon.penolakan     { background: #fde8e8; color: #9b2c2c; }
    .notif-icon.pengambilan   { background: #d1ecf1; color: #0c5460; }
    .notif-icon.pengembalian  { background: #d1ecf1; color: #0c5460; }
    .notif-icon.sistem        { background: #eef0f3; color: #6b6b6b; }

    .notif-body { flex: 1; }
    .notif-title { font-size: 13.5px; font-weight: 600; color: #1a1a1a; margin-bottom: 3px; }
    .notif-desc  { font-size: 12.5px; color: #6b6b6b; line-height: 1.5; }
    .notif-time  { font-size: 11px; color: #9aa0a6; white-space: nowrap; padding-top: 2px; }

    .notif-mark-btn {
        background: none; border: none; color: #185fa5;
        font-size: 11.5px; cursor: pointer; padding: 0; margin-top: 6px;
    }
    .notif-mark-btn:hover { text-decoration: underline; }

    .empty-notif {
        text-align: center; padding: 56px 24px; color: #9aa0a6;
    }
    .empty-notif .empty-icon { font-size: 44px; opacity: .4; margin-bottom: 10px; }
</style>
@endpush

@section('content')

    @if (session('success'))
    <div class="alert alert-success" style="font-size:13px;">{{ session('success') }}</div>
    @endif

    <div class="notif-tabs">
        <a href="{{ route('admin.notifikasi') }}"
           class="notif-tab {{ !request('filter') ? 'active' : '' }}">
            Semua
        </a>
        <a href="{{ route('admin.notifikasi', ['filter' => 'belum_dibaca']) }}"
           class="notif-tab {{ request('filter') === 'belum_dibaca' ? 'active' : '' }}">
            Belum dibaca ({{ $jumlahBelumDibaca }})
        </a>
    </div>

    <div class="panel-card" style="padding:0;">
        @forelse ($notifikasi as $n)
            <div class="notif-item {{ !$n['dibaca'] ? 'unread' : '' }}">
                <div class="notif-icon {{ $n['tipe'] }}">
                    @switch($n['tipe'])
                        @case('keterlambatan') <i class="bi bi-clock-history"></i> @break
                        @case('persetujuan')   <i class="bi bi-check-circle"></i> @break
                        @case('penolakan')     <i class="bi bi-x-circle"></i> @break
                        @case('pengambilan')   <i class="bi bi-box-arrow-in-down"></i> @break
                        @case('pengembalian')  <i class="bi bi-box-arrow-in-left"></i> @break
                        @default               <i class="bi bi-info-circle"></i>
                    @endswitch
                </div>
                <div class="notif-body">
                    <div class="notif-title">{{ $n['judul'] }}</div>
                    <div class="notif-desc">{{ $n['pesan'] }}</div>

                    @if (!$n['dibaca'])
                    <form action="{{ route('admin.notifikasi.baca', $n['id']) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="notif-mark-btn">Tandai dibaca</button>
                    </form>
                    @endif
                </div>
                <div class="notif-time">{{ $n['waktu'] }}</div>
            </div>
        @empty
            <div class="empty-notif">
                <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
                <p class="fw-semibold mb-1">Belum ada notifikasi.</p>
                <p style="font-size:13px;">Notifikasi keterlambatan aset akan muncul di sini secara otomatis.</p>
            </div>
        @endforelse
    </div>

    @if (isset($notifikasiList) && $notifikasiList->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $notifikasiList->links() }}
    </div>
    @endif

@endsection