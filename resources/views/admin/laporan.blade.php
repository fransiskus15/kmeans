@extends('layouts.admin')

@section('title', 'Laporan — Batam Pos')

@section('page-title', 'Laporan Admin Aset')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <h6 class="fw-bold mb-3">
                Filter laporan
            </h6>

            <form>

                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">
                            Periode dari
                        </label>

                        <input type="date" class="form-control" value="2025-06-01">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">
                            Periode sampai
                        </label>

                        <input type="date" class="form-control" value="2025-06-30">
                    </div>

                    <div class="col-md-3">

                        <label class="form-label">
                            Kategori aset
                        </label>

                        <select class="form-select">

                            <option>Semua kategori</option>
                            <option>Kamera</option>
                            <option>Laptop</option>
                            <option>Drone</option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select class="form-select">

                            <option>Semua status</option>
                            <option>Tepat waktu</option>
                            <option>Terlambat</option>

                        </select>

                    </div>

                </div>

                <div class="mt-3">

                    <button class="btn btn-outline-dark">
                        <i class="bi bi-funnel"></i>
                        Terapkan
                    </button>

                    <button class="btn btn-outline-dark">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Ekspor PDF
                    </button>

                    <button class="btn btn-outline-dark">
                        <i class="bi bi-file-earmark-excel"></i>
                        Ekspor Excel
                    </button>

                </div>

            </form>

        </div>

    </div>



    <div class="row mt-4">

        <div class="col-md-3">

            <div class="card shadow-sm">

                <div class="card-body">

                    <small>Total transaksi</small>

                    <h3 class="fw-bold">
                        47
                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm">

                <div class="card-body">

                    <small>Tepat waktu</small>

                    <h3 class="text-success fw-bold">
                        39
                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm">

                <div class="card-body">

                    <small>Terlambat</small>

                    <h3 class="text-danger fw-bold">
                        8
                    </h3>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm">

                <div class="card-body">

                    <small>Tingkat terlambat</small>

                    <h3 class="text-warning fw-bold">
                        17%
                    </h3>

                </div>

            </div>

        </div>

    </div>



    <div class="card mt-4 shadow-sm">

        <div class="card-body p-0">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Peminjam</th>

                        <th>Aset</th>

                        <th>Tgl Pinjam</th>

                        <th>Tgl Kembali</th>

                        <th>Durasi</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($laporan as $item)

                    <tr>

                        <td>{{ $item['nama'] }}</td>

                        <td>{{ $item['aset'] }}</td>

                        <td>{{ $item['tgl_pinjam'] }}</td>

                        <td>{{ $item['tgl_kembali'] }}</td>

                        <td>{{ $item['durasi'] }}</td>

                        <td>


                            @if(str_contains($item['status'],'Tepat'))

                            <span class="badge bg-success">
                                {{ $item['status'] }}
                            </span>

                            @else

                            <span class="badge bg-danger">
                                {{ $item['status'] }}
                            </span>

                            @endif

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="card-footer bg-white text-end">

            Menampilkan 4 dari 47 transaksi

        </div>

    </div>

</div>
@endsection