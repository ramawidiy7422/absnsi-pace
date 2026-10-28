<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormulirMeninggalkanKerja extends Model
{
    protected $table = 'formulir_meninggalkan_kerja';

    const STAGE_SPV     = 'spv';
    const STAGE_MANAJER = 'manajer';
    const STAGE_HRD     = 'hrd';

    protected $fillable = [
        'nomor_fmk','karyawan_id','bagian','tanggal','jam_keluar','jam_kembali',
        'kembali_bekerja','alasan','keterangan','status',
        'disetujui_oleh','disetujui_at','catatan_approver','spv_nama','hrd_nama',
        'spv_by','spv_at','spv_note',
        'manajer_by','manajer_at','manajer_note',
        'hrd_by','hrd_at','hrd_note',
        'rejected_stage','rejected_note',
        'pergi_at','metode_pergi','kembali_at','metode_kembali','durasi_menit',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'disetujui_at' => 'datetime',
        'spv_at'       => 'datetime',
        'manajer_at'   => 'datetime',
        'hrd_at'       => 'datetime',
        'pergi_at'     => 'datetime',
        'kembali_at'   => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function spvApprover()
    {
        return $this->belongsTo(User::class, 'spv_by');
    }

    public function manajerApprover()
    {
        return $this->belongsTo(User::class, 'manajer_by');
    }

    public function hrdApprover()
    {
        return $this->belongsTo(User::class, 'hrd_by');
    }

    /** Tahap yang sedang aktif ('spv'|'manajer'|'hrd'), atau null jika sudah final */
    public function currentStage(): ?string
    {
        return match ($this->status) {
            'pending_spv'     => self::STAGE_SPV,
            'pending_manajer' => self::STAGE_MANAJER,
            'pending_hrd'     => self::STAGE_HRD,
            default           => null,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['disetujui', 'ditolak']);
    }

    /** FMK final disetujui HRD, untuk tanggal hari ini, dan belum absen pergi */
    public function bisaAbsenPergi(): bool
    {
        return $this->status === 'disetujui'
            && $this->tanggal->isToday()
            && $this->pergi_at === null;
    }

    /** Sudah absen pergi, belum absen kembali */
    public function bisaAbsenKembali(): bool
    {
        return $this->status === 'disetujui'
            && $this->pergi_at !== null
            && $this->kembali_at === null;
    }

    public function sudahPergi(): bool
    {
        return $this->pergi_at !== null;
    }

    public function sudahKembali(): bool
    {
        return $this->kembali_at !== null;
    }

    /**
     * Durasi yang mengurangi jam kerja efektif akibat FMK ini.
     * Dinas (keperluan_dinas) => null (jam kerja penuh, tidak dipotong).
     * Selain dinas => selisih menit pergi_at-kembali_at.
     */
    public function hitungDurasiKerja(): ?int
    {
        if ($this->alasan === 'keperluan_dinas') return null;
        if (!$this->pergi_at || !$this->kembali_at) return null;
        return (int) $this->pergi_at->diffInMinutes($this->kembali_at);
    }

    public static function generateNomor(): string
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');
        $count = self::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)->count() + 1;
        return sprintf('FMK/%s/%s/%04d', $bulan, $tahun, $count);
    }

    /**
     * Buat FMK baru dengan nomor_fmk yang dijamin unik walau ada submit bersamaan.
     * Pakai MySQL named lock (mutex sesi, bukan row/gap lock InnoDB) supaya antrian
     * antar request rapi tanpa risiko deadlock seperti lockForUpdate() pada tabel besar.
     */
    public static function createWithNomor(array $data): self
    {
        $lockName = 'fmk_nomor_generator';
        DB::select('SELECT GET_LOCK(?, 10)', [$lockName]);
        try {
            $data['nomor_fmk'] = self::generateNomor();
            return self::create($data);
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function getLabelAlasanAttribute(): string
    {
        return [
            'sakit'           => 'Sakit',
            'urusan_keluarga' => 'Urusan Keluarga',
            'urusan_pribadi'  => 'Urusan Pribadi',
            'keperluan_dinas' => 'Keperluan Dinas',
            'lainnya'         => 'Lainnya',
        ][$this->alasan] ?? $this->alasan;
    }

    public function getLabelStatusAttribute(): string
    {
        return [
            'pending_spv'     => 'Menunggu SPV',
            'pending_manajer' => 'Menunggu Manajer',
            'pending_hrd'     => 'Menunggu HRD',
            'disetujui'       => 'Disetujui',
            'ditolak'         => 'Ditolak',
        ][$this->status] ?? $this->status;
    }
}
