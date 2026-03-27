<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            ['nama_shift' => 'Shift Pagi',   'jam_masuk' => '06:00:00', 'jam_keluar' => '14:00:00', 'toleransi_menit' => 15],
            ['nama_shift' => 'Shift Siang',  'jam_masuk' => '14:00:00', 'jam_keluar' => '22:00:00', 'toleransi_menit' => 15],
            ['nama_shift' => 'Shift Malam',  'jam_masuk' => '22:00:00', 'jam_keluar' => '06:00:00', 'toleransi_menit' => 15],
            ['nama_shift' => 'Shift Normal', 'jam_masuk' => '08:00:00', 'jam_keluar' => '17:00:00', 'toleransi_menit' => 15],
        ];

        foreach ($shifts as $shift) {
            \App\Models\Shift::create($shift);
        }
    }
}
