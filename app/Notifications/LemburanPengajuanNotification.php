<?php
namespace App\Notifications;

use App\Models\Lemburan;
use Illuminate\Notifications\Notification;

class LemburanPengajuanNotification extends Notification
{
    public function __construct(public Lemburan $lemburan) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        $lb = $this->lemburan;
        return [
            'title' => 'Pengajuan Lembur Baru',
            'body'  => "{$lb->karyawan->nama} mengajukan lembur pada " . $lb->tanggal->translatedFormat('d F Y') . " ({$lb->total_jam} jam)",
            'icon'  => 'fas fa-business-time',
            'bg'    => '#eff6ff',
            'color' => '#1e40af',
            'url'   => '/admin/lemburan',
        ];
    }
}
