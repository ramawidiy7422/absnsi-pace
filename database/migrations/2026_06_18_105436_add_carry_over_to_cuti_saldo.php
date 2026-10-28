<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuti_saldo', function (Blueprint $table) {
            // Sisa cuti tahun sebelumnya yang dibawa ke tahun ini
            $table->integer('carry_over')->default(0)->after('terpakai');
            // Batas maksimum carry-over (0 = tidak dibatasi, ikut pengaturan)
            $table->integer('max_carry_over')->default(0)->after('carry_over');
        });
    }

    public function down(): void
    {
        Schema::table('cuti_saldo', function (Blueprint $table) {
            $table->dropColumn(['carry_over', 'max_carry_over']);
        });
    }
};
