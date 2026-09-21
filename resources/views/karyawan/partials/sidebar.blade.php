<div class="sidebar-label">MENU</div>

<a href="{{ route('karyawan.dashboard') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
    <i class="bi bi-house"></i> Dashboard
</a>
<a href="{{ route('karyawan.pengajuan') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'pengajuan' ? 'active' : '' }}">
    <i class="bi bi-plus-lg"></i> Ajukan pinjaman
</a>
<a href="{{ route('karyawan.status') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'status' ? 'active' : '' }}">
    <i class="bi bi-clock"></i> Status permintaan
</a>
<a href="{{ route('karyawan.riwayat') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'riwayat' ? 'active' : '' }}">
    <i class="bi bi-clock-history"></i> Riwayat
</a>
<a href="{{ route('karyawan.notifikasi') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'notifikasi' ? 'active' : '' }}"
    style="position: relative;">
    <i class="bi bi-bell"></i>
    Notifikasi
    @if (!empty($karyawanNotifCount) && $karyawanNotifCount > 0)
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
    ">{{ $karyawanNotifCount > 99 ? '99+' : $karyawanNotifCount }}</span>
    @endif
</a>


<div class="sidebar-label">AKUN</div>


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