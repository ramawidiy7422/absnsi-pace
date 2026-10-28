<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('workhistories', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable()->after('type');
            $table->string('departemen', 100)->nullable()->after('jabatan');
            $table->string('status_karyawan', 20)->nullable()->after('departemen');
        });
    }
    public function down(): void {
        Schema::table('workhistories', function (Blueprint $table) {
            $table->dropColumn(['jabatan','departemen','status_karyawan']);
        });
    }
};
