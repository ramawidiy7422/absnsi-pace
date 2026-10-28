<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeteksiAlpha3Berturut extends Command
{
    protected $signature = 'absensi:deteksi-alpha-berturut
                            {--tanggal= : Tanggal akhir pengecekan (Y-m-d), default kemarin}
                            {--hari=3   : Jumlah hari alpha berturut-turut yang dicek}
                            {--dry-run  : Tampilkan saja, tidak kirim notifikasi}';

    protected $description = 'Deteksi karyawan yang alpha N hari berturut-turut dan kirim notifikasi ke HRD';

    public function handle(): int
    {
        $tanggalAkhir = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : Carbon::yesterday();

        $jumlahHari = (int) $this->option('hari');
        $dryRun     = $this->option('dry-run');

        $this->info("=== Deteksi Alpha {$jumlahHari} Hari Berturut-turut ===");
        $this->info("Tanggal akhir : {$tanggalAkhir->format('d/m/Y')}");
        $this->info("Mode          : " . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->line('');

        // Bangun rentang tanggal (N hari ke belakang)
        $tanggalMulai = $tanggalAkhir->copy()->subDays($jumlahHari - 1);

        $this->info("Cek periode   : {$tanggalMulai->format('d/m/Y')} s/d {$tanggalAkhir->format('d/m/Y')}");

        // Ambil semua record absensi alpha dalam periode
        $alphaRecords = Absensi::whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->where('status', 'alpha')
            ->with('karyawan.department')
            ->get()
            ->groupBy('karyawan_id');

        // Filter: hanya karyawan yang alpha SEMUA hari dalam periode
        $karyawanAlphaBerturut = collect();

        foreach ($alphaRecords as $karyawanId => $records) {
            // Harus tepat $jumlahHari hari alpha
            if ($records->count() < $jumlahHari) continue;

            // Verifikasi semua tanggal dalam range berurutan
            $tanggalAlpha = $records->pluck('tanggal')->map(fn($t) => $t->toDateString())->sort()->values();

            $allDates = collect();
            for ($i = 0; $i < $jumlahHari; $i++) {
                $allDates->push($tanggalMulai->copy()->addDays($i)->toDateString());
            }

            // Semua tanggal dalam range harus ada di alpha records
            $allPresent = $allDates->every(fn($d) => $tanggalAlpha->contains($d));

            if ($allPresent) {
                $karyawan = $records->first()->karyawan;
                if ($karyawan
                    && in_array($karyawan->status, ['kontrak', 'tetap', 'magang', 'training'])
                    && !$karyawan->isBebasAbsen()
                ) {
                    $karyawanAlphaBerturut->push($karyawan);
                }
            }
        }

        if ($karyawanAlphaBerturut->isEmpty()) {
            $this->info("Tidak ada karyawan yang alpha {$jumlahHari} hari berturut-turut.");
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$karyawanAlphaBerturut->count()} karyawan alpha {$jumlahHari} hari berturut-turut:");
        $this->table(
            ['ID', 'Nama', 'Departemen', 'Status'],
            $karyawanAlphaBerturut->map(fn($k) => [
                $k->id, $k->nama, $k->department?->name ?? '-', $k->status,
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn('DRY RUN: tidak ada notifikasi dikirim.');
            return self::SUCCESS;
        }

        // Kirim notifikasi ke HRD/admin
        $hrUsers = User::whereIn('role', ['hrd', 'superadmin', 'admin'])->get();

        if ($hrUsers->isEmpty()) {
            $this->warn('Tidak ada user HRD/admin untuk dikirim notifikasi.');
            return self::SUCCESS;
        }

        $lines = $karyawanAlphaBerturut->map(function ($k) use ($tanggalMulai, $tanggalAkhir) {
            $dept = $k->department?->name ?? '-';
            return "• {$k->nama} ({$dept}) — alpha {$tanggalMulai->format('d/m')} s/d {$tanggalAkhir->format('d/m/Y')}";
        })->join("\n");

        $sent = 0;
        foreach ($hrUsers as $hrUser) {
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'alpha_berturut',
                'notifiable_type' => User::class,
                'notifiable_id'   => $hrUser->id,
                'data'            => json_encode([
                    'title' => "🔴 {$karyawanAlphaBerturut->count()} Karyawan Alpha {$jumlahHari} Hari Berturut-turut",
                    'body'  => "Periode {$tanggalMulai->format('d/m')} – {$tanggalAkhir->format('d/m/Y')}:\n{$lines}",
                    'url'   => '/admin/absensi',
                    'icon'  => 'fas fa-user-times',
                    'color' => '#c53030',
                    'bg'    => '#fff5f5',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sent++;
        }

        $this->info("Notifikasi dikirim ke {$sent} user HRD/admin.");
        return self::SUCCESS;
    }
}
