<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ClusteringController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════════════
// HALAMAN UTAMA — arahkan ke login
// PENTING: Ini yang tadinya HILANG sehingga muncul 404
// ══════════════════════════════════════════════════════════════════════
Route::get('/', function () {
    return redirect()->route('login');
});

// ══════════════════════════════════════════════════════════════════════
// AUTENTIKASI (hanya bisa diakses jika BELUM login)
// ══════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get ('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ══════════════════════════════════════════════════════════════════════
// ADMIN ASET — dilindungi middleware auth + role
// ══════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:admin_aset'])->group(function () {

    // Clustering
    Route::get ('/clustering',             [ClusteringController::class, 'index'])->name('clustering.index');
    Route::post('/clustering/jalankan',    [ClusteringController::class, 'jalankan'])->name('clustering.jalankan');
    Route::post('/clustering/elbow',       [ClusteringController::class, 'elbow'])->name('clustering.elbow');
    Route::get ('/clustering/detail/{id}', [ClusteringController::class, 'detail'])->name('clustering.detail');

    // Laporan
    Route::get('/laporan',              [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-pdf',   [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // Dashboard & fitur Admin Aset lainnya
    Route::get ('/admin/dashboard',    [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get ('/admin/aset',         [AdminController::class, 'aset'])->name('admin.aset');
    Route::post('/admin/aset',         [AdminController::class, 'simpanAset'])->name('admin.aset.simpan');
    Route::put ('/admin/aset/{id}',    [AdminController::class, 'updateAset'])->name('admin.aset.update');
    Route::delete('/admin/aset/{id}',  [AdminController::class, 'hapusAset'])->name('admin.aset.hapus');
    Route::get ('/admin/peminjaman',                [AdminController::class, 'peminjaman'])->name('admin.peminjaman');
    Route::post('/admin/peminjaman/ambil/{id}',     [AdminController::class, 'konfirmasiAmbil'])->name('admin.peminjaman.ambil');
    Route::post('/admin/peminjaman/konfirmasi',     [AdminController::class, 'konfirmasiKembali'])->name('admin.peminjaman.konfirmasi');
    Route::get ('/admin/laporan',                   [AdminController::class, 'laporan'])->name('admin.laporan');
    Route::get ('/admin/cluster',                   [AdminController::class, 'cluster'])->name('admin.cluster');
    Route::post('/admin/cluster/jalankan',          [AdminController::class, 'jalankanCluster'])->name('admin.cluster.jalankan');

});

// ══════════════════════════════════════════════════════════════════════
// HR / KEPALA DIVISI — dilindungi middleware auth + role
// ══════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:hr_kepala_divisi'])->group(function () {

    Route::get ('/hr/dashboard', [HrController::class, 'dashboard'])->name('hr.dashboard');
    Route::get ('/hr/approval',  [HrController::class, 'approval'])->name('hr.approval');
    Route::post('/hr/approval',  [HrController::class, 'prosesApproval'])->name('hr.approval.proses');

});

// ══════════════════════════════════════════════════════════════════════
// KARYAWAN — dilindungi middleware auth + role
// ══════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:karyawan'])->group(function () {

    Route::get ('/karyawan/dashboard',  [KaryawanController::class, 'dashboard'])->name('karyawan.dashboard');
    Route::get ('/karyawan/pengajuan',  [KaryawanController::class, 'pengajuan'])->name('karyawan.pengajuan');
    Route::post('/karyawan/pengajuan',  [KaryawanController::class, 'store'])->name('karyawan.pengajuan.store');
    Route::get ('/karyawan/status',     [KaryawanController::class, 'status'])->name('karyawan.status');
    Route::get ('/karyawan/notifikasi', [KaryawanController::class, 'notifikasi'])->name('karyawan.notifikasi');

});