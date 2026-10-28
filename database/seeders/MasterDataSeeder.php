<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Devision;
use App\Models\Position;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ────────────────────────────────────────────────────
        $departments = [
            ['name' => 'Produksi',         'description' => 'Departemen Produksi'],
            ['name' => 'Quality Control',  'description' => 'Departemen QC / Pengendalian Mutu'],
            ['name' => 'Gudang',           'description' => 'Departemen Gudang / Warehouse'],
            ['name' => 'HRD',              'description' => 'Departemen Human Resource Development'],
            ['name' => 'Keuangan',         'description' => 'Departemen Keuangan & Akuntansi'],
            ['name' => 'IT',               'description' => 'Departemen Information Technology'],
            ['name' => 'Marketing',        'description' => 'Departemen Marketing & Penjualan'],
        ];

        $createdDepts = [];
        foreach ($departments as $dept) {
            $createdDepts[$dept['name']] = Department::firstOrCreate(
                ['name' => $dept['name']],
                ['description' => $dept['description']]
            );
        }

        $this->command->info('Departments seeded: ' . count($departments));

        // ── Positions ──────────────────────────────────────────────────────
        // Catatan: kolom level di tabel positions max varchar(8)
        $positions = [
            ['name' => 'Operator',         'level' => 'Staff',  'salary_grade' => 'Grade 1'],
            ['name' => 'Senior Operator',  'level' => 'Staff',  'salary_grade' => 'Grade 2'],
            ['name' => 'Supervisor',       'level' => 'Spv',    'salary_grade' => 'Grade 3'],
            ['name' => 'Manager',          'level' => 'Manager', 'salary_grade' => 'Grade 4'],
            ['name' => 'HRD Staff',        'level' => 'Staff',  'salary_grade' => 'Grade 2'],
            ['name' => 'Admin',            'level' => 'Staff',  'salary_grade' => 'Grade 1'],
        ];

        foreach ($positions as $pos) {
            Position::firstOrCreate(
                ['name' => $pos['name']],
                ['level' => $pos['level'], 'salary_grade' => $pos['salary_grade']]
            );
        }

        $this->command->info('Positions seeded: ' . count($positions));

        // ── Devisions ──────────────────────────────────────────────────────
        $devisions = [
            'Produksi'        => ['Mixing', 'Filling', 'Packaging'],
            'Quality Control' => ['QC In-Process', 'QC Finish Good'],
            'Gudang'          => ['Gudang Bahan Baku', 'Gudang FG'],
        ];

        $devisionCount = 0;
        foreach ($devisions as $deptName => $divList) {
            if (!isset($createdDepts[$deptName])) {
                continue;
            }
            $deptId = $createdDepts[$deptName]->id;
            foreach ($divList as $divName) {
                Devision::firstOrCreate(
                    ['name' => $divName, 'department_id' => $deptId],
                    []
                );
                $devisionCount++;
            }
        }

        $this->command->info('Devisions seeded: ' . $devisionCount);
        $this->command->info('MasterDataSeeder selesai!');
    }
}
