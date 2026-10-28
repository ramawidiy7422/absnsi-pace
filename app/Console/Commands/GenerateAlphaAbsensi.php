<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\HariLibur;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Leaverequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAlphaAbsensi extends Command
{
    protected $signature = 'absensi:generate-alpha
                            {--tanggal= : Tanggal target (Y-m-d), default kemarin}
                            {--dry-run  : Hanya tampilkan, tidak simpan ke DB}';

    protected $description = 'Generate record absensi alpha untuk karyawan yang tidak hadir, tidak punya izin/cuti, dan bukan hari libur';

    public function handle(): int
    {
        $tanggalStr = $this->option('tanggal')
            ?? Carbon::yesterday()->toDateString();

        try {
            $tanggal = Carbon::parse($tanggalStr);
        } catch (\Exception $e) {
            $this->error("Format tanggal tidak valid: {$tanggalStr}");
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        $this->info("=== Generate Alpha Absensi ===");
        $this->info("Tanggal  : {$tanggal->translatedFormat('l, d F Y')}");
        $this->info("Mode     : " . ($dryRun ? 'DRY RUN (tidak disimpan)' : 'LIVE'));
        $this->line('');

        // 1. Cek hari libur nasional
        if (HariLibur::isLibur($tanggal)) {
            $namaLibur = HariLibur::whereDate('tanggal', $tanggal)->value('nama');
            $this->warn("Hari libur: {$namaLibur}. Tidak ada alpha yang di-generate.");
            return self::SUCCESS;
        }

        // 2. Ambil semua karyawan aktif (kecuali bebas absen — manager ke atas tidak di-alpha)
        $karyawanAktif = Karyawan::whereIn('status', ['kontrak', 'tetap', 'magang', 'training'])
            ->get()
            ->filter(fn($k) => !$k->isBebasAbsen());

        $this->info("Karyawan aktif: {$karyawanAktif->count()} orang");

        // 3. Yang sudah ada record absensi hari itu (apapun statusnya)
        $sudahAbsen = Absensi::whereDate('tanggal', $tanggal)
            ->pluck('karyawan_id')
            ->toArray();

        // 4. Yang punya izin/sakit disetujui hari itu
        $punyaIzin = Izin::where('status', 'disetujui')
            ->whereDate('tanggal', $tanggal)
            ->pluck('karyawan_id')
            ->toArray();

        // 5. Yang punya cuti disetujui yang mencakup hari itu
        $punyaCuti = Leaverequest::where('status', 'disetujui')
            ->where('start_date', '<=', $tanggal->toDateString())
            ->where('end_date', '>=', $tanggal->toDateString())
            ->pluck('karyawan_id')
            ->toArray();

        $dikecualikan = array_unique(array_merge($sudahAbsen, $punyaIzin, $punyaCuti));

        // 6. Karyawan yang harus di-generate alpha
        $alphaList = $karyawanAktif->whereNotIn('id', $dikecualikan);

        $this->info("Sudah absen   : " . count($sudahAbsen));
        $this->info("Punya izin    : " . count($punyaIzin));
        $this->info("Punya cuti    : " . count($punyaCuti));
        $this->info("Akan di-alpha : {$alphaList->count()} orang");
        $this->line('');

        if ($alphaList->isEmpty()) {
            $this->info("Tidak ada karyawan yang perlu di-generate alpha.");
            return self::SUCCESS;
        }

        // Tampilkan daftar
        $this->table(
            ['ID', 'Nama', 'Departemen', 'Status'],
            $alphaList->map(fn($k) => [
                $k->id, $k->nama, $k->departemen ?? '-', $k->status
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn("DRY RUN: tidak ada data yang disimpan.");
            return self::SUCCESS;
        }

        // 7. Konfirmasi jika dijalankan manual (bukan dari scheduler)
        if ($this->input->isInteractive()) {
            if (!$this->confirm("Generate {$alphaList->count()} record alpha untuk {$tanggal->format('d/m/Y')}?")) {
                $this->info("Dibatalkan.");
                return self::SUCCESS;
            }
        }

        // 8. Insert alpha
        $berhasil = 0;
        foreach ($alphaList as $k) {
            Absensi::create([
                'karyawan_id' => $k->id,
                'shift_id'    => $k->shift_id,
                'tanggal'     => $tanggal->toDateString(),
                'jam_masuk'   => null,
                'jam_keluar'  => null,
                'status'      => 'alpha',
                'metode'      => 'manual',
                'keterangan'  => 'Auto-generate: tidak hadir tanpa keterangan',
            ]);
            $berhasil++;
        }

        AuditLog::catat(
            'create',
            "Generate alpha otomatis {$tanggal->format('d/m/Y')}: {$berhasil} karyawan"
        );

        $this->info("Selesai: {$berhasil} record alpha berhasil disimpan.");
        return self::SUCCESS;
    }
}
