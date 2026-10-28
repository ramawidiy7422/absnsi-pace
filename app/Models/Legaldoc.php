<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legaldoc extends Model
{
    protected $table = 'legaldocs';

    protected $fillable = ['karyawan_id', 'nik', 'kk', 'npwp', 'bpjs_tk', 'bpjs_kesehatan'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
