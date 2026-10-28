<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Leaverequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CutiPengajuanNotification extends Notification
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

    private function stageLabel(): string
    {
        return match ($this->leaverequest->currentStage()) {
            'spv'     => 'SPV',
            'manajer' => 'Manajer',
            'hrd'     => 'HRD',
            default   => '',
        };
    }

    public function toMail($notifiable): MailMessage
    {
        $lr = $this->leaverequest;
        return (new MailMessage)
            ->subject("Pengajuan {$lr->type} Baru - {$lr->karyawan->nama} (Menunggu {$this->stageLabel()})")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Ada pengajuan **{$lr->type}** baru yang menunggu persetujuan Anda ({$this->stageLabel()}).")
            ->line("**Karyawan:** {$lr->karyawan->nama} ({$lr->karyawan->nik})")
            ->line("**Departemen:** " . ($lr->karyawan->department?->name ?? $lr->karyawan->departemen))
            ->line("**Periode:** {$lr->start_date->format('d M Y')} — {$lr->end_date->format('d M Y')}")
            ->line("**Keterangan:** " . ($lr->keterangan ?: '-'))
            ->action('Review Pengajuan', url('/admin/leaverequest'))
            ->salutation('Sistem Absensi');
    }

    public function toDatabase($notifiable): array
    {
        $lr = $this->leaverequest;
        return [
            'title' => "Pengajuan {$lr->type} Baru — Menunggu Approval {$this->stageLabel()}",
            'body'  => "{$lr->karyawan->nama} mengajukan {$lr->type} ({$lr->start_date->format('d M')} — {$lr->end_date->format('d M Y')})",
            'icon'  => 'fas fa-calendar-alt',
            'bg'    => '#fef9c3',
            'color' => '#854d0e',
            'url'   => url('/admin/leaverequest'),
        ];
    }
}
