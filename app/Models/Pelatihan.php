<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $table = 'pelatihan_karyawan';

    protected $fillable = [
        'karyawan_id', 'nama_pelatihan', 'jenis', 'tipe', 'penyelenggara',
        'tanggal_mulai', 'tanggal_selesai', 'tanggal_expired',
        'status', 'nomor_sertifikat', 'lampiran', 'catatan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_expired' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // Label jenis pelatihan (materi/topik)
    public static function jenisLabel(): array
    {
        return [
            'cpob'     => 'CPOB',
            'k3'       => 'K3',
            'hygiene'  => 'Higiene Personal',
            'sop'      => 'SOP / Prosedur',
            'internal' => 'Internal Lainnya',
            'external' => 'Eksternal Lainnya',
            'lainnya'  => 'Lainnya',
        ];
    }

    // Label tipe penyelenggaraan
    public static function tipeLabel(): array
    {
        return [
            'internal'  => 'Internal',
            'eksternal' => 'Eksternal',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::jenisLabel()[$this->jenis] ?? ucfirst($this->jenis);
    }

    public function getTipeLabelAttribute(): string
    {
        return self::tipeLabel()[$this->tipe] ?? ucfirst($this->tipe);
    }

    public function getTipeBadgeAttribute(): array
    {
        return match($this->tipe) {
            'eksternal' => ['bg' => '#ebf8ff', 'color' => '#2b6cb0', 'border' => '#90cdf4', 'icon' => 'fa-globe'],
            default     => ['bg' => '#f0fff4', 'color' => '#276749', 'border' => '#9ae6b4', 'icon' => 'fa-home'],
        };
    }

    // Apakah sertifikat sudah expired?
    public function getIsExpiredAttribute(): bool
    {
        return $this->tanggal_expired && $this->tanggal_expired->isPast();
    }

    // Apakah akan expired dalam 30 hari ke depan?
    public function getIsNearExpiredAttribute(): bool
    {
        return $this->tanggal_expired
            && !$this->is_expired
            && (int) now()->startOfDay()->diffInDays($this->tanggal_expired->startOfDay()) <= 30;
    }

    // Sisa hari sebelum expired (negatif = sudah expired)
    public function getSisaHariAttribute(): ?int
    {
        if (!$this->tanggal_expired) return null;
        return (int) now()->startOfDay()->diffInDays($this->tanggal_expired->startOfDay(), false);
    }

    public function getStatusColorAttribute(): array
    {
        if ($this->status === 'tidak_lulus')     return ['bg' => '#fff5f5', 'color' => '#c53030', 'border' => '#fed7d7'];
        if ($this->status === 'sedang_berjalan') return ['bg' => '#ebf8ff', 'color' => '#2b6cb0', 'border' => '#90cdf4'];
        if ($this->status === 'belum_ikut')      return ['bg' => '#f7fafc', 'color' => '#718096', 'border' => '#e2e8f0'];
        if ($this->is_expired)                   return ['bg' => '#fff5f5', 'color' => '#c53030', 'border' => '#fed7d7'];
        if ($this->is_near_expired)              return ['bg' => '#fffaf0', 'color' => '#c05621', 'border' => '#fbd38d'];
        return ['bg' => '#f0fff4', 'color' => '#276749', 'border' => '#9ae6b4'];
    }
}
