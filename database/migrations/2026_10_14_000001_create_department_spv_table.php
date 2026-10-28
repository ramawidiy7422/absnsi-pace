<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('department_spv', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
        });

        // Backfill: pindahkan spv_user_id lama (1 SPV per departemen) ke tabel
        // pivot sebagai baris pertama, supaya data existing tidak hilang saat
        // kolom spv_user_id di departments dihapus pada migration berikutnya.
        DB::table('departments')->whereNotNull('spv_user_id')->get(['id', 'spv_user_id'])->each(function ($dept) {
            DB::table('department_spv')->insert([
                'department_id' => $dept->id,
                'user_id'       => $dept->spv_user_id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_spv');
    }
};
