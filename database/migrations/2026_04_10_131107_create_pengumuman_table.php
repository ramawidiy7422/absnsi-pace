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
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('isi');
            $table->enum('jenis', ['pengumuman', 'memo'])->default('pengumuman');
            $table->enum('prioritas', ['normal', 'penting', 'mendesak'])->default('normal');
            $table->string('target_departemen', 100)->nullable(); // null = semua
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->string('lampiran', 255)->nullable();
            $table->timestamps();
            $table->index('aktif');
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
