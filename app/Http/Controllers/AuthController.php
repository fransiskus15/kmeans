<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // TAMPILKAN HALAMAN LOGIN
    // ══════════════════════════════════════════════════════════════════════
    public function showLogin()
    {
        // Jika sudah login, langsung arahkan ke dashboard sesuai peran
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    // ══════════════════════════════════════════════════════════════════════
    // PROSES LOGIN
    // ══════════════════════════════════════════════════════════════════════
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Cek status akun sebelum autentikasi
        $pengguna = Pengguna::where('email', $credentials['email'])->first();

        if ($pengguna && $pengguna->status_akun === 'nonaktif') {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi Admin Aset.',
            ]);
        }

        // Percobaan login menggunakan guard default (yang sudah diarahkan ke Pengguna)
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user())
            ->with('success', 'Berhasil masuk. Selamat datang, ' . Auth::user()->nama . '!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════════════════════════════════════════
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar dari sistem.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPER: Arahkan ke dashboard sesuai peran
    // ══════════════════════════════════════════════════════════════════════
    private function redirectByRole(Pengguna $pengguna)
    {
        return match ($pengguna->role) {
            'karyawan'          => redirect()->route('karyawan.dashboard'),
            'hr_kepala_divisi'  => redirect()->route('hr.dashboard'),
            'admin_aset'        => redirect()->route('admin.dashboard'),
            default             => redirect()->route('login'),
        };
    }
}