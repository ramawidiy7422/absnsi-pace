<?php
namespace App\Notifications;

use App\Models\IzinPulangCepat;
use Illuminate\Notifications\Notification;

class IzinPulangCepatPengajuanNotification extends Notification
{
    public function __construct(public IzinPulangCepat $izinPulangCepat) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Pengajuan Izin Pulang Cepat Baru',
            'body'  => "{$this->izinPulangCepat->karyawan->nama} mengajukan izin pulang cepat pada " . $this->izinPulangCepat->tanggal->translatedFormat('d F Y'),
            'icon'  => 'fas fa-sign-out-alt',
            'bg'    => '#fefcbf',
            'color' => '#744210',
            'url'   => '/admin/izin-pulang-cepat',
        ];
    }
}
