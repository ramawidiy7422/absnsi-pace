<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employeshifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('date');
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();

            $table->unique(['karyawan_id', 'date']); // 1 karyawan 1 shift per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employeshifts');
    }
};
