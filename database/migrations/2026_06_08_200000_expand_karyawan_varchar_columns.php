<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('no_hp', 50)->nullable()->change();
            $table->string('birth_place', 100)->nullable()->change();
            $table->string('pendidikan_terakhir', 50)->nullable()->change();
            $table->string('religion', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('no_hp', 20)->nullable()->change();
            $table->string('birth_place', 45)->nullable()->change();
            $table->string('pendidikan_terakhir', 30)->nullable()->change();
            $table->string('religion', 20)->nullable()->change();
        });
    }
};
