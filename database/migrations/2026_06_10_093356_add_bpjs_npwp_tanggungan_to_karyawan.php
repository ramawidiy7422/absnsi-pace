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
            $table->unsignedTinyInteger('jumlah_tanggungan')->default(0)->after('marital_status');
            $table->string('no_bpjs_kesehatan', 30)->nullable()->after('jumlah_tanggungan');
            $table->string('no_bpjs_ketenagakerjaan', 30)->nullable()->after('no_bpjs_kesehatan');
            $table->string('npwp', 25)->nullable()->after('no_bpjs_ketenagakerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_tanggungan', 'no_bpjs_kesehatan', 'no_bpjs_ketenagakerjaan', 'npwp']);
        });
    }
};
