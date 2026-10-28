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
        Schema::create('spk_lembur', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spk', 30)->unique();

            // Target: satu karyawan atau seluruh departemen
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('pekerjaan', 500);           // deskripsi pekerjaan yang harus dilakukan
            $table->string('lokasi', 100)->nullable();  // lokasi lembur jika berbeda
            $table->decimal('estimasi_jam', 4, 2)->nullable();

            $table->enum('status', ['draft','aktif','selesai','dibatalkan'])->default('draft');

            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->string('catatan', 300)->nullable();

            $table->timestamps();

            $table->index(['tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_lembur');
    }
};
