<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'karyawan_id', 'tanggal', 'jenis', 'keterangan',
        'lampiran', 'status', 'approved_by', 'approved_at', 'catatan_approver',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLabelJenisAttribute(): string
    {
        return match($this->jenis) {
            'sakit' => 'Sakit',
            'izin'  => 'Izin',
            'alfa'  => 'Alfa / Tanpa Keterangan',
            default => ucfirst($this->jenis),
        };
    }

    public function getBadgeColorAttribute(): array
    {
        return match($this->jenis) {
            'sakit' => ['bg' => '#ebf8ff', 'text' => '#2b6cb0'],
            'izin'  => ['bg' => '#fef9c3', 'text' => '#854d0e'],
            'alfa'  => ['bg' => '#fed7e2', 'text' => '#c53030'],
            default => ['bg' => '#f7fafc', 'text' => '#718096'],
        };
    }
}
