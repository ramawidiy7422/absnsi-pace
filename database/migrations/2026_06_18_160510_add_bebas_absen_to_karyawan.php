<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Jika true: karyawan bebas absen jam berapa saja, tidak dihitung terlambat,
            // tidak di-generate alpha, jam keluar tidak dibatasi shift
            $table->boolean('bebas_absen')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('bebas_absen');
        });
    }
};
