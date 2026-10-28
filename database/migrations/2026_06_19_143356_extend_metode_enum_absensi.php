<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend enum: tambah 'face' dan 'sistem' ke metode absensi
        DB::statement("ALTER TABLE absensi MODIFY COLUMN metode ENUM('wajah','qrcode','manual','face','sistem') NOT NULL DEFAULT 'wajah'");
    }

    public function down(): void
    {
        // Revert — pastikan tidak ada data face/sistem dulu sebelum rollback
        DB::statement("UPDATE absensi SET metode='manual' WHERE metode IN ('face','sistem')");
        DB::statement("ALTER TABLE absensi MODIFY COLUMN metode ENUM('wajah','qrcode','manual') NOT NULL DEFAULT 'wajah'");
    }
};
