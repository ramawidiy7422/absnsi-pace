<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Department
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Divisi
        Schema::create('devisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamps();
        });

        // Posisi / Jabatan
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('level', 8)->nullable();
            $table->string('salary_grade', 45)->nullable();
            $table->timestamps();
        });

        // Tambah kolom FK ke karyawan (department, devision, position)
        Schema::table('karyawan', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('jabatan')->constrained('departments')->nullOnDelete();
            $table->foreignId('devision_id')->nullable()->after('department_id')->constrained('devisions')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('devision_id')->constrained('positions')->nullOnDelete();
            $table->date('birth_date')->nullable()->after('position_id');
            $table->string('birth_place', 45)->nullable()->after('birth_date');
            $table->string('religion', 20)->nullable()->after('birth_place');
            $table->string('material_status', 20)->nullable()->after('religion'); // status pernikahan
            $table->date('permanent_date')->nullable()->after('material_status');
            $table->date('probation_start')->nullable()->after('permanent_date');
        });

        // Pendidikan
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('level', 15)->nullable();     // SD, SMP, SMA, S1, S2
            $table->string('major', 25)->nullable();     // Jurusan
            $table->string('institution', 35)->nullable(); // Nama sekolah/kampus
            $table->year('year')->nullable();            // Tahun lulus
            $table->timestamps();
        });

        // Dokumen Legal
        Schema::create('legaldocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('nik', 20)->nullable();
            $table->string('kk', 20)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('bpjs_tk', 25)->nullable();
            $table->string('bpjs_kesehatan', 25)->nullable();
            $table->timestamps();
        });

        // Riwayat Kerja
        Schema::create('workhistories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('type', 20)->nullable(); // kontrak, tetap, magang
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // Surat Peringatan
        Schema::create('warningletters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('type', 15);              // SP1, SP2, SP3
            $table->date('date');
            $table->text('description')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 15)->default('aktif'); // aktif, selesai
            $table->timestamps();
        });

        // Pengajuan Cuti
        Schema::create('leaverequests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type', 15);              // cuti, izin, sakit
            $table->string('status', 15)->default('pending'); // pending, approved, rejected
            $table->text('keterangan')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('devision_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropColumn(['birth_date','birth_place','religion','material_status','permanent_date','probation_start']);
        });
        Schema::dropIfExists('leaverequests');
        Schema::dropIfExists('warningletters');
        Schema::dropIfExists('workhistories');
        Schema::dropIfExists('legaldocs');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('devisions');
        Schema::dropIfExists('departments');
    }
};
