<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuratDinas extends Model
{
    protected $table = 'surat_dinas';

    protected $fillable = [
        'nomor_spd','tanggal_dibuat','department_id','keperluan',
        'kota_tujuan','lokasi_tujuan','tanggal_berangkat','tanggal_kembali',
        'jam_berangkat','jenis_kendaraan','nomor_kendaraan',
        'pemberi_tugas_id','pemberi_tugas_nama','pemberi_tugas_jabatan',
        'hrd_id','hrd_nama','status','catatan',
    ];

    protected $casts = [
        'tanggal_dibuat'    => 'date',
        'tanggal_berangkat' => 'date',
        'tanggal_kembali'   => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function pemberiTugas()
    {
        return $this->belongsTo(Karyawan::class, 'pemberi_tugas_id');
    }

    public function hrd()
    {
        return $this->belongsTo(Karyawan::class, 'hrd_id');
    }

    public function staf()
    {
        return $this->belongsToMany(Karyawan::class, 'surat_dinas_staf', 'surat_dinas_id', 'karyawan_id');
    }

    public static function generateNomor(): string
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');
        $count = self::whereYear('tanggal_dibuat', $tahun)->whereMonth('tanggal_dibuat', $bulan)->count() + 1;
        return sprintf('SPD/%s/%s/%04d', $bulan, $tahun, $count);
    }

    /**
     * Buat Surat Dinas baru dengan nomor_spd yang dijamin unik walau ada submit bersamaan.
     * Pakai MySQL named lock (mutex sesi) supaya antrian antar request rapi tanpa
     * risiko deadlock seperti lockForUpdate() pada tabel besar.
     */
    public static function createWithNomor(array $data): self
    {
        $lockName = 'surat_dinas_nomor_generator';
        DB::select('SELECT GET_LOCK(?, 10)', [$lockName]);
        try {
            $data['nomor_spd'] = self::generateNomor();
            return self::create($data);
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function getLabelKendaraanAttribute(): string
    {
        return [
            'motor'          => 'Motor',
            'mobil_dinas'    => 'Mobil Dinas',
            'mobil_pribadi'  => 'Mobil Pribadi',
            'angkutan_umum'  => 'Angkutan Umum',
            'pesawat'        => 'Pesawat',
            'kereta'         => 'Kereta',
            'lainnya'        => 'Lainnya',
        ][$this->jenis_kendaraan] ?? $this->jenis_kendaraan;
    }

    public function getLabelStatusAttribute(): string
    {
        return [
            'draft'    => 'Draft',
            'disetujui'=> 'Disetujui',
            'ditolak'  => 'Ditolak',
        ][$this->status] ?? $this->status;
    }
}
