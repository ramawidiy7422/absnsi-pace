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
        Schema::table('karyawan', function (Blueprint $table) {
            $table->date('tanggal_masuk')->nullable()->after('status');
            $table->date('tanggal_kontrak_berakhir')->nullable()->after('tanggal_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_masuk','tanggal_kontrak_berakhir']);
        });
    }
};
