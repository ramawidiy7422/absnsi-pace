<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warninglettter extends Model
{
    protected $table = 'warningletters';

    protected $fillable = ['karyawan_id', 'type', 'date', 'description', 'valid_until', 'status'];

    protected $casts = [
        'date'        => 'date',
        'valid_until' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
