<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'kode_karyawan', 'nik', 'inisial', 'nama', 'departemen', 'jabatan', 'jenis_kelamin',
        'no_hp', 'email', 'pendidikan_terakhir', 'alamat',
        'golongan_darah',
        'foto_wajah', 'status', 'shift_id',
        'department_id', 'devision_id', 'position_id',
        'birth_date', 'birth_place', 'religion', 'marital_status',
        'jumlah_tanggungan',
        'kontak_darurat_nama', 'kontak_darurat_hubungan', 'kontak_darurat_no_hp',
        'bank_nama', 'bank_no_rekening', 'bank_atas_nama',
        'tanggal_masuk', 'tanggal_kontrak_berakhir',
        'tanggal_keluar', 'alasan_keluar', 'vendor_outsourcing',
        'bebas_absen',
    ];

    protected $casts = [
        'birth_date'              => 'date',
        'tanggal_masuk'           => 'date',
        'tanggal_kontrak_berakhir'=> 'date',
        'tanggal_keluar'          => 'date',
        'bebas_absen'             => 'boolean',
    ];

    // Jabatan yang otomatis bebas dari validasi jam absensi (no cutoff, no terlambat, no pulang cepat)
    const JABATAN_BEBAS_ABSEN = [
        'Direktur', 'Plant Manager', 'Manager', 'Asst. Manager',
    ];

    /**
     * Apakah karyawan bebas dari validasi jam absen?
     * True jika kolom bebas_absen=1 ATAU jabatannya level manager ke atas.
     */
    public function isBebasAbsen(): bool
    {
        if ($this->bebas_absen) return true;
        return in_array(trim($this->jabatan), self::JABATAN_BEBAS_ABSEN, true);
    }

    /**
     * Hitung masa kerja dari tanggal_masuk
     */
    public function getMasaKerjaAttribute(): string
    {
        if (!$this->tanggal_masuk) return '-';
        $diff = $this->tanggal_masuk->diff(now());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' bulan';
        if (empty($parts))  $parts[] = $diff->d . ' hari';
        return implode(' ', $parts);
    }

    /**
     * Cek apakah kontrak akan habis dalam N hari
     */
    public function isKontrakHabis(int $hari = 30): bool
    {
        return $this->status === 'kontrak'
            && $this->tanggal_kontrak_berakhir
            && (int) $this->tanggal_kontrak_berakhir->startOfDay()->diffInDays(now()->startOfDay(), false) <= 0
            && (int) $this->tanggal_kontrak_berakhir->startOfDay()->diffInDays(now()->startOfDay(), false) >= -$hari;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function devision()
    {
        return $this->belongsTo(Devision::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Shift efektif pada tanggal tertentu:
     * prioritas jadwal khusus (employeshift), fallback ke shift default.
     */
    public function resolveShift(?\Carbon\Carbon $tanggal = null): ?Shift
    {
        $tanggal = $tanggal ?? today();
        $jadwalKhusus = $this->employeshifts()
            ->with('shift')
            ->whereDate('date', $tanggal)
            ->first();
        return $jadwalKhusus?->shift ?? $this->shift;
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensiHariIni()
    {
        return $this->hasOne(Absensi::class)->whereDate('tanggal', today());
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function legaldoc()
    {
        return $this->hasOne(Legaldoc::class);
    }

    public function workhistories()
    {
        return $this->hasMany(Workhistory::class)->orderByDesc('start_date');
    }

    public function pelatihans()
    {
        return $this->hasMany(\App\Models\Pelatihan::class, 'karyawan_id')->orderByDesc('tanggal_mulai');
    }

    public function warningletters()
    {
        return $this->hasMany(Warninglettter::class)->orderByDesc('date');
    }

    public function leaverequests()
    {
        return $this->hasMany(Leaverequest::class)->orderByDesc('start_date');
    }

    public function employeshifts()
    {
        return $this->hasMany(Employeshift::class)->orderByDesc('date');
    }

    public function lemburan()
    {
        return $this->hasMany(Lemburan::class)->orderByDesc('tanggal');
    }

    public function izin()
    {
        return $this->hasMany(Izin::class)->orderByDesc('tanggal');
    }

    public function izinTerlambat()
    {
        return $this->hasMany(IzinTerlambat::class)->orderByDesc('tanggal');
    }

    public function izinPulangCepat()
    {
        return $this->hasMany(IzinPulangCepat::class)->orderByDesc('tanggal');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
