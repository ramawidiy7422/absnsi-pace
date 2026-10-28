<?php
namespace App\Notifications;

use App\Models\Izin;
use Illuminate\Notifications\Notification;

class IzinPengajuanNotification extends Notification
{
    public function __construct(public Izin $izin) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $jenis = match($this->izin->jenis) {
            'sakit' => 'Sakit',
            'alfa'  => 'Alfa',
            default => 'Izin',
        };
        return [
            'title' => "Pengajuan {$jenis} Baru",
            'body'  => "{$this->izin->karyawan->nama} mengajukan {$jenis} pada " . $this->izin->tanggal->translatedFormat('d F Y'),
            'icon'  => 'fas fa-user-times',
            'bg'    => '#fffbeb',
            'color' => '#854d0e',
            'url'   => '/admin/izin',
        ];
    }
}
