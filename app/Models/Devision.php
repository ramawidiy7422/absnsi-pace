<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devision extends Model
{
    protected $table = 'devisions';

    protected $fillable = ['name', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
