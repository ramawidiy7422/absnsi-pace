<?php

namespace App\Console\Commands;

use App\Models\CutiSaldo;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResetSaldoCuti extends Command
{
    protected $signature = 'cuti:reset-saldo
                            {--tahun= : Tahun target (default: tahun berjalan)}
                            {--dry-run : Tampilkan saja, tidak simpan}
                            {--force : Overwrite saldo yang sudah ada}
                            {--no-carry-over : Tidak membawa sisa cuti tahun lalu}';

    protected $description = 'Reset / inisialisasi saldo cuti tahunan untuk semua karyawan aktif, dengan carry-over sisa cuti';

    public function handle(): int
    {
        $tahun      = (int) ($this->option('tahun') ?? now()->year);
        $dryRun     = $this->option('dry-run');
        $force      = $this->option('force');
        $noCarryOver = $this->option('no-carry-over');

        $statusAktif = ['kontrak', 'tetap', 'magang', 'training'];

        $karyawanList = Karyawan::whereIn('status', $statusAktif)
            ->whereNull('tanggal_keluar')
            ->orderBy('nama')
            ->get();

        $this->info("=== Reset Saldo Cuti Tahun {$tahun} ===");
        $this->info("Total karyawan aktif: {$karyawanList->count()}");
        $this->info("Carry-over          : " . ($noCarryOver ? 'DINONAKTIFKAN' : 'AKTIF (bawa sisa cuti tahun lalu)'));
        $this->info("Mode                : " . ($dryRun ? 'DRY-RUN (tidak ada perubahan)' : ($force ? 'FORCE (overwrite)' : 'NORMAL (skip yang sudah ada)')));
        $this->newLine();

        $diproses = 0;
        $diskip   = 0;
        $rows     = [];

        foreach ($karyawanList as $k) {
            $jatah      = CutiSaldo::hitungJatah($k->id, $tahun);
            $carryOver  = $noCarryOver ? 0 : CutiSaldo::hitungCarryOver($k->id, $tahun);

            $existing = CutiSaldo::where('karyawan_id', $k->id)->where('tahun', $tahun)->first();

            if ($existing && !$force) {
                $diskip++;
                $rows[] = [$k->nik ?? '-', $k->nama, $jatah, $carryOver, $existing->jatah, 'SKIP (sudah ada)'];
                continue;
            }

            if (!$dryRun) {
                if ($existing) {
                    $existing->update(['jatah' => $jatah, 'terpakai' => 0, 'carry_over' => $carryOver]);
                } else {
                    CutiSaldo::create([
                        'karyawan_id' => $k->id,
                        'tahun'       => $tahun,
                        'jatah'       => $jatah,
                        'terpakai'    => 0,
                        'carry_over'  => $carryOver,
                    ]);
                }
            }

            $diproses++;
            $status = $existing ? 'UPDATE' : 'BARU';
            $rows[] = [$k->nik ?? '-', $k->nama, $jatah, $carryOver, $existing?->jatah ?? '-', $dryRun ? "[dry-run] {$status}" : $status];
        }

        $this->table(['NIK', 'Nama', 'Jatah Baru', 'Carry-Over', 'Jatah Lama', 'Status'], $rows);
        $this->newLine();
        $this->info("Diproses: {$diproses} | Skip: {$diskip}");

        if (!$dryRun && $diproses > 0) {
            // Kirim notifikasi ke HRD bahwa reset sudah berjalan
            $hrUsers = User::whereIn('role', ['superadmin', 'admin', 'hrd'])->get();
            foreach ($hrUsers as $u) {
                DB::table('notifications')->insert([
                    'id'              => Str::uuid(),
                    'type'            => 'reset_cuti',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $u->id,
                    'data'            => json_encode([
                        'title' => "Saldo Cuti {$tahun} Berhasil Direset",
                        'body'  => "Reset saldo cuti tahunan {$tahun} selesai. {$diproses} karyawan diproses, {$diskip} diskip.",
                        'icon'  => 'fas fa-umbrella-beach',
                        'color' => '#276749',
                        'bg'    => '#f0fff4',
                        'url'   => '/admin/cuti-saldo',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->info("Notifikasi dikirim ke {$hrUsers->count()} admin/HRD.");
        }

        return 0;
    }
}
