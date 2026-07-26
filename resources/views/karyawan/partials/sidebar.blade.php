<div class="sidebar-label">MENU</div>

<a href="{{ route('karyawan.dashboard') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
    <i class="bi bi-house"></i> Dashboard
</a>
<a href="{{ route('karyawan.pengajuan') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'pengajuan' ? 'active' : '' }}">
    <i class="bi bi-plus-lg"></i> Ajukan pinjaman
</a>
<a href="#"
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
