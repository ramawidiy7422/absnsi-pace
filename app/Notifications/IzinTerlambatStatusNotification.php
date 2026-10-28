<?php
namespace App\Notifications;

use App\Models\IzinTerlambat;
use Illuminate\Notifications\Notification;

class IzinTerlambatStatusNotification extends Notification
{
    public function __construct(public IzinTerlambat $izinTerlambat) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $approved = $this->izinTerlambat->status === 'disetujui';
        return [
            'title' => 'Pengajuan Izin Terlambat ' . ($approved ? 'Disetujui' : 'Ditolak'),
            'body'  => 'Pengajuan izin terlambat Anda pada ' . $this->izinTerlambat->tanggal->translatedFormat('d F Y') . ' telah ' . ($approved ? 'disetujui' : 'ditolak'),
            'icon'  => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approved ? '#f0fff4' : '#fff5f5',
            'color' => $approved ? '#276749' : '#c53030',
            'url'   => '/portal/cuti',
        ];
    }
}
