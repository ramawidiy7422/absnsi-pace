<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = ['name', 'description', 'spv_user_id', 'manager_user_id'];

    public function devisions()
    {
        return $this->hasMany(Devision::class);
    }

    public function spvUser()
    {
        return $this->belongsTo(User::class, 'spv_user_id');
    }

    public function managerUser()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }
}
