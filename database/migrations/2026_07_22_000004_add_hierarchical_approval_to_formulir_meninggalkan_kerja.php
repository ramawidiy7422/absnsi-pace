<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE formulir_meninggalkan_kerja MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending_spv'");

        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->foreignId('spv_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('spv_at')->nullable()->after('spv_by');
            $table->string('spv_note', 255)->nullable()->after('spv_at');

            $table->foreignId('manajer_by')->nullable()->after('spv_note')->constrained('users')->nullOnDelete();
            $table->timestamp('manajer_at')->nullable()->after('manajer_by');
            $table->string('manajer_note', 255)->nullable()->after('manajer_at');

            $table->foreignId('hrd_by')->nullable()->after('manajer_note')->constrained('users')->nullOnDelete();
            $table->timestamp('hrd_at')->nullable()->after('hrd_by');
            $table->string('hrd_note', 255)->nullable()->after('hrd_at');

            $table->string('rejected_stage', 10)->nullable()->after('hrd_note');
            $table->string('rejected_note', 255)->nullable()->after('rejected_stage');
        });

        DB::table('formulir_meninggalkan_kerja')->where('status', 'menunggu')->update(['status' => 'pending_spv']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('formulir_meninggalkan_kerja')
            ->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])
            ->update(['status' => 'menunggu']);

        Schema::table('formulir_meninggalkan_kerja', function (Blueprint $table) {
            $table->dropForeign(['spv_by']);
            $table->dropForeign(['manajer_by']);
            $table->dropForeign(['hrd_by']);
            $table->dropColumn([
                'spv_by', 'spv_at', 'spv_note',
                'manajer_by', 'manajer_at', 'manajer_note',
                'hrd_by', 'hrd_at', 'hrd_note',
                'rejected_stage', 'rejected_note',
            ]);
        });

        DB::statement("ALTER TABLE formulir_meninggalkan_kerja MODIFY status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu'");
    }
};
