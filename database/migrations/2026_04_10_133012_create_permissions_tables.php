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
        // Tabel permissions — daftar semua permission yang tersedia
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();       // slug: kelola-karyawan
            $table->string('label', 150);                 // label: Kelola Karyawan
            $table->string('kategori', 80)->default('Umum'); // grouping di UI
            $table->timestamps();
        });

        // Pivot: role → permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->string('role', 50);                   // superadmin, admin, hrd, dst
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
