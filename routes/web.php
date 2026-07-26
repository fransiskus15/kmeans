<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ClusteringController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');

Route::middleware(['auth', 'role:admin_aset'])->group(function () {

     // Clustering
     Route::get ('/clustering',            [ClusteringController::class, 'index']);
     Route::post('/clustering/jalankan',   [ClusteringController::class, 'jalankan']);
    Route::post('/clustering/elbow',      [ClusteringController::class, 'elbow']);
     Route::get ('/clustering/detail/{id}',[ClusteringController::class, 'detail']);

     // Laporan
     Route::get ('/laporan' , [LaporanController::class, 'index']);
     Route::get ('/laporan/export-pdf', [LaporanController::class, 'exportPdf']);
     Route::get ('/laporan/export-excel', [LaporanController::class, 'exportExcel']);
});

Route::get('/hr/dashboard', [HrController::class, 'dashboard'])->name('hr.dashboard');
Route::get('/hr/approval', [HrController::class, 'approval'])->name('hr.approval');
Route::get('/hr/cluster', [HrController::class, 'cluster'])->name('hr.cluster');
 
Route::get('/karyawan/dashboard', [KaryawanController::class, 'dashboard'])->name('karyawan.dashboard');
Route::get('/karyawan/pengajuan', [KaryawanController::class, 'pengajuan'])->name('karyawan.pengajuan');
Route::post('/karyawan/pengajuan', [KaryawanController::class, 'store'])->name('karyawan.pengajuan.store');
Route::get('/karyawan/notifikasi', [KaryawanController::class, 'notifikasi'])->name('karyawan.notifikasi');

Route::get('/admin/aset', [AdminController::class, 'aset'])->name('admin.aset');
Route::get('/admin/peminjaman', [AdminController::class, 'peminjaman'])->name('admin.peminjaman');
Route::get('/admin/laporan', [AdminController::class, 'laporan'])->name('admin.laporan');
Route::post('/admin/peminjaman/konfirmasi', [AdminController::class, 'konfirmasiKembali'])->name('admin.peminjaman.konfirmasi');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
