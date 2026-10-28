<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpkLembur extends Model
{
    protected $table = 'spk_lembur';

    protected $fillable = [
        'nomor_spk', 'karyawan_id', 'department_id',
        'tanggal', 'jam_mulai', 'jam_selesai', 'pekerjaan',
        'lokasi', 'estimasi_jam', 'status',
        'dibuat_oleh', 'disetujui_oleh', 'disetujui_at', 'catatan',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'disetujui_at' => 'datetime',
    ];

    public function karyawan()   { return $this->belongsTo(Karyawan::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function pembuat()    { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function penyetuju()  { return $this->belongsTo(User::class, 'disetujui_oleh'); }
    public function lemburan()   { return $this->hasMany(Lemburan::class, 'spk_lembur_id'); }

    public static function generateNomor(): string
    {
        $prefix = 'SPK-LMB/' . now()->format('Ym');
        $last   = static::where('nomor_spk', 'like', "{$prefix}/%")
                        ->orderByDesc('nomor_spk')
                        ->value('nomor_spk');

        $urut = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . '/' . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat SPK Lembur baru dengan nomor_spk yang dijamin unik walau ada submit bersamaan.
     * Pakai MySQL named lock (mutex sesi) supaya antrian antar request rapi tanpa
     * risiko deadlock seperti lockForUpdate() pada tabel besar.
     */
    public static function createWithNomor(array $data): self
    {
        $lockName = 'spk_lembur_nomor_generator';
        DB::select('SELECT GET_LOCK(?, 10)', [$lockName]);
        try {
            $data['nomor_spk'] = self::generateNomor();
            return self::create($data);
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft'      => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'Draft'],
            'aktif'      => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Aktif'],
            'selesai'    => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Selesai'],
            'dibatalkan' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Dibatalkan'],
            default      => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => ucfirst($this->status)],
        };
    }

    public function getTargetLabelAttribute(): string
    {
        if ($this->karyawan_id) {
            return $this->karyawan?->nama ?? '-';
        }
        return 'Dept. ' . ($this->department?->name ?? '-');
    }

    // Cek apakah karyawan tertentu punya SPK aktif hari ini
    public static function spkAktifUntuk(int $karyawanId): ?self
    {
        $karyawan = Karyawan::find($karyawanId);

        return static::where('status', 'aktif')
            ->whereDate('tanggal', today())
            ->where(function ($q) use ($karyawanId, $karyawan) {
                $q->where('karyawan_id', $karyawanId);
                if ($karyawan?->department_id) {
                    $q->orWhere('department_id', $karyawan->department_id);
                }
            })
            ->first();
    }
}
