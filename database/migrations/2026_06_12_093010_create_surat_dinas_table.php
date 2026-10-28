<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spd', 50)->unique();
            $table->date('tanggal_dibuat');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('keperluan');
            $table->string('kota_tujuan', 100);
            $table->string('lokasi_tujuan');
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->time('jam_berangkat')->nullable();
            $table->enum('jenis_kendaraan', ['motor','mobil_dinas','mobil_pribadi','angkutan_umum','pesawat','kereta','lainnya'])->default('motor');
            $table->string('nomor_kendaraan', 20)->nullable();
            $table->unsignedBigInteger('pemberi_tugas_id')->nullable();
            $table->string('pemberi_tugas_nama', 150)->nullable();
            $table->string('pemberi_tugas_jabatan', 100)->nullable();
            $table->unsignedBigInteger('hrd_id')->nullable();
            $table->string('hrd_nama', 150)->nullable();
            $table->enum('status', ['draft','disetujui','ditolak'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('pemberi_tugas_id')->references('id')->on('karyawan')->nullOnDelete();
            $table->foreign('hrd_id')->references('id')->on('karyawan')->nullOnDelete();
        });

        Schema::create('surat_dinas_staf', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_dinas_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->timestamps();

            $table->foreign('surat_dinas_id')->references('id')->on('surat_dinas')->cascadeOnDelete();
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_dinas_staf');
        Schema::dropIfExists('surat_dinas');
    }
};
