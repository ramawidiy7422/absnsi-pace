<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ALTER COLUMN enum tidak support langsung, pakai MODIFY
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','hrd','supervisor','karyawan','operator') NOT NULL DEFAULT 'karyawan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','operator') NOT NULL DEFAULT 'operator'");
    }
};
