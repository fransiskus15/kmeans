<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable; 

    // Nama tabel (karena tidak mengikuti konvensi plural default 'penggunas')
    protected $table = 'pengguna';

    // Primary key custom (bukan 'id' default Laravel)
    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'divisi',
        'no_telepon',
        'status_akun',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',   // Laravel 10+ otomatis hash saat set
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // 1 pengguna (karyawan) → M peminjaman yang diajukan
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'pengguna_id', 'id_pengguna');
    }

    // 1 pengguna (HR) → M approval yang diberikan
    public function approval()
    {
        return $this->hasMany(Approval::class, 'pengguna_id', 'id_pengguna');
    }

    // 1 pengguna → 1 profil cluster (relasi 1:1)
    public function profilCluster()
    {
        return $this->hasOne(Profilcluster::class, 'pengguna_id', 'id_pengguna');
    }

    // 1 pengguna → M notifikasi yang diterima
    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'pengguna_id', 'id_pengguna');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeKaryawan($query)
    {
        return $query->where('role', 'karyawan');
    }

    public function scopeHr($query)
    {
        return $query->where('role', 'hr_kepala_divisi');
    }

    public function scopeAdminAset($query)
    {
        return $query->where('role', 'admin_aset');
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr_kepala_divisi';
    }

    public function isAdminAset(): bool
    {
        return $this->role === 'admin_aset';
    }
}
