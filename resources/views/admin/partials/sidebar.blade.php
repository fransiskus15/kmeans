<div class="sidebar-label">MENU</div>

<a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
    <i class="bi bi-grid"></i> Dashboard
</a>
<a href="{{ route('admin.aset') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'aset' ? 'active' : '' }}">
    <i class="bi bi-box"></i> Data aset
</a>
<a href="{{ route('admin.peminjaman') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'peminjaman' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Peminjaman
</a>
<a href="{{ route('admin.notifikasi') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'notifikasi' ? 'active' : '' }}"
    style="position: relative;">
    <i class="bi bi-bell"></i>
    Notifikasi
    @if (!empty($notifBelumDibacaCount) && $notifBelumDibacaCount > 0)
    <span style="
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        background: #e53e3e;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border-radius: 20px;
        line-height: 1;
        margin-left: auto;
    ">{{ $notifBelumDibacaCount > 99 ? '99+' : $notifBelumDibacaCount }}</span>
    @endif
</a>
<a href="{{ route('admin.cluster') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'cluster' ? 'active' : '' }}">
    <i class="bi bi-pie-chart"></i> Analisis cluster
</a>
{{-- <a href="#" class="sidebar-item {{ ($activeMenu ?? '') === 'pengguna' ? 'active' : '' }}">
    <i class="bi bi-people"></i> Pengguna
</a> --}}

<div class="sidebar-label">ANALISIS</div>

<a href="{{ route('admin.laporan') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'laporan' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
</a>

<form action="{{ route('logout') }}" method="POST" style="margin:0;">
    @csrf
    <button
        type="submit"
        class="sidebar-item"
        style="
            width:100%;
            border:none;
            background:none;
            text-align:left;
            cursor:pointer;
            font-family:inherit;
            font-size:inherit;
            color:#A32D2D;
        "
        onclick="return confirm('Anda yakin ingin keluar dari sistem?')"
    >
        <i class="bi bi-box-arrow-right"></i> Keluar
    </button>
</form>