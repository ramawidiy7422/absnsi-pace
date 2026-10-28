<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifLupaScanKeluar extends Command
{
    protected $signature = 'absensi:notif-lupa-keluar
                            {--tanggal= : Tanggal cek (Y-m-d), default hari ini}
                            {--dry-run  : Tampilkan saja, tidak kirim notifikasi}';

    protected $description = 'Kirim notifikasi ke karyawan dan HRD yang lupa scan keluar';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : today();

        $dryRun = $this->option('dry-run');

        $this->info("=== Notif Lupa Scan Keluar ===");
        $this->info("Tanggal : {$tanggal->format('d/m/Y')}");
        $this->info("Mode    : " . ($dryRun ? 'DRY RUN' : 'LIVE'));

        $sekarang   = now()->format('H:i:s');
        $batasMenit = 30;

        $lupaBelumKeluar = Absensi::whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->with(['karyawan.shift', 'karyawan.user'])
            ->get();

        // Filter: hanya shift pagi/siang (bukan cross-midnight) yang sudah > 30 menit lewat jadwal keluar
        $perluNotif = $lupaBelumKeluar->filter(function ($absensi) use ($sekarang, $batasMenit) {
            $karyawan = $absensi->karyawan;

            // Skip karyawan bebas absen — mereka tidak terikat jadwal keluar
            if ($karyawan->isBebasAbsen()) return false;

            $shift = $karyawan->shift;
            if (!$shift || !$shift->jam_keluar) return false;

            // Skip shift malam (jam_keluar < jam_masuk = cross-midnight) — ditangani AutoClose
            // Gunakan strtotime bukan string compare agar lebih robust
            if (strtotime($shift->jam_keluar) < strtotime($shift->jam_masuk)) return false;

            $detikSejak = strtotime($sekarang) - strtotime($shift->jam_keluar);
            if ($detikSejak < 0) return false; // belum lewat jadwal keluar
            $menitSejak = (int) round($detikSejak / 60);

            return $menitSejak > $batasMenit && $menitSejak < 4 * 60;
        });

        if ($perluNotif->isEmpty()) {
            $this->info("Tidak ada karyawan yang perlu diingatkan scan keluar.");
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$perluNotif->count()} karyawan lupa scan keluar:");
        $this->table(
            ['Nama', 'Shift', 'Jam Masuk', 'Jadwal Keluar'],
            $perluNotif->map(fn($a) => [
                $a->karyawan->nama,
                $a->karyawan->shift->nama_shift ?? '-',
                substr($a->jam_masuk, 0, 5),
                substr($a->karyawan->shift->jam_keluar ?? '-', 0, 5),
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn('DRY RUN: tidak ada notifikasi dikirim.');
            return self::SUCCESS;
        }

        $hrUserIds = User::whereIn('role', ['hrd', 'admin', 'superadmin'])->pluck('id');
        $sent = 0;

        foreach ($perluNotif as $absensi) {
            $karyawan     = $absensi->karyawan;
            $jadwalKeluar = substr($karyawan->shift->jam_keluar, 0, 5);
            $jamMasuk     = substr($absensi->jam_masuk, 0, 5);

            if ($karyawan->user) {
                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'lupa_scan_keluar',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $karyawan->user->id,
                    'data'            => json_encode([
                        'title' => '⏰ Jangan Lupa Scan Keluar!',
                        'body'  => "Anda masuk pukul {$jamMasuk}, jadwal keluar {$jadwalKeluar}. Segera scan keluar di kiosk.",
                        'url'   => '/portal/absensi',
                        'icon'  => 'fas fa-sign-out-alt',
                        'color' => '#1e40af',
                        'bg'    => '#eff6ff',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $sent++;
        }

        // Notif ringkasan ke HRD
        $namaList = $perluNotif->map(fn($a) => "• {$a->karyawan->nama} (jadwal keluar " . substr($a->karyawan->shift->jam_keluar, 0, 5) . ")")->join("\n");
        foreach ($hrUserIds as $hrId) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'lupa_scan_keluar_summary',
                'notifiable_type' => User::class,
                'notifiable_id'   => $hrId,
                'data'            => json_encode([
                    'title' => "⚠️ {$perluNotif->count()} Karyawan Belum Scan Keluar",
                    'body'  => "Karyawan berikut belum scan keluar:\n{$namaList}",
                    'url'   => '/admin/absensi',
                    'icon'  => 'fas fa-clock',
                    'color' => '#92400e',
                    'bg'    => '#fffbeb',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("Notifikasi dikirim ke {$sent} karyawan + " . $hrUserIds->count() . " HRD/admin.");
        return self::SUCCESS;
    }
}
