<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Aset</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .header p  { font-size: 10px; color: #475569; margin-top: 3px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 10px; color: #475569; }
        .stat-row { display: flex; gap: 10px; margin-bottom: 14px; }
        .stat-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 10px; text-align: center; }
        .stat-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .stat-box .val   { font-size: 15px; font-weight: bold; margin-top: 2px; }
        .val-total    { color: #1e40af; }
        .val-tepat    { color: #059669; }
        .val-terlambat{ color: #dc2626; }
        .val-pct      { color: #d97706; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #1e293b; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-terlambat  { background: #fee2e2; color: #dc2626; }
        .badge-tepat      { background: #dcfce7; color: #059669; }
        .badge-pending    { background: #e0e7ff; color: #3730a3; }
        .footer { text-align: right; font-size: 9px; color: #94a3b8; margin-top: 12px; }
        .filter-info { font-size: 9px; color: #475569; background: #f1f5f9; padding: 5px 10px; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN PEMINJAMAN ASET</h1>
        <p>Sistem Manajemen Aset — Admin Aset</p>
    </div>

    <div class="meta">
        <span>Periode: <strong>{{ \Carbon\Carbon::parse($filter['periode_from'])->format('d M Y') }}</strong>
              s/d <strong>{{ \Carbon\Carbon::parse($filter['periode_to'])->format('d M Y') }}</strong></span>
        <span>Dibuat: {{ $generated }}</span>
    </div>

    <div class="filter-info">
        Kategori: <strong>{{ $filter['kategori'] === 'all' ? 'Semua' : $filter['kategori'] }}</strong> &nbsp;|&nbsp;
        Status: <strong>{{ $filter['status'] === 'all' ? 'Semua' : ucfirst(str_replace('_', ' ', $filter['status'])) }}</strong>
    </div>

    {{-- Statistik --}}
    <div class="stat-row">
        <div class="stat-box">
            <div class="label">Total Transaksi</div>
            <div class="val val-total">{{ $statistik['total'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Tepat Waktu</div>
            <div class="val val-tepat">{{ $statistik['tepat_waktu'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Terlambat</div>
            <div class="val val-terlambat">{{ $statistik['terlambat'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Tingkat Terlambat</div>
            <div class="val val-pct">{{ $statistik['pct_terlambat'] }}%</div>
        </div>
    </div>

    {{-- Tabel data --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Divisi</th>
                <th>Aset</th>
                <th>Kategori</th>
                <th>Tgl Pengajuan</th>
                <th>Tgl Pinjam</th>
                <th>Batas Kembali</th>
                <th>Tgl Kembali Aktual</th>
                <th>Durasi (hr)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjaman as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->peminjam->nama ?? '-' }}</td>
                <td>{{ $item->peminjam->divisi ?? '-' }}</td>
                <td>{{ $item->aset->nama_aset ?? '-' }}</td>
                <td>{{ $item->aset->kategori ?? '-' }}</td>
                <td>{{ $item->tgl_pengajuan ? \Carbon\Carbon::parse($item->tgl_pengajuan)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->tgl_pinjam ? \Carbon\Carbon::parse($item->tgl_pinjam)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->tgl_rencana_kembali ? \Carbon\Carbon::parse($item->tgl_rencana_kembali)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->tgl_kembali_aktual ? \Carbon\Carbon::parse($item->tgl_kembali_aktual)->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($item->tgl_pinjam && $item->tgl_kembali_aktual)
                        {{ \Carbon\Carbon::parse($item->tgl_pinjam)->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_aktual)) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @php
                        $terlambat = $item->tgl_kembali_aktual &&
                            \Carbon\Carbon::parse($item->tgl_kembali_aktual)->gt(\Carbon\Carbon::parse($item->tgl_rencana_kembali));
                    @endphp
                    @if($terlambat)
                        <span class="badge badge-terlambat">Terlambat</span>
                    @elseif($item->tgl_kembali_aktual)
                        <span class="badge badge-tepat">Tepat Waktu</span>
                    @else
                        <span class="badge badge-pending">Belum Kembali</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center;padding:20px;color:#94a3b8;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini digenerate otomatis oleh Sistem Manajemen Aset — {{ $generated }}
    </div>

</body>
</html>
