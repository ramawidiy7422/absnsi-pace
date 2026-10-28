<?php
namespace App\Notifications;

use App\Models\Izin;
use Illuminate\Notifications\Notification;

class IzinStatusNotification extends Notification
{
    public function __construct(public Izin $izin) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $approved = $this->izin->status === 'disetujui';
        $jenis    = match($this->izin->jenis) {
            'sakit' => 'Sakit', 'alfa' => 'Alfa', default => 'Izin'
        };
        return [
            'title' => "Pengajuan {$jenis} " . ($approved ? 'Disetujui' : 'Ditolak'),
            'body'  => "Pengajuan {$jenis} Anda pada " . $this->izin->tanggal->translatedFormat('d F Y') . " telah " . ($approved ? 'disetujui' : 'ditolak'),
            'icon'  => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approved ? '#f0fff4' : '#fff5f5',
            'color' => $approved ? '#276749' : '#c53030',
            'url'   => '/portal/cuti',
        ];
    }
}
