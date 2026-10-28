<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Leaverequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CutiStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Leaverequest $leaverequest) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (NotifHelper::emailAktif() && $notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    private function rejectedStageLabel(): string
    {
        return match ($this->leaverequest->rejected_stage) {
            'spv'     => 'SPV',
            'manajer' => 'Manajer',
            'hrd'     => 'HRD',
            default   => '',
        };
    }

    public function toMail($notifiable): MailMessage
    {
        $lr     = $this->leaverequest;
        $status = $lr->status === 'disetujui' ? 'DISETUJUI ✅' : 'DITOLAK ❌';
        $line   = $lr->status === 'ditolak' && $lr->rejected_stage
            ? "Pengajuan **{$lr->type}** Anda **ditolak** pada tahap {$this->rejectedStageLabel()}."
            : "Pengajuan **{$lr->type}** Anda telah **{$lr->status}**.";
        return (new MailMessage)
            ->subject("Pengajuan {$lr->type} Anda {$status}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line($line)
            ->line("**Periode:** {$lr->start_date->format('d M Y')} — {$lr->end_date->format('d M Y')}")
            ->line("**Keterangan:** " . ($lr->keterangan ?: '-'))
            ->action('Lihat Detail', url('/admin/karyawan/' . $lr->karyawan_id . '#tab-leaverequest'))
            ->salutation('Hormat kami, Tim HRD');
    }

    public function toDatabase($notifiable): array
    {
        $lr      = $this->leaverequest;
        $approve = $lr->status === 'disetujui';
        $body    = "Periode: {$lr->start_date->format('d M')} — {$lr->end_date->format('d M Y')}";
        if (!$approve && $lr->rejected_stage) {
            $body .= " — Ditolak pada tahap {$this->rejectedStageLabel()}";
        }
        return [
            'title' => "Pengajuan {$lr->type} " . ($approve ? 'Disetujui' : 'Ditolak'),
            'body'  => $body,
            'icon'  => $approve ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'bg'    => $approve ? '#c6f6d5' : '#fed7e2',
            'color' => $approve ? '#276749' : '#c53030',
            'url'   => url('/portal/cuti'),
        ];
    }
}
