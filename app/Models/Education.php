<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = ['karyawan_id', 'level', 'major', 'institution', 'year'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
