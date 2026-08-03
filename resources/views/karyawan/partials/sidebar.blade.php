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
<a href="#"
    class="sidebar-item {{ ($activeMenu ?? '') === 'riwayat' ? 'active' : '' }}">
    <i class="bi bi-grid"></i> Riwayat
</a>
<a href="{{ route('karyawan.notifikasi') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'notifikasi' ? 'active' : '' }}">
    <i class="bi bi-bell"></i> Notifikasi
    @if (($karyawan['unread_notifikasi'] ?? 0) > 0)
        <span class="sidebar-badge">{{ $karyawan['unread_notifikasi'] }}</span>
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