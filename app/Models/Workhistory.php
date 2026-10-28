<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workhistory extends Model
{
    protected $table = 'workhistories';

    protected $fillable = ['karyawan_id', 'start_date', 'end_date', 'type', 'jabatan', 'departemen', 'status_karyawan', 'description'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
