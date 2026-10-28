<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Email pribadi karyawan (beda dengan email login akun)
            $table->string('email')->nullable()->after('no_hp');
            // Pendidikan terakhir ringkas (untuk profil cepat, detail di tabel educations)
            $table->string('pendidikan_terakhir', 30)->nullable()->after('email');
        });

        // Tabel lembur (overtime)
        Schema::create('lemburan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('total_jam', 4, 2)->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->string('alasan')->nullable();
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemburan');
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['email', 'pendidikan_terakhir']);
        });
    }
};
