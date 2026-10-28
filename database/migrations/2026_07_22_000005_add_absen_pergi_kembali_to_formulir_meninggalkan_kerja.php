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
        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->timestamp('pergi_at')->nullable()->after('rejected_note');
            $table->string('metode_pergi', 10)->nullable()->after('pergi_at');
            $table->timestamp('kembali_at')->nullable()->after('metode_pergi');
            $table->string('metode_kembali', 10)->nullable()->after('kembali_at');
            $table->integer('durasi_menit')->nullable()->after('metode_kembali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->dropColumn(['pergi_at', 'metode_pergi', 'kembali_at', 'metode_kembali', 'durasi_menit']);
        });
    }
};
