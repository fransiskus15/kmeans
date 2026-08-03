<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses halaman berdasarkan peran (role) pengguna.
 *
 * Cara pakai di routes:
 *   Route::middleware(['auth', 'role:admin_aset'])->group(...)
 *   Route::middleware(['auth', 'role:hr_kepala_divisi,admin_aset'])->group(...)  // multi-role
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login sama sekali
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Role pengguna tidak termasuk dalam daftar role yang diizinkan
        if (!in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}