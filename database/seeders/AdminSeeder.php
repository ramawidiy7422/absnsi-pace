<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@pabrikfarmasi.com',
            'password' => bcrypt('admin123'),
            'role'     => 'superadmin',
        ]);

        \App\Models\User::create([
            'name'     => 'Operator HRD',
            'email'    => 'hrd@pabrikfarmasi.com',
            'password' => bcrypt('hrd123'),
            'role'     => 'operator',
        ]);
    }
}
