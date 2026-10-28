<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Lemburan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LemburanStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Lemburan $lemburan) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (NotifHelper::emailAktif() && $notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $lb     = $this->lemburan;
        $status = $lb->status === 'approved' ? 'DISETUJUI ✅' : 'DITOLAK ❌';
        return (new MailMessage)
            ->subject("Pengajuan Lembur Anda {$status}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pengajuan lembur Anda pada **{$lb->tanggal->format('d M Y')}** telah **{$lb->status}**.")
            ->line("**Jam:** {$lb->jam_mulai} — {$lb->jam_selesai} ({$lb->total_jam} jam)")
            ->line("**Alasan:** " . ($lb->alasan ?: '-'))
            ->action('Lihat Detail', url('/admin/karyawan/' . $lb->karyawan_id . '#tab-lemburan'))
            ->salutation('Hormat kami, Tim HRD');
    }

    public function toDatabase($notifiable): array
    {
        $lb      = $this->lemburan;
        $approve = $lb->status === 'approved';
        return [
            'title' => 'Pengajuan Lembur ' . ($approve ? 'Disetujui' : 'Ditolak'),
            'body'  => "{$lb->tanggal->format('d M Y')}: {$lb->jam_mulai}–{$lb->jam_selesai} ({$lb->total_jam} jam)",
            'icon'  => $approve ? 'fas fa-business-time' : 'fas fa-times-circle',
            'bg'    => $approve ? '#e6f9f0' : '#fed7e2',
            'color' => $approve ? '#276749' : '#c53030',
            'url'   => url('/portal/lembur'),
        ];
    }
}
