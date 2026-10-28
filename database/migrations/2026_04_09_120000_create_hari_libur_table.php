<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hari_libur', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('nama', 100);
            $table->enum('jenis', ['nasional','cuti_bersama','internal'])->default('nasional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('hari_libur');
    }
};
