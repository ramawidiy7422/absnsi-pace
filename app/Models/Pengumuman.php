<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';
    protected $fillable = ['user_id','judul','isi','jenis','prioritas','target_departemen','tanggal_mulai','tanggal_selesai','aktif','tampil_popup','lampiran'];
    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'aktif' => 'boolean', 'tampil_popup' => 'boolean'];

    public function penulis() { return $this->belongsTo(User::class, 'user_id'); }

    public function getBadgePrioritasAttribute(): array
    {
        return match($this->prioritas) {
            'mendesak' => ['bg' => '#fed7e2', 'text' => '#c53030', 'label' => 'Mendesak'],
            'penting'  => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Penting'],
            default    => ['bg' => '#e2e8f0', 'text' => '#4a5568', 'label' => 'Normal'],
        };
    }

    public function getBadgeJenisAttribute(): array
    {
        return match($this->jenis) {
            'memo'  => ['bg' => '#faf5ff', 'text' => '#553c9a', 'label' => 'Memo', 'icon' => 'fas fa-file-alt'],
            default => ['bg' => '#ebf8ff', 'text' => '#2b6cb0', 'label' => 'Pengumuman', 'icon' => 'fas fa-bullhorn'],
        };
    }

    public function scopeAktifSekarang($query)
    {
        return $query->where('aktif', true)
            ->where('tanggal_mulai', '<=', today())
            ->where(fn($q) => $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', today()));
    }
}
