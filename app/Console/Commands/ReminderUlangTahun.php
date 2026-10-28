<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReminderUlangTahun extends Command
{
    protected $signature = 'hr:reminder-ulang-tahun
                            {--tanggal= : Tanggal target (Y-m-d), default hari ini}
                            {--dry-run  : Tampilkan saja, tidak kirim notifikasi}';

    protected $description = 'Kirim notifikasi ke HRD untuk karyawan yang berulang tahun hari ini';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : Carbon::today();

        $dryRun = $this->option('dry-run');

        $bulan = $tanggal->month;
        $hari  = $tanggal->day;

        $this->info("=== Reminder Ulang Tahun ===");
        $this->info("Tanggal : {$tanggal->translatedFormat('d F Y')}");
        $this->info("Mode    : " . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->line('');

        $karyawan = Karyawan::whereIn('status', ['kontrak', 'tetap', 'magang', 'training', 'outsourcing'])
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $bulan)
            ->whereDay('birth_date', $hari)
            ->with('department')
            ->get();

        if ($karyawan->isEmpty()) {
            $this->info("Tidak ada karyawan yang berulang tahun hari ini.");
            return self::SUCCESS;
        }

        $this->info("Berulang tahun hari ini: {$karyawan->count()} karyawan");
        $this->table(
            ['Nama', 'Departemen', 'Usia', 'Status'],
            $karyawan->map(fn($k) => [
                $k->nama,
                $k->department?->name ?? '-',
                $k->birth_date->age . ' tahun',
                ucfirst($k->status),
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn('DRY RUN: tidak ada notifikasi dikirim.');
            return self::SUCCESS;
        }

        $hrUsers = User::whereIn('role', ['hrd', 'superadmin', 'admin'])->get();

        if ($hrUsers->isEmpty()) {
            $this->warn('Tidak ada user HRD/admin ditemukan.');
            return self::SUCCESS;
        }

        $lines = $karyawan->map(function ($k) {
            $usia = $k->birth_date->age;
            $dept = $k->department?->name ?? '-';
            return "🎂 {$k->nama} ({$dept}) — {$usia} tahun";
        })->join("\n");

        $sent = 0;
        foreach ($hrUsers as $hrUser) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'reminder_ulang_tahun',
                'notifiable_type' => User::class,
                'notifiable_id'   => $hrUser->id,
                'data'            => json_encode([
                    'title' => "🎉 {$karyawan->count()} Karyawan Berulang Tahun Hari Ini!",
                    'body'  => $lines,
                    'url'   => '/admin/karyawan',
                    'icon'  => 'fas fa-birthday-cake',
                    'color' => '#b7791f',
                    'bg'    => '#fffff0',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sent++;
        }

        $this->info("Notifikasi ulang tahun dikirim ke {$sent} user HRD/admin.");
        return self::SUCCESS;
    }
}
