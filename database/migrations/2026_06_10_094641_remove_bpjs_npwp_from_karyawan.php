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
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['no_bpjs_kesehatan', 'no_bpjs_ketenagakerjaan', 'npwp']);
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('no_bpjs_kesehatan', 30)->nullable();
            $table->string('no_bpjs_ketenagakerjaan', 30)->nullable();
            $table->string('npwp', 25)->nullable();
        });
    }
};
