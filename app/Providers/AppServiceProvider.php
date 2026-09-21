<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Admin sidebar: badge notifikasi belum dibaca ─────────────────
        View::composer('admin.partials.sidebar', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = Notifikasi::where('pengguna_id', Auth::user()->id_pengguna)
                    ->where('dibaca', false)
                    ->count();
            }
            $view->with('notifBelumDibacaCount', $count);
        });

        // ── Karyawan sidebar: badge notifikasi belum dibaca ───────────────
        // Query langsung ke DB sehingga selalu akurat di SEMUA halaman karyawan,
        // tidak bergantung pada variabel $karyawan yang dikirim controller
        View::composer('karyawan.partials.sidebar', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = Notifikasi::where('pengguna_id', Auth::user()->id_pengguna)
                    ->where('dibaca', false)
                    ->count();
            }
            $view->with('karyawanNotifCount', $count);
        });
    }
}
