<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE karyawan MODIFY status ENUM('kontrak','tetap','magang','nonaktif','training') NOT NULL DEFAULT 'kontrak'");
    }

    public function down(): void
    {
        DB::statement("UPDATE karyawan SET status = 'kontrak' WHERE status = 'training'");
        DB::statement("ALTER TABLE karyawan MODIFY status ENUM('kontrak','tetap','magang','nonaktif') NOT NULL DEFAULT 'kontrak'");
    }
};
