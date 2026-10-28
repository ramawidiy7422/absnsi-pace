<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE karyawan MODIFY COLUMN status ENUM('kontrak','tetap','magang','nonaktif','training','outsourcing','resign','phk') NOT NULL DEFAULT 'kontrak'");

        if (!Schema::hasColumn('karyawan', 'tanggal_keluar')) {
            Schema::table('karyawan', function ($table) {
                $table->date('tanggal_keluar')->nullable()->after('tanggal_kontrak_berakhir');
                $table->string('alasan_keluar', 100)->nullable()->after('tanggal_keluar');
                $table->string('vendor_outsourcing', 100)->nullable()->after('alasan_keluar');
            });
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE karyawan MODIFY COLUMN status ENUM('kontrak','tetap','magang','nonaktif','training') NOT NULL DEFAULT 'kontrak'");
    }
};
