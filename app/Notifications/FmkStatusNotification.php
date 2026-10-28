<?php
namespace App\Notifications;

use App\Models\FormulirMeninggalkanKerja;
use Illuminate\Notifications\Notification;

class FmkStatusNotification extends Notification
{
    public function __construct(public FormulirMeninggalkanKerja $fmk) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $approved = $this->fmk->status === 'disetujui';
        return [
            'title' => 'Formulir Meninggalkan Kerja ' . ($approved ? 'Disetujui' : 'Ditolak'),
            'body'  => 'FMK No. ' . $this->fmk->nomor_fmk . ' pada ' . $this->fmk->tanggal->translatedFormat('d F Y') . ' telah ' . ($approved ? 'disetujui' : 'ditolak'),
            'icon'  => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approved ? '#f0fff4' : '#fff5f5',
            'color' => $approved ? '#276749' : '#c53030',
            'url'   => '/portal',
        ];
    }
}
