<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NotifikasiPelatihanExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'pelatihan:notif-expired';
    protected $description = 'Kirim notifikasi ke admin/HRD untuk pelatihan yang sudah atau mendekati expired';

    public function handle(): void
    {
        $pelatihans = \App\Models\Pelatihan::with('karyawan')
            ->where('status', 'lulus')
            ->whereNotNull('tanggal_expired')
            ->where('tanggal_expired', '<=', now()->addDays(30))
            ->get();

        if ($pelatihans->isEmpty()) {
            $this->info('Tidak ada pelatihan mendekati/sudah expired.');
            return;
        }

        $admins = \App\Models\User::whereIn('role', ['superadmin', 'admin', 'hrd'])->get();
        $count  = 0;

        foreach ($pelatihans as $p) {
            $sisaHari = (int) now()->startOfDay()->diffInDays($p->tanggal_expired->startOfDay(), false);

            // Kirim notifikasi di H-30, H-7, H-1, H0 (hari expired), dan sudah lewat (H-1)
            if (!in_array($sisaHari, [30, 7, 1, 0, -1])) continue;

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\PelatihanExpiredNotification($p, $sisaHari));
            }
            $count++;
            $label = $sisaHari <= 0 ? 'EXPIRED' : "H-{$sisaHari}";
            $this->line("  [{$label}] {$p->nama_pelatihan} — {$p->karyawan?->nama}");
        }

        $this->info("Notifikasi dikirim untuk {$count} pelatihan.");
    }
}
