<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'pengguna_id',
        'peminjaman_id',
        'judul',
        'pesan',
        'tipe',
        'dibaca',
        'dibaca_pada',
    ];

    protected $casts = [
        'dibaca'      => 'boolean',
        'dibaca_pada' => 'datetime',
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // M notifikasi → 1 pengguna (penerima)
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'id_pengguna');
    }

    // M notifikasi → 1 peminjaman (pemicu, boleh null)
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id', 'id_peminjaman');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopeTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    // Tandai notifikasi sebagai sudah dibaca
    public function tandaiDibaca(): void
    {
        $this->update([
            'dibaca'      => true,
            'dibaca_pada' => now(),
        ]);
    }
}