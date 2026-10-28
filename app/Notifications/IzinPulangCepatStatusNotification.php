<?php
namespace App\Notifications;

use App\Models\IzinPulangCepat;
use Illuminate\Notifications\Notification;

class IzinPulangCepatStatusNotification extends Notification
{
    public function __construct(public IzinPulangCepat $izinPulangCepat) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $approved = $this->izinPulangCepat->status === 'disetujui';
        return [
            'title' => 'Pengajuan Izin Pulang Cepat ' . ($approved ? 'Disetujui' : 'Ditolak'),
            'body'  => 'Pengajuan izin pulang cepat Anda pada ' . $this->izinPulangCepat->tanggal->translatedFormat('d F Y') . ' telah ' . ($approved ? 'disetujui' : 'ditolak'),
            'icon'  => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approved ? '#f0fff4' : '#fff5f5',
            'color' => $approved ? '#276749' : '#c53030',
            'url'   => '/portal/cuti',
        ];
    }
}
