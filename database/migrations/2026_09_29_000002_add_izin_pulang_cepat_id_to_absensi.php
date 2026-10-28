<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('izin_pulang_cepat_id')->nullable()->after('izin_terlambat_id')
                ->comment('ID izin pulang cepat jika pulang cepat ini sudah disetujui HRD');
            $table->foreign('izin_pulang_cepat_id')->references('id')->on('izin_pulang_cepat')->nullOnDelete();
            $table->string('keterangan_pulang_cepat', 255)->nullable()->after('izin_pulang_cepat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['izin_pulang_cepat_id']);
            $table->dropColumn(['izin_pulang_cepat_id', 'keterangan_pulang_cepat']);
        });
    }
};
