<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KoreksiAbsensi extends Model
{
    protected $table = 'koreksi_absensi';

    protected $fillable = [
        'karyawan_id', 'absensi_id', 'tanggal', 'tipe',
        'jam_masuk_diajukan', 'jam_keluar_diajukan',
        'alasan', 'lampiran', 'status',
        'approved_by', 'approved_at', 'catatan_approver',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLabelStatusAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => $this->status,
        };
    }

    public function getLabelTipeAttribute(): string
    {
        return match($this->tipe) {
            'masuk'    => 'Jam Masuk',
            'keluar'   => 'Jam Keluar',
            'keduanya' => 'Masuk & Keluar',
            default    => $this->tipe,
        };
    }
}
