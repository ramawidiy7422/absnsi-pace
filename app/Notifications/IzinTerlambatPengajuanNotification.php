<?php
namespace App\Notifications;

use App\Models\IzinTerlambat;
use Illuminate\Notifications\Notification;

class IzinTerlambatPengajuanNotification extends Notification
{
    public function __construct(public IzinTerlambat $izinTerlambat) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Pengajuan Izin Terlambat Baru',
            'body'  => "{$this->izinTerlambat->karyawan->nama} mengajukan izin terlambat pada " . $this->izinTerlambat->tanggal->translatedFormat('d F Y'),
            'icon'  => 'fas fa-clock',
            'bg'    => '#fffbeb',
            'color' => '#854d0e',
            'url'   => '/admin/izin-terlambat',
        ];
    }
}
