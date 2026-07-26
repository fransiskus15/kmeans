<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approval';
    protected $primaryKey = 'id_approval';

    protected $fillable = [
        'peminjaman_id',
        'pengguna_id',
        'keputusan',
        'catatan',
        'tgl_approval',
        'cluster_saat_approval',
    ];

    protected $casts = [
        'tgl_approval' => 'datetime',
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // 1 approval → 1 peminjaman (relasi 1:1, inverse dari hasOne)
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id', 'id_peminjaman');
    }

    // M approval → 1 pengguna (HR yang memberi keputusan)
    public function pemberiKeputusan()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'id_pengguna');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeDisetujui($query)
    {
        return $query->where('keputusan', 'Disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('keputusan', 'Ditolak');
    }

    public function isDisetujui(): bool
    {
        return $this->keputusan === 'Disetujui';
    }
}
