<?php
namespace App\Notifications;

use App\Models\SuratDinas;
use Illuminate\Notifications\Notification;

class SuratDinasStatusNotification extends Notification
{
    public function __construct(public SuratDinas $spd) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $approved = $this->spd->status === 'disetujui';
        return [
            'title' => 'Surat Perintah Dinas ' . ($approved ? 'Disetujui' : 'Ditolak'),
            'body'  => 'SPD No. ' . $this->spd->nomor_spd . ' telah ' . ($approved ? 'disetujui' : 'ditolak'),
            'icon'  => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approved ? '#f0fff4' : '#fff5f5',
            'color' => $approved ? '#276749' : '#c53030',
            'url'   => '/admin/surat-dinas/' . $this->spd->id,
        ];
    }
}
