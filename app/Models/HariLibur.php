<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';
    protected $fillable = ['tanggal','nama','jenis','is_active','tanggal_libur_pengganti'];
    protected $casts    = ['tanggal'=>'date','is_active'=>'boolean','tanggal_libur_pengganti'=>'date'];

    public static function isLibur(\Carbon\Carbon|string $tanggal): bool
    {
        return static::where('is_active', true)
            ->where(function($q) use ($tanggal) {
                // Hari libur biasa
                $q->whereDate('tanggal', $tanggal)
                  ->where('jenis','!=','kerja_pengganti');
            })
            ->orWhere(function($q) use ($tanggal) {
                // Libur pengganti (hari yang digeser jadi libur)
                $q->where('jenis','kerja_pengganti')
                  ->where('is_active', true)
                  ->whereDate('tanggal_libur_pengganti', $tanggal);
            })
            ->exists();
    }

    // Cek apakah tanggal ini adalah hari kerja pengganti (Sabtu masuk)
    public static function isKerjaPengganti(\Carbon\Carbon|string $tanggal): bool
    {
        return static::where('jenis','kerja_pengganti')
            ->where('is_active', true)
            ->whereDate('tanggal', $tanggal)
            ->exists();
    }

    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis) {
            'nasional'        => 'Libur Nasional',
            'cuti_bersama'    => 'Cuti Bersama',
            'internal'        => 'Libur Internal',
            'kerja_pengganti' => 'Kerja Pengganti',
            default           => ucfirst($this->jenis),
        };
    }

    public function getJenisBadgeColorAttribute(): string
    {
        return match($this->jenis) {
            'nasional'        => '#dc2626',
            'cuti_bersama'    => '#7c3aed',
            'internal'        => '#2563eb',
            'kerja_pengganti' => '#d97706',
            default           => '#64748b',
        };
    }
}
