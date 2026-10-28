<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // izin: filter by tanggal, status, karyawan+tanggal
        Schema::table('izin', function (Blueprint $table) {
            if (!$this->indexExists('izin', 'izin_tanggal_index')) {
                $table->index('tanggal', 'izin_tanggal_index');
            }
            if (!$this->indexExists('izin', 'izin_status_index')) {
                $table->index('status', 'izin_status_index');
            }
            if (!$this->indexExists('izin', 'izin_karyawan_tanggal_index')) {
                $table->index(['karyawan_id', 'tanggal'], 'izin_karyawan_tanggal_index');
            }
        });

        // leaverequests: filter by status, start_date, karyawan+start_date
        Schema::table('leaverequests', function (Blueprint $table) {
            if (!$this->indexExists('leaverequests', 'lr_status_index')) {
                $table->index('status', 'lr_status_index');
            }
            if (!$this->indexExists('leaverequests', 'lr_start_date_index')) {
                $table->index('start_date', 'lr_start_date_index');
            }
            if (!$this->indexExists('leaverequests', 'lr_karyawan_start_index')) {
                $table->index(['karyawan_id', 'start_date'], 'lr_karyawan_start_index');
            }
        });

        // lemburan: filter by tanggal, status, karyawan+tanggal
        Schema::table('lemburan', function (Blueprint $table) {
            if (!$this->indexExists('lemburan', 'lemburan_tanggal_index')) {
                $table->index('tanggal', 'lemburan_tanggal_index');
            }
            if (!$this->indexExists('lemburan', 'lemburan_status_index')) {
                $table->index('status', 'lemburan_status_index');
            }
            if (!$this->indexExists('lemburan', 'lemburan_karyawan_tanggal_index')) {
                $table->index(['karyawan_id', 'tanggal'], 'lemburan_karyawan_tanggal_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('izin', function (Blueprint $table) {
            $table->dropIndexIfExists('izin_tanggal_index');
            $table->dropIndexIfExists('izin_status_index');
            $table->dropIndexIfExists('izin_karyawan_tanggal_index');
        });

        Schema::table('leaverequests', function (Blueprint $table) {
            $table->dropIndexIfExists('lr_status_index');
            $table->dropIndexIfExists('lr_start_date_index');
            $table->dropIndexIfExists('lr_karyawan_start_index');
        });

        Schema::table('lemburan', function (Blueprint $table) {
            $table->dropIndexIfExists('lemburan_tanggal_index');
            $table->dropIndexIfExists('lemburan_status_index');
            $table->dropIndexIfExists('lemburan_karyawan_tanggal_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
