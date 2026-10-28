<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCloseAbsensiShiftMalam extends Command
{
    protected $signature = 'absensi:auto-close-shift-malam
                            {--dry-run : Tampilkan saja, tidak update DB}';

    protected $description = 'Tutup otomatis absensi shift malam (cross-midnight) yang belum scan keluar setelah jam_keluar shift lewat';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info("=== Auto-Close Absensi Shift Malam ===");
        $this->info("Mode : " . ($dryRun ? 'DRY RUN' : 'LIVE'));

        // Kemarin malam — karyawan masuk kemarin, keluar dini hari tadi
        $kemarin = Carbon::yesterday();

        // Ambil absensi kemarin yang belum keluar, shift cross-midnight (jam_keluar < jam_masuk)
        $belumTutup = Absensi::whereDate('tanggal', $kemarin)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->with(['karyawan.shift'])
            ->get()
            ->filter(function ($absensi) {
                $shift = $absensi->karyawan->shift;
                if (!$shift || !$shift->jam_keluar) return false;
                // Shift malam: jam_keluar < jam_masuk (cross-midnight), gunakan strtotime
                return strtotime($shift->jam_keluar) < strtotime($shift->jam_masuk);
            });

        if ($belumTutup->isEmpty()) {
            $this->info("Tidak ada absensi shift malam yang perlu ditutup.");
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$belumTutup->count()} absensi shift malam yang belum keluar:");
        $this->table(
            ['Nama', 'Tanggal', 'Jam Masuk', 'Jadwal Keluar', 'Keterangan'],
            $belumTutup->map(fn($a) => [
                $a->karyawan->nama,
                $a->tanggal->format('d/m/Y'),
                substr($a->jam_masuk, 0, 5),
                substr($a->karyawan->shift->jam_keluar, 0, 5),
                'Akan di-set jam_keluar = jadwal shift',
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn('DRY RUN: tidak ada perubahan.');
            return self::SUCCESS;
        }

        $tutup = 0;
        foreach ($belumTutup as $absensi) {
            $shift       = $absensi->karyawan->shift;
            $jamKeluar   = $shift->jam_keluar; // misal 06:00:00
            // Durasi cross-midnight: dari jam_masuk kemarin ke jam_keluar dini hari
            $detik = strtotime($jamKeluar) - strtotime($absensi->jam_masuk);
            if ($detik < 0) $detik += 86400;
            $durasiMenit = (int) round($detik / 60);

            $absensi->update([
                'jam_keluar'   => $jamKeluar,
                'durasi_menit' => $durasiMenit,
                'keterangan'   => ($absensi->keterangan ? $absensi->keterangan . ' | ' : '') . 'Keluar otomatis oleh sistem (tidak scan)',
            ]);

            // Notif ke karyawan
            if ($absensi->karyawan->user) {
                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'auto_close_shift_malam',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $absensi->karyawan->user->id,
                    'data'            => json_encode([
                        'title' => '🌙 Absensi Shift Malam Ditutup Otomatis',
                        'body'  => "Absensi Anda tanggal {$absensi->tanggal->format('d/m/Y')} telah ditutup sistem pukul " . substr($jamKeluar, 0, 5) . " karena tidak scan keluar. Hubungi HRD jika ada koreksi.",
                        'url'   => '/portal/absensi',
                        'icon'  => 'fas fa-moon',
                        'color' => '#4c1d95',
                        'bg'    => '#f5f3ff',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $tutup++;
        }

        AuditLog::catat('update', "Auto-close shift malam {$kemarin->format('d/m/Y')}: {$tutup} absensi ditutup otomatis");
        $this->info("Selesai: {$tutup} absensi shift malam ditutup otomatis.");
        return self::SUCCESS;
    }
}
