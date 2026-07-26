<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    use HasFactory;

    protected $table = 'aset';
    protected $primaryKey = 'id_aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori',
        'nilai_perolehan',
        'kondisi',
        'status',
        'deskripsi',
        'lokasi',
    ];

    protected $casts = [
        'nilai_perolehan' => 'decimal:2',
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // 1 aset → M peminjaman
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'aset_id', 'id_aset');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeTersedia($query)
    {
        return $query->where('status', 'Tersedia');
    }

    public function scopeDipinjam($query)
    {
        return $query->where('status', 'Dipinjam');
    }

    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function isTersedia(): bool
    {
        return $this->status === 'Tersedia';
    }
}