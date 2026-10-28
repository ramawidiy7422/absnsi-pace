<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id', 'shift_id', 'leaverequest_id', 'izin_id', 'izin_terlambat_id', 'izin_pulang_cepat_id', 'tanggal', 'jam_masuk', 'jam_keluar',
        'durasi_menit', 'pulang_cepat',
        'status', 'metode', 'confidence_masuk', 'confidence_keluar',
        'foto_masuk', 'foto_keluar', 'keterangan', 'keterangan_terlambat', 'keterangan_pulang_cepat',
    ];

    public function getHasAlasanTerlambatAttribute(): bool
    {
        return $this->status === 'terlambat' && !empty($this->keterangan_terlambat);
    }

    /** CSS class badge sesuai status + ada/tidaknya keterangan terlambat */
    public function getBadgeClassAttribute(): string
    {
        if ($this->status === 'terlambat') {
            return $this->has_alasan_terlambat ? 'badge-terlambat-izin' : 'badge-terlambat';
        }
        return 'badge-' . $this->status;
    }

    /** Label badge yang ditampilkan */
    public function getBadgeLabelAttribute(): string
    {
        $map = [
            'hadir'     => 'Hadir',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alpha'     => 'Alpha',
            'cuti'      => 'Cuti',
            'terlambat' => $this->has_alasan_terlambat ? 'Terlambat (Berizin)' : 'Terlambat',
        ];
        return $map[$this->status] ?? ucfirst($this->status);
    }

    protected $casts = ['tanggal' => 'date'];

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

    public function leaverequest()
    {
        return $this->belongsTo(Leaverequest::class);
    }

    public function izinTerlambat()
    {
        return $this->belongsTo(IzinTerlambat::class);
    }
}
