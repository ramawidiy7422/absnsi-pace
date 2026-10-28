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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('spv_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });

        // Backfill: pindahkan isi department_spv (kalau ada >1 SPV per
        // departemen, ambil yang paling pertama ditambahkan) ke kolom tunggal.
        DB::table('department_spv')
            ->orderBy('id')
            ->get()
            ->groupBy('department_id')
            ->each(function ($rows, $deptId) {
                DB::table('departments')->where('id', $deptId)->update([
                    'spv_user_id' => $rows->first()->user_id,
                ]);
            });

        Schema::dropIfExists('department_spv');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('department_spv', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
        });

        DB::table('departments')->whereNotNull('spv_user_id')->get(['id', 'spv_user_id'])->each(function ($dept) {
            DB::table('department_spv')->insert([
                'department_id' => $dept->id,
                'user_id'       => $dept->spv_user_id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['spv_user_id']);
            $table->dropColumn('spv_user_id');
        });
    }
};
