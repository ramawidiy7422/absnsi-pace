<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\Pelatihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class KirimReminderKontrak extends Command
{
    protected $signature = 'hr:reminder-kontrak
                            {--dry-run : Tampilkan saja, tidak kirim notifikasi}';

    protected $description = 'Kirim reminder ke HRD: kontrak karyawan akan habis (H-30 & H-7) dan sertifikat pelatihan expired';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $today = now()->startOfDay();

        // ── 1a. Kontrak akan habis H-7 (urgent) ─────────────────────────────
        $kontrakH7 = Karyawan::with('department')
            ->whereIn('status', ['kontrak', 'magang'])
            ->whereNotNull('tanggal_kontrak_berakhir')
            ->whereDate('tanggal_kontrak_berakhir', $today->copy()->addDays(7)->toDateString())
            ->orderBy('tanggal_kontrak_berakhir')
            ->get();

        // ── 1b. Kontrak akan habis dalam 30 hari ─────────────────────────────
        $kontrakHabis = Karyawan::with('department')
            ->whereIn('status', ['kontrak', 'magang'])
            ->whereNotNull('tanggal_kontrak_berakhir')
            ->whereBetween('tanggal_kontrak_berakhir', [
                $today->toDateString(),
                $today->copy()->addDays(30)->toDateString(),
            ])
            ->orderBy('tanggal_kontrak_berakhir')
            ->get();

        // ── 2. Pelatihan wajib (CPOB/K3/Hygiene) expired atau akan expired ──
        $pelatihanExpired = Pelatihan::with('karyawan.department')
            ->whereIn('jenis', ['cpob', 'k3', 'hygiene'])
            ->where('status', 'lulus')
            ->whereNotNull('tanggal_expired')
            ->where('tanggal_expired', '<=', $today->copy()->addDays(30)->toDateString())
            ->orderBy('tanggal_expired')
            ->get();

        $this->info("=== Reminder HR — " . $today->translatedFormat('d F Y') . " ===");
        $this->info("Kontrak URGENT H-7: {$kontrakH7->count()} karyawan");
        $this->info("Kontrak akan habis (≤30 hari): {$kontrakHabis->count()} karyawan");
        $this->info("Sertifikat expired/akan expired (≤30 hari): {$pelatihanExpired->count()} record");

        if ($dryRun) {
            if ($kontrakH7->isNotEmpty()) {
                $this->warn('--- H-7 URGENT ---');
                $this->table(['NIK','Nama','Departemen','Kontrak Berakhir'], $kontrakH7->map(fn($k) => [
                    $k->nik, $k->nama, $k->department?->name ?? '-',
                    $k->tanggal_kontrak_berakhir->format('d/m/Y'),
                ])->toArray());
            }
            if ($kontrakHabis->isNotEmpty()) {
                $this->table(['NIK','Nama','Departemen','Kontrak Berakhir','Sisa Hari'], $kontrakHabis->map(fn($k) => [
                    $k->nik,
                    $k->nama,
                    $k->department?->name ?? '-',
                    $k->tanggal_kontrak_berakhir->format('d/m/Y'),
                    $today->diffInDays($k->tanggal_kontrak_berakhir, false),
                ])->toArray());
            }
            if ($pelatihanExpired->isNotEmpty()) {
                $this->table(['NIK','Nama','Pelatihan','Expired','Sisa Hari'], $pelatihanExpired->map(fn($p) => [
                    $p->karyawan?->nik ?? '-',
                    $p->karyawan?->nama ?? '-',
                    strtoupper($p->jenis),
                    $p->tanggal_expired->format('d/m/Y'),
                    $today->diffInDays($p->tanggal_expired, false),
                ])->toArray());
            }
            $this->info('[dry-run] Tidak ada notifikasi dikirim.');
            return 0;
        }

        if ($kontrakH7->isEmpty() && $kontrakHabis->isEmpty() && $pelatihanExpired->isEmpty()) {
            $this->info('Tidak ada item yang perlu diingatkan hari ini.');
            return 0;
        }

        // Simpan notifikasi ke database untuk semua user HRD/admin
        $hrUsers = User::whereIn('role', ['hrd', 'superadmin', 'admin'])->get();

        if ($hrUsers->isEmpty()) {
            $this->warn('Tidak ada user HRD/admin ditemukan untuk dikirim notifikasi.');
            return 0;
        }

        $sent = 0;
        foreach ($hrUsers as $hrUser) {
            // H-7 urgent notification (separate, higher priority)
            if ($kontrakH7->isNotEmpty()) {
                $lines = $kontrakH7->map(function ($k) {
                    $tgl  = $k->tanggal_kontrak_berakhir->format('d/m/Y');
                    $dept = $k->department?->name ?? '-';
                    return "• {$k->nama} ({$dept}) — berakhir {$tgl} [7 HARI LAGI]";
                })->join("\n");

                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'reminder_kontrak_urgent',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $hrUser->id,
                    'data'            => json_encode([
                        'title' => "🚨 URGENT: {$kontrakH7->count()} Kontrak Berakhir 7 Hari Lagi!",
                        'body'  => $lines,
                        'url'   => '/admin/karyawan?status=kontrak',
                        'icon'  => 'fas fa-exclamation-triangle',
                        'color' => '#c53030',
                        'bg'    => '#fff5f5',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sent++;
            }

            if ($kontrakHabis->isNotEmpty()) {
                $lines = $kontrakHabis->map(function ($k) use ($today) {
                    $sisa  = $today->diffInDays($k->tanggal_kontrak_berakhir, false);
                    $dept  = $k->department?->name ?? '-';
                    $tgl   = $k->tanggal_kontrak_berakhir->format('d/m/Y');
                    return "• {$k->nama} ({$dept}) — berakhir {$tgl} ({$sisa} hari lagi)";
                })->join("\n");

                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'reminder_kontrak',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $hrUser->id,
                    'data'            => json_encode([
                        'title' => "⚠️ {$kontrakHabis->count()} Kontrak Karyawan Akan Habis",
                        'body'  => $lines,
                        'url'   => '/admin/karyawan?status=kontrak',
                        'icon'  => 'fas fa-file-contract',
                        'color' => '#c05621',
                        'bg'    => '#fffaf0',
                    ]),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $sent++;
            }

            if ($pelatihanExpired->isNotEmpty()) {
                $lines = $pelatihanExpired->map(function ($p) use ($today) {
                    $sisa    = $today->diffInDays($p->tanggal_expired, false);
                    $nama    = $p->karyawan?->nama ?? '-';
                    $jenis   = strtoupper($p->jenis);
                    $expired = $sisa < 0 ? "sudah expired " . abs($sisa) . " hari lalu" : "{$sisa} hari lagi";
                    return "• {$nama} — {$jenis} ({$expired})";
                })->join("\n");

                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'reminder_pelatihan',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $hrUser->id,
                    'data'            => json_encode([
                        'title' => "🎓 {$pelatihanExpired->count()} Sertifikat Pelatihan Expired",
                        'body'  => $lines,
                        'url'   => '/admin/pelatihan?expiring=expired',
                        'icon'  => 'fas fa-graduation-cap',
                        'color' => '#c53030',
                        'bg'    => '#fff5f5',
                    ]),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $sent++;
            }
        }

        $this->info("Notifikasi dikirim ke {$hrUsers->count()} user HR ({$sent} notif total).");
        return 0;
    }
}
