<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinPulangCepat extends Model
{
    protected $table = 'izin_pulang_cepat';

    protected $fillable = [
        'karyawan_id', 'tanggal', 'keterangan', 'status',
        'input_by', 'approved_by', 'approved_at', 'catatan_approver',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function inputOleh()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
