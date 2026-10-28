<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cuti_saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->year('tahun');
            $table->integer('jatah')->default(12);
            $table->integer('terpakai')->default(0);
            $table->integer('sisa')->storedAs('jatah - terpakai');
            $table->timestamps();
            $table->unique(['karyawan_id','tahun']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cuti_saldo');
    }
};
