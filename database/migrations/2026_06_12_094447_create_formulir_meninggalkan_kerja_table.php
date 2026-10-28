<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_fmk', 50)->unique();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal');
            $table->time('jam_keluar');
            $table->time('jam_kembali')->nullable();
            $table->enum('alasan', ['sakit','urusan_keluarga','urusan_pribadi','keperluan_dinas','lainnya']);
            $table->string('keterangan')->nullable();
            $table->enum('status', ['menunggu','disetujui','ditolak'])->default('menunggu');
            $table->unsignedBigInteger('disetujui_oleh')->nullable();
            $table->timestamp('disetujui_at')->nullable();
            $table->string('catatan_approver')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulir_meninggalkan_kerja');
    }
};
