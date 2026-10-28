<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->string('bagian', 100)->nullable()->after('karyawan_id');
            $table->enum('kembali_bekerja', ['kembali','tidak_kembali'])->default('kembali')->after('jam_kembali');
            // Kolom spv_nama & hrd_nama untuk tanda tangan manual
            $table->string('spv_nama', 150)->nullable()->after('catatan_approver');
            $table->string('hrd_nama', 150)->nullable()->after('spv_nama');
        });
    }

    public function down(): void
    {
        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->dropColumn(['bagian','kembali_bekerja','spv_nama','hrd_nama']);
        });
    }
};
