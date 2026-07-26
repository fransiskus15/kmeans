<div class="sidebar-label">MENU</div>

<a href="{{ route('hr.dashboard') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
    <i class="bi bi-grid"></i> Dashboard
</a>
<a href="{{ route('hr.approval') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'approval' ? 'active' : '' }}">
    <i class="bi bi-check-circle"></i> Approval
</a>
<a href="{{ route('hr.cluster') }}"
    class="sidebar-item {{ ($activeMenu ?? '') === 'cluster' ? 'active' : '' }}">
    <i class="bi bi-pie-chart"></i> Analisis cluster
</a>
<a href="#"
    class="sidebar-item {{ ($activeMenu ?? '') === 'pantau' ? 'active' : '' }}">
    <i class="bi bi-eye"></i> Pantau aset
</a>
<a href="#"
    class="sidebar-item {{ ($activeMenu ?? '') === 'riwayat' ? 'active' : '' }}">
    <i class="bi bi-clock-history"></i> Riwayat
</a>
