<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Pindah NIK KTP (16 digit angka) dari karyawan.nik ke legaldocs.nik
        $karyawanNikKtp = DB::table('karyawan')
            ->whereRaw("LENGTH(nik) = 16 AND nik REGEXP '^[0-9]+$'")
            ->get(['id', 'nik']);

        foreach ($karyawanNikKtp as $k) {
            $existing = DB::table('legaldocs')->where('karyawan_id', $k->id)->first();
            if ($existing) {
                if (empty($existing->nik)) {
                    DB::table('legaldocs')->where('karyawan_id', $k->id)->update(['nik' => $k->nik]);
                }
            } else {
                DB::table('legaldocs')->insert([
                    'karyawan_id' => $k->id,
                    'nik'         => $k->nik,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Step 2: Generate ID Karyawan format YYYYMMNN dari tanggal_masuk
        $counter      = [];
        $semuaKaryawan = DB::table('karyawan')
            ->orderBy('tanggal_masuk')
            ->orderBy('id')
            ->get(['id', 'tanggal_masuk']);

        foreach ($semuaKaryawan as $k) {
            if ($k->tanggal_masuk) {
                $tgl    = \Carbon\Carbon::parse($k->tanggal_masuk);
                $prefix = $tgl->format('Ym');
                $counter[$prefix] = ($counter[$prefix] ?? 0) + 1;
                $idBaru = $tgl->format('Y') . $tgl->format('m') . str_pad($counter[$prefix], 2, '0', STR_PAD_LEFT);
            } else {
                // Karyawan tanpa tanggal_masuk: id saja dengan prefix 0
                $idBaru = '000000' . str_pad($k->id, 2, '0', STR_PAD_LEFT);
            }
            DB::table('karyawan')->where('id', $k->id)->update(['nik' => $idBaru]);
        }
    }

    public function down(): void
    {
        // Tidak reversible — data lama sudah diganti
    }
};
