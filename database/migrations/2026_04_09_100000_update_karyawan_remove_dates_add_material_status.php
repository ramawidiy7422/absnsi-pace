<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Hapus kolom yang tidak dipakai
            if (Schema::hasColumn('karyawan', 'tanggal_masuk')) {
                $table->dropColumn('tanggal_masuk');
            }
            if (Schema::hasColumn('karyawan', 'tgl_kontrak_berakhir')) {
                $table->dropColumn('tgl_kontrak_berakhir');
            }
            if (Schema::hasColumn('karyawan', 'permanent_date')) {
                $table->dropColumn('permanent_date');
            }
        });

        Schema::table('karyawan', function (Blueprint $table) {
            // Tambah field material_status baru
            if (!Schema::hasColumn('karyawan', 'material_status')) {
                $table->string('material_status', 15)->nullable()->after('religion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('material_status');
            $table->date('tanggal_masuk')->nullable();
            $table->date('tgl_kontrak_berakhir')->nullable();
            $table->date('permanent_date')->nullable();
        });
    }
};
