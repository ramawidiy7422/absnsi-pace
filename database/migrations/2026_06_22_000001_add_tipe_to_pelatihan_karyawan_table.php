<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelatihan_karyawan', function (Blueprint $table) {
            $table->enum('tipe', ['internal', 'eksternal'])->default('internal')->after('jenis');
        });

        // Set tipe otomatis untuk data lama berdasarkan jenis
        // 'external' → eksternal, sisanya → internal
        DB::statement("UPDATE pelatihan_karyawan SET tipe = 'eksternal' WHERE jenis = 'external'");
    }

    public function down(): void
    {
        Schema::table('pelatihan_karyawan', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
