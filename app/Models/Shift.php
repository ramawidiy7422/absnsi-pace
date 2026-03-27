<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['nama_shift', 'jam_masuk', 'jam_keluar', 'toleransi_menit', 'status'];

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }
}
