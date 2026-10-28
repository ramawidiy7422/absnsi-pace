<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pelatihan_karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('nama_pelatihan');
            $table->string('jenis')->default('internal');
            // jenis: cpob | k3 | internal | external | hygiene | sop | lainnya
            $table->string('penyelenggara')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->date('tanggal_expired')->nullable();  // null = tidak ada masa berlaku
            $table->enum('status', ['lulus', 'tidak_lulus', 'sedang_berjalan', 'belum_ikut'])->default('lulus');
            $table->string('nomor_sertifikat')->nullable();
            $table->string('lampiran')->nullable();       // path file sertifikat
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['karyawan_id', 'jenis']);
            $table->index('tanggal_expired');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihan_karyawan');
    }
};
