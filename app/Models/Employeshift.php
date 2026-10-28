<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employeshift extends Model
{
    protected $table = 'employeshifts';

    protected $fillable = ['karyawan_id', 'shift_id', 'date', 'keterangan'];

    protected $casts = ['date' => 'date'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
