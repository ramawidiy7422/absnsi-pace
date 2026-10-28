<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CutiSaldo extends Model
{
    protected $table = 'cuti_saldo';
    protected $fillable = ['karyawan_id','tahun','jatah','terpakai','carry_over','max_carry_over'];

    public function karyawan() { return $this->belongsTo(Karyawan::class); }

    /**
     * Hitung jatah cuti proporsional berdasarkan ulang tahun kerja:
     *
     * - Ulang tahun kerja (genap 1 tahun) belum tiba di tahun ini → 0 hari
     * - Ulang tahun kerja sudah lewat sebelum 1 Jan tahun ini → jatah penuh
     * - Ulang tahun kerja jatuh di tengah tahun ini → proporsional:
     *   sisa_bulan_di_tahun_ini / 12 × jatah_penuh (dibulatkan ke atas)
     *
     * Contoh (jatah penuh = 12):
     *   Masuk Jan 2024 → ulang tahun Jan 2025 → full 12 hari
     *   Masuk Jul 2024 → ulang tahun Jul 2025 → sisa 6 bln → 6 hari
     *   Masuk Nov 2024 → ulang tahun Nov 2025 → sisa 2 bln → 2 hari
     */
    public static function hitungJatah(int $karyawanId, int $tahun): int
    {
        $karyawan = Karyawan::find($karyawanId);
        if (!$karyawan || !$karyawan->tanggal_masuk) {
            return (int) (Pengaturan::get('jatah_cuti_tahunan') ?? 12);
        }

        $jatahPenuh = (int) (Pengaturan::get('jatah_cuti_tahunan') ?? 12);
        $masuk      = $karyawan->tanggal_masuk->startOfDay();
        $ultahKerja = $masuk->copy()->addYear(); // tanggal genap 1 tahun kerja

        if ($ultahKerja->year < $tahun) {
            // Sudah lebih dari 1 tahun sebelum tahun ini → jatah penuh dari awal tahun
            return $jatahPenuh;
        }
        if ($ultahKerja->year > $tahun) {
            // Ulang tahun pertama belum tiba di tahun ini → belum berhak
            return 0;
        }

        // Ulang tahun kerja jatuh tepat di tahun ini → proporsional
        // Sisa bulan: dari bulan ulang tahun s.d. Desember (inklusif bulan ulang tahun)
        // Contoh: ultah Jul → Jul,Agu,Sep,Okt,Nov,Des = 6 bulan → 6/12 × 12 = 6 hari
        $sisaBulan = 12 - $ultahKerja->month + 1;
        $sisaBulan = min(12, max(1, $sisaBulan));

        return (int) round($jatahPenuh * $sisaBulan / 12);
    }

    public static function getSaldo(int $karyawanId, int $tahun = null): self
    {
        $tahun = $tahun ?? now()->year;
        return static::firstOrCreate(
            ['karyawan_id' => $karyawanId, 'tahun' => $tahun],
            ['jatah' => static::hitungJatah($karyawanId, $tahun), 'terpakai' => 0]
        );
    }

    public function getSisaAttribute(): int
    {
        // Sisa = jatah + carry_over - terpakai
        return max(0, $this->jatah + ($this->carry_over ?? 0) - $this->terpakai);
    }

    /**
     * Hitung berapa carry-over yang layak dari saldo tahun sebelumnya.
     * Max carry-over: ambil dari max_carry_over record, atau dari Pengaturan, atau unlimited.
     */
    public static function hitungCarryOver(int $karyawanId, int $tahunBaru): int
    {
        $tahunLama = $tahunBaru - 1;
        $saldoLama = static::where('karyawan_id', $karyawanId)->where('tahun', $tahunLama)->first();

        if (!$saldoLama) return 0;

        $sisaTahunLama = max(0, $saldoLama->jatah + ($saldoLama->carry_over ?? 0) - $saldoLama->terpakai);

        // Batas carry-over: dari pengaturan sistem
        $maxCarry = (int) (Pengaturan::get('max_carry_over_cuti', 0));

        if ($maxCarry <= 0) {
            // Unlimited carry-over
            return $sisaTahunLama;
        }

        return min($sisaTahunLama, $maxCarry);
    }

    public function tambahTerpakai(int $hari): void
    {
        $this->increment('terpakai', $hari);
    }

    public function kurangiTerpakai(int $hari): void
    {
        $this->refresh();
        $kurangi = min($hari, $this->terpakai);
        if ($kurangi > 0) {
            $this->decrement('terpakai', $kurangi);
        }
    }
}
