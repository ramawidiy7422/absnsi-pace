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
            $table->unsignedBigInteger('izin_id')->nullable()->after('leaverequest_id')
                ->comment('ID izin jika absensi ini dibuat otomatis saat approve izin harian');
            $table->foreign('izin_id')->references('id')->on('izin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['izin_id']);
            $table->dropColumn('izin_id');
        });
    }
};
