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
        Schema::create('koreksi_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('absensi_id')->nullable()->constrained('absensi')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar', 'keduanya'])->default('masuk');
            $table->time('jam_masuk_diajukan')->nullable();
            $table->time('jam_keluar_diajukan')->nullable();
            $table->string('alasan', 500);
            $table->string('lampiran', 255)->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('catatan_approver', 500)->nullable();
            $table->timestamps();

            $table->index(['karyawan_id', 'tanggal']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koreksi_absensi');
    }
};
