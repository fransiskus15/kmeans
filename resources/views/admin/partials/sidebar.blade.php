<div class="sidebar-label">MENU</div>

<a href="{{ route('dashboard') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
    <i class="bi bi-grid"></i> Dashboard
</a>
<a href="{{ route('admin.aset') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'aset' ? 'active' : '' }}">
    <i class="bi bi-box"></i> Data aset
</a>
<a href="{{ route('admin.peminjaman') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'peminjaman' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Peminjaman
</a>
<a href="#" class="sidebar-item {{ ($activeMenu ?? '') === 'pengguna' ? 'active' : '' }}">
    <i class="bi bi-people"></i> Pengguna
</a>

<div class="sidebar-label">ANALISIS</div>

<a href="#" class="sidebar-item {{ ($activeMenu ?? '') === 'cluster' ? 'active' : '' }}">
    <i class="bi bi-diagram-3"></i> Cluster
</a>
<a href="{{ route('admin.laporan') }}" class="sidebar-item {{ ($activeMenu ?? '') === 'laporan' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
</a>