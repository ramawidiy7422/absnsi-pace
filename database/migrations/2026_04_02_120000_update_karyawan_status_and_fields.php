<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah enum status: aktif → kontrak/tetap/magang/nonaktif
        //    Expand dulu ke semua nilai, baru UPDATE, baru restrict
        DB::statement("ALTER TABLE karyawan MODIFY status ENUM('aktif','nonaktif','kontrak','tetap','magang') NOT NULL DEFAULT 'aktif'");
        DB::statement("UPDATE karyawan SET status = 'kontrak' WHERE status = 'aktif'");
        DB::statement("ALTER TABLE karyawan MODIFY status ENUM('kontrak','tetap','magang','nonaktif') NOT NULL DEFAULT 'kontrak'");

        // 2. Hapus probation_start — diganti logika tgl_kontrak_berakhir
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('probation_start');
        });

        // 3. Tambah tgl_kontrak_berakhir (untuk status kontrak/magang)
        Schema::table('karyawan', function (Blueprint $table) {
            $table->date('tgl_kontrak_berakhir')->nullable()->after('permanent_date');
        });
    }

    public function down(): void
    {
        DB::statement("UPDATE karyawan SET status = 'aktif' WHERE status IN ('kontrak','tetap','magang')");
        DB::statement("ALTER TABLE karyawan MODIFY status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'");

        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('tgl_kontrak_berakhir');
            $table->date('probation_start')->nullable()->after('permanent_date');
        });
    }
};
