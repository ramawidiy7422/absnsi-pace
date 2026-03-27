<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedSmallInteger('durasi_menit')->nullable()->after('jam_keluar');
            $table->boolean('pulang_cepat')->default(false)->after('durasi_menit');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['durasi_menit', 'pulang_cepat']);
        });
    }
};
