<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('golongan_darah', 5)->nullable()->after('jenis_kelamin');
            $table->string('kontak_darurat_nama', 100)->nullable()->after('jumlah_tanggungan');
            $table->string('kontak_darurat_hubungan', 50)->nullable()->after('kontak_darurat_nama');
            $table->string('kontak_darurat_no_hp', 20)->nullable()->after('kontak_darurat_hubungan');
            $table->string('bank_nama', 50)->nullable()->after('kontak_darurat_no_hp');
            $table->string('bank_no_rekening', 30)->nullable()->after('bank_nama');
            $table->string('bank_atas_nama', 100)->nullable()->after('bank_no_rekening');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn([
                'golongan_darah',
                'kontak_darurat_nama','kontak_darurat_hubungan','kontak_darurat_no_hp',
                'bank_nama','bank_no_rekening','bank_atas_nama',
            ]);
        });
    }
};
