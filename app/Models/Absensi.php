<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id', 'shift_id', 'tanggal', 'jam_masuk', 'jam_keluar',
        'durasi_menit', 'pulang_cepat',
        'status', 'metode', 'confidence_masuk', 'confidence_keluar',
        'foto_masuk', 'foto_keluar', 'keterangan',
    ];

    // Format durasi menit → "X jam Y menit"
    public function getDurasiFormatAttribute(): string
    {
        if (!$this->durasi_menit) return '-';
        $jam   = intdiv($this->durasi_menit, 60);
        $menit = $this->durasi_menit % 60;
        return $jam > 0 ? "{$jam}j {$menit}m" : "{$menit}m";
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
