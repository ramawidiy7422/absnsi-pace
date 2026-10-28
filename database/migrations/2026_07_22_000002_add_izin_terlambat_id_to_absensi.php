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
            $table->unsignedBigInteger('izin_terlambat_id')->nullable()->after('izin_id')
                ->comment('ID izin terlambat (via WA) jika keterlambatan ini sudah disetujui HRD');
            $table->foreign('izin_terlambat_id')->references('id')->on('izin_terlambat')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['izin_terlambat_id']);
            $table->dropColumn('izin_terlambat_id');
        });
    }
};
