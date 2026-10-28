<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulasiAbsensi extends Command
{
    protected $signature = 'absensi:simulasi
                            {--nik=         : NIK karyawan (opsional, default karyawan pertama)}
                            {--tanggal=     : Tanggal absensi Y-m-d (default hari ini)}
                            {--jam-masuk=   : Jam masuk H:i (contoh: 07:05 = hadir, 08:00 = terlambat)}
                            {--jam-keluar=  : Jam keluar H:i (contoh: 15:30)}
                            {--status=      : Override status (hadir/terlambat/alpha/izin/sakit/cuti)}
                            {--hapus        : Hapus record absensi hari ini untuk karyawan ini (reset)}
                            {--list         : Tampilkan absensi hari ini saja}';

    protected $description = '[TESTING] Simulasi absensi masuk/keluar dengan jam yang bisa diatur manual';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : today();

        if ($this->option('list')) {
            return $this->tampilkanList($tanggal);
        }

        // Cari karyawan
        if ($this->option('nik')) {
            $karyawan = Karyawan::where('nik', $this->option('nik'))->first();
            if (!$karyawan) {
                $this->error("NIK '{$this->option('nik')}' tidak ditemukan.");
                return self::FAILURE;
            }
        } else {
            $karyawan = Karyawan::with('shift')
                ->whereIn('status', ['kontrak','tetap','magang','training'])
                ->whereNotNull('shift_id')
                ->first();
        }

        if (!$karyawan) {
            $this->error('Tidak ada karyawan aktif yang ditemukan.');
            return self::FAILURE;
        }

        $this->info("Karyawan  : {$karyawan->nama} (NIK: {$karyawan->nik})");
        $this->info("Tanggal   : {$tanggal->format('d/m/Y')}");
        $shift = $karyawan->shift;
        if ($shift) {
            $this->info("Shift     : {$shift->nama_shift} ({$shift->jam_masuk} – {$shift->jam_keluar}) | Toleransi: {$shift->toleransi_menit} menit");
        }
        $this->line('');

        // Mode hapus
        if ($this->option('hapus')) {
            $deleted = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', $tanggal)
                ->delete();
            $this->warn("Record absensi {$tanggal->format('d/m/Y')} dihapus ({$deleted} record). Siap untuk simulasi ulang.");
            return self::SUCCESS;
        }

        $record = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Simulasi MASUK
        if ($this->option('jam-masuk') && (!$record || !$record->jam_masuk)) {
            $jamMasuk = $this->option('jam-masuk') . ':00';

            $status = 'hadir';
            if ($this->option('status')) {
                $status = $this->option('status');
            } elseif ($shift) {
                $batasTerlambat = date('H:i:s', strtotime($shift->jam_masuk) + ($shift->toleransi_menit * 60));
                if ($jamMasuk > $batasTerlambat) $status = 'terlambat';
            }

            if ($record) {
                $record->update(['jam_masuk' => $jamMasuk, 'status' => $status]);
            } else {
                Absensi::create([
                    'karyawan_id' => $karyawan->id,
                    'shift_id'    => $karyawan->shift_id,
                    'tanggal'     => $tanggal->toDateString(),
                    'jam_masuk'   => $jamMasuk,
                    'status'      => $status,
                    'metode'      => 'manual',
                    'keterangan'  => '[SIMULASI] Testing',
                ]);
            }

            $icon   = $status === 'terlambat' ? '🔴' : '🟢';
            $this->info("{$icon} Absen MASUK: {$jamMasuk} — Status: " . strtoupper($status));
        }

        // Simulasi KELUAR
        if ($this->option('jam-keluar')) {
            $record = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', $tanggal)->first();

            if (!$record || !$record->jam_masuk) {
                $this->error('Belum ada absen masuk. Tambahkan --jam-masuk dulu.');
                return self::FAILURE;
            }

            $jamKeluar = $this->option('jam-keluar') . ':00';

            // Hitung durasi (handle shift malam)
            $detik = strtotime($jamKeluar) - strtotime($record->jam_masuk);
            if ($detik < 0) $detik += 86400;
            $durasiMenit = (int) round($detik / 60);

            $pulangCepat = false;
            if ($shift && $shift->jam_keluar) {
                $pulangCepat = $jamKeluar < $shift->jam_keluar;
            }

            $record->update([
                'jam_keluar'   => $jamKeluar,
                'durasi_menit' => $durasiMenit,
                'pulang_cepat' => $pulangCepat,
            ]);

            $durasiFormat = intdiv($durasiMenit, 60) . ' jam ' . ($durasiMenit % 60) . ' menit';
            $icon = $pulangCepat ? '🟡' : '🟢';
            $this->info("{$icon} Absen KELUAR: {$jamKeluar} — Durasi: {$durasiFormat}" . ($pulangCepat ? ' (pulang lebih awal)' : ''));
        }

        // Tampilkan state akhir
        $record = Absensi::where('karyawan_id', $karyawan->id)->whereDate('tanggal', $tanggal)->first();
        if ($record) {
            $this->line('');
            $this->info('=== State Akhir ===');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Tanggal',    $record->tanggal->format('d/m/Y')],
                    ['Jam Masuk',  $record->jam_masuk ?? '-'],
                    ['Jam Keluar', $record->jam_keluar ?? '-'],
                    ['Durasi',     $record->durasi_menit ? intdiv($record->durasi_menit,60).'j '.($record->durasi_menit%60).'m' : '-'],
                    ['Status',     strtoupper($record->status)],
                    ['Metode',     $record->metode],
                ]
            );
        }

        return self::SUCCESS;
    }

    private function tampilkanList(Carbon $tanggal): int
    {
        $list = Absensi::with(['karyawan.shift', 'karyawan.department'])
            ->whereDate('tanggal', $tanggal)
            ->get();

        $this->info("=== Absensi {$tanggal->format('d/m/Y')} ({$list->count()} record) ===");

        if ($list->isEmpty()) {
            $this->warn('Belum ada absensi.');
            return self::SUCCESS;
        }

        $this->table(
            ['NIK', 'Nama', 'Dept', 'Masuk', 'Keluar', 'Durasi', 'Status', 'Metode'],
            $list->map(fn($a) => [
                $a->karyawan->nik,
                $a->karyawan->nama,
                $a->karyawan->department?->name ?? '-',
                $a->jam_masuk ? substr($a->jam_masuk,0,5) : '-',
                $a->jam_keluar ? substr($a->jam_keluar,0,5) : '-',
                $a->durasi_menit ? intdiv($a->durasi_menit,60).'j '.($a->durasi_menit%60).'m' : '-',
                strtoupper($a->status),
                $a->metode,
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
