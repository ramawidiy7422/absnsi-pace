<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('label', 150);
            $table->string('group', 50)->default('umum');
            $table->timestamps();
        });

        // Seed default settings
        DB::table('pengaturan')->insert([
            ['key'=>'jam_kerja_standar',    'value'=>'8',    'label'=>'Jam Kerja Standar (per hari)', 'group'=>'absensi', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'toleransi_terlambat',  'value'=>'15',   'label'=>'Toleransi Keterlambatan (menit)', 'group'=>'absensi', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'jatah_cuti_tahunan',   'value'=>'12',   'label'=>'Jatah Cuti Tahunan (hari)', 'group'=>'cuti', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'tarif_lembur_per_jam', 'value'=>'0',    'label'=>'Tarif Lembur per Jam (Rp)', 'group'=>'lembur', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'nama_perusahaan',      'value'=>'PT Global Multi Pharmalab', 'label'=>'Nama Perusahaan', 'group'=>'umum', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'alamat_perusahaan',    'value'=>'',     'label'=>'Alamat Perusahaan', 'group'=>'umum', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'email_notif_aktif',    'value'=>'0',    'label'=>'Aktifkan Notifikasi Email', 'group'=>'notifikasi', 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'email_hr',             'value'=>'',     'label'=>'Email HRD (penerima notif)', 'group'=>'notifikasi', 'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('pengaturan');
    }
};
