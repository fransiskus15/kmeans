<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'id_peminjaman';

    protected $fillable = [
        'pengguna_id',
        'aset_id',
        'tgl_pengajuan',
        'tgl_pinjam',
        'tgl_rencana_kembali',
        'tgl_kembali_aktual',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tgl_pengajuan'       => 'date',
        'tgl_pinjam'          => 'date',
        'tgl_rencana_kembali' => 'date',
        'tgl_kembali_aktual'  => 'date',
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // M peminjaman → 1 pengguna (karyawan yang mengajukan)
    public function peminjam()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'id_pengguna');
    }

    // M peminjaman → 1 aset
    public function aset()
    {
        return $this->belongsTo(Aset::class, 'aset_id', 'id_aset');
    }

    // 1 peminjaman → 1 approval (relasi 1:1)
    public function approval()
    {
        return $this->hasOne(Approval::class, 'peminjaman_id', 'id_peminjaman');
    }

    // 1 peminjaman → M notifikasi
    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'peminjaman_id', 'id_peminjaman');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'Menunggu Persetujuan');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['Disetujui', 'Dipinjam']);
    }

    public function scopeTerlambat($query)
    {
        return $query->where('tgl_rencana_kembali', '<', now())
                      ->whereNull('tgl_kembali_aktual');
    }

    // Hitung apakah pengembalian terlambat
    public function isTerlambat(): bool
    {
        if (!$this->tgl_kembali_aktual) {
            return Carbon::now()->gt($this->tgl_rencana_kembali);
        }
        return Carbon::parse($this->tgl_kembali_aktual)
                     ->gt(Carbon::parse($this->tgl_rencana_kembali));
    }

    // Hitung durasi peminjaman dalam hari
    public function durasiHari(): ?int
    {
        if (!$this->tgl_kembali_aktual) return null;
        return Carbon::parse($this->tgl_pinjam)
                     ->diffInDays(Carbon::parse($this->tgl_kembali_aktual));
    }
}
