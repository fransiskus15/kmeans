<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilCluster extends Model 
{
    use HasFactory;

    protected $table = 'profil_cluster';
    protected $primaryKey = 'id_profil';

    protected $fillable = [
        'pengguna_id',
        'label_cluster',
        'nama_cluster',
        'nilai_f1',
        'nilai_f2',
        'nilai_f3',
        'nilai_f4',
        'nilai_f5',
        'silhouette_score',
        'tgl_diperbarui',
    ];

    protected $casts = [
        'nilai_f1'         => 'decimal:2',
        'nilai_f2'         => 'decimal:2',
        'nilai_f3'         => 'decimal:2',
        'nilai_f4'         => 'integer',
        'nilai_f5'         => 'decimal:2',
        'silhouette_score' => 'decimal:4',
        'tgl_diperbarui'   => 'datetime',
    ];

    // ── RELASI ────────────────────────────────────────────────────────────

    // 1 profil cluster → 1 pengguna (relasi 1:1, inverse dari hasOne)
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'id_pengguna');
    }

    // ── SCOPE / HELPER ───────────────────────────────────────────────────

    public function scopeClusterA($query)
    {
        return $query->where('label_cluster', 'A');
    }

    public function scopeClusterB($query)
    {
        return $query->where('label_cluster', 'B');
    }

    public function scopeClusterC($query)
    {
        return $query->where('label_cluster', 'C');
    }

    // Warna badge untuk tampilan UI sesuai label cluster
    public function warnaLabel(): string
    {
        return match ($this->label_cluster) {
            'A' => 'green',
            'B' => 'amber',
            'C' => 'red',
            default => 'gray',
        };
    }
}