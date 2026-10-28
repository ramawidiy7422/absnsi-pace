<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KirimDailySummaryAbsensi extends Command
{
    protected $signature = 'absensi:daily-summary
                            {--tanggal= : Tanggal rekap (Y-m-d), default kemarin}
                            {--dry-run  : Tampilkan saja, tidak kirim notifikasi}';

    protected $description = 'Kirim ringkasan rekap kehadiran harian ke semua user HRD/admin';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : Carbon::yesterday();

        $dryRun = $this->option('dry-run');

        $this->info("=== Daily Summary Absensi ===");
        $this->info("Tanggal : {$tanggal->translatedFormat('l, d F Y')}");
        $this->info("Mode    : " . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->line('');

        // Skip hari libur
        if (HariLibur::isLibur($tanggal)) {
            $namaLibur = HariLibur::whereDate('tanggal', $tanggal)->value('nama') ?? 'Hari Libur';
            $this->info("Hari libur ({$namaLibur}), tidak ada summary dikirim.");
            return self::SUCCESS;
        }

        // Hitung total karyawan aktif yang wajib absen (exclude bebas_absen dan jabatan manajer)
        $totalAktif = Karyawan::whereIn('status', ['kontrak', 'tetap', 'magang', 'training'])
            ->where('bebas_absen', false)
            ->whereNotIn('jabatan', Karyawan::JABATAN_BEBAS_ABSEN)
            ->count();

        // Rekap absensi hari itu
        $rekap = Absensi::whereDate('tanggal', $tanggal)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $hadir    = ($rekap->get('hadir', 0) + $rekap->get('terlambat', 0));
        $terlambat = $rekap->get('terlambat', 0);
        $alpha    = $rekap->get('alpha', 0);
        $izin     = $rekap->get('izin', 0) + $rekap->get('sakit', 0);
        $cuti     = $rekap->get('cuti', 0);
        $tidakAbsen = max(0, $totalAktif - $rekap->sum());

        // Top 5 terlambat (cross-midnight aware: MOD(TIMESTAMPDIFF detik, 86400))
        $top5Terlambat = DB::table('absensi')
            ->join('karyawan', 'karyawan.id', '=', 'absensi.karyawan_id')
            ->leftJoin('departments', 'departments.id', '=', 'karyawan.department_id')
            ->leftJoin('shifts', 'shifts.id', '=', 'absensi.shift_id')
            ->whereDate('absensi.tanggal', $tanggal)
            ->where('absensi.status', 'terlambat')
            ->whereNotNull('absensi.jam_masuk')
            ->selectRaw("karyawan.nama, departments.name as dept_name,
                         absensi.jam_masuk, shifts.jam_masuk as shift_jam_masuk,
                         MOD(TIMESTAMPDIFF(SECOND, shifts.jam_masuk, absensi.jam_masuk) + 86400, 86400) / 60
                         as menit_terlambat")
            ->orderByRaw("menit_terlambat DESC")
            ->limit(5)
            ->get();

        // Alpha karyawan (nama)
        $alphaKaryawan = Absensi::whereDate('tanggal', $tanggal)
            ->where('status', 'alpha')
            ->with('karyawan.department')
            ->get();

        $this->info("Total aktif : {$totalAktif}");
        $this->info("Hadir       : {$hadir}");
        $this->info("Terlambat   : {$terlambat}");
        $this->info("Alpha       : {$alpha}");
        $this->info("Izin/Sakit  : {$izin}");
        $this->info("Cuti        : {$cuti}");
        $this->line('');

        if ($dryRun) {
            $this->warn('DRY RUN: tidak ada notifikasi dikirim.');
            return self::SUCCESS;
        }

        $hrUsers = User::whereIn('role', ['hrd', 'superadmin', 'admin'])->get();

        if ($hrUsers->isEmpty()) {
            $this->warn('Tidak ada user HRD/admin untuk dikirim notifikasi.');
            return self::SUCCESS;
        }

        // Susun body notifikasi
        $tglLabel = $tanggal->translatedFormat('l, d F Y');
        $persen   = $totalAktif > 0 ? round(($hadir / $totalAktif) * 100) : 0;

        $bodyLines = [
            "📊 Rekap Kehadiran {$tglLabel}",
            "─────────────────────────────",
            "✅ Hadir      : {$hadir} dari {$totalAktif} karyawan ({$persen}%)",
            "⏰ Terlambat  : {$terlambat} orang",
            "❌ Alpha      : {$alpha} orang",
            "🏥 Izin/Sakit : {$izin} orang",
            "🌴 Cuti       : {$cuti} orang",
        ];

        if ($alphaKaryawan->isNotEmpty()) {
            $bodyLines[] = "";
            $bodyLines[] = "Alpha hari ini:";
            foreach ($alphaKaryawan->take(10) as $a) {
                $dept = $a->karyawan?->department?->name ?? '-';
                $bodyLines[] = "• {$a->karyawan?->nama} ({$dept})";
            }
            if ($alphaKaryawan->count() > 10) {
                $bodyLines[] = "  ...dan " . ($alphaKaryawan->count() - 10) . " lainnya";
            }
        }

        $body = implode("\n", $bodyLines);

        // Tentukan warna/ikon berdasarkan persentase kehadiran
        [$icon, $color, $bg] = match(true) {
            $persen >= 90 => ['fas fa-check-circle', '#276749', '#f0fff4'],
            $persen >= 70 => ['fas fa-info-circle', '#c05621', '#fffaf0'],
            default       => ['fas fa-exclamation-triangle', '#c53030', '#fff5f5'],
        };

        $sent = 0;
        foreach ($hrUsers as $hrUser) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'daily_summary_absensi',
                'notifiable_type' => User::class,
                'notifiable_id'   => $hrUser->id,
                'data'            => json_encode([
                    'title' => "📋 Summary Absensi {$tanggal->format('d/m/Y')} — {$persen}% Hadir",
                    'body'  => $body,
                    'url'   => '/admin/absensi?tanggal=' . $tanggal->toDateString(),
                    'icon'  => $icon,
                    'color' => $color,
                    'bg'    => $bg,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sent++;
        }

        $this->info("Daily summary dikirim ke {$sent} user HRD/admin.");
        return self::SUCCESS;
    }
}
