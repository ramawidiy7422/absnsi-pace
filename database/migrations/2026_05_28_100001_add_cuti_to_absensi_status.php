<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum status absensi → tambah 'cuti'
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','terlambat','izin','sakit','alpha','cuti') NOT NULL DEFAULT 'hadir'");

        // Tambah kolom flag untuk membedakan absensi auto-generated dari leave approval
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('leaverequest_id')->nullable()->after('keterangan')->comment('ID leaverequest jika absensi ini dibuat otomatis saat approve cuti');
            $table->foreign('leaverequest_id')->references('id')->on('leaverequests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['leaverequest_id']);
            $table->dropColumn('leaverequest_id');
        });
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','terlambat','izin','sakit','alpha') NOT NULL DEFAULT 'hadir'");
    }
};
