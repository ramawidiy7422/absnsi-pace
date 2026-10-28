<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lemburan extends Model
{
    protected $table = 'lemburan';

    protected $fillable = [
        'karyawan_id', 'spk_lembur_id', 'jenis',
        'tanggal', 'jam_mulai', 'jam_selesai',
        'total_jam', 'status', 'alasan', 'keterangan',
        'deskripsi_kerja', 'is_mendadak', 'alasan_tolak',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'approved_at'  => 'datetime',
        'is_mendadak'  => 'boolean',
    ];

    public function karyawan() { return $this->belongsTo(Karyawan::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function spk()      { return $this->belongsTo(SpkLembur::class, 'spk_lembur_id'); }

    public function getJenisBadgeAttribute(): array
    {
        return match($this->jenis ?? 'mandiri') {
            'spk'    => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Berdasar SPK',   'icon' => 'fas fa-file-alt'],
            'auto'   => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Auto-detected',  'icon' => 'fas fa-magic'],
            default  => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Mandiri',         'icon' => 'fas fa-user'],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'approved' => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Disetujui'],
            'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Ditolak'],
            default    => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Menunggu'],
        };
    }
}
