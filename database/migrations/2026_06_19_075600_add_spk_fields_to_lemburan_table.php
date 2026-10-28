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
        Schema::table('lemburan', function (Blueprint $table) {
            // spk_lembur_id kolom sudah ada, tambah foreign key-nya
            $table->foreign('spk_lembur_id')->references('id')->on('spk_lembur')->nullOnDelete();

            // Jenis: spk = berdasar SPK, mandiri = klaim sendiri, auto = detected kiosk
            $table->enum('jenis', ['spk', 'mandiri', 'auto'])->default('mandiri')->after('spk_lembur_id');

            // Deskripsi pekerjaan yang dilakukan selama lembur
            $table->string('deskripsi_kerja', 500)->nullable()->after('keterangan');

            // Apakah lembur mendadak (bukan dari SPK)
            $table->boolean('is_mendadak')->default(false)->after('deskripsi_kerja');

            // Alasan tolak dari HRD
            $table->string('alasan_tolak', 300)->nullable()->after('is_mendadak');
        });
    }

    public function down(): void
    {
        Schema::table('lemburan', function (Blueprint $table) {
            $table->dropForeign(['spk_lembur_id']);
            $table->dropColumn(['spk_lembur_id', 'jenis', 'deskripsi_kerja', 'is_mendadak', 'alasan_tolak']);
        });
    }
};
