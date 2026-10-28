<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Warninglettter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SuratPeringatanNotification extends Notification
{
    use Queueable;

    public function __construct(public Warninglettter $sp) {}

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
        $sp = $this->sp;
        return (new MailMessage)
            ->subject("Surat Peringatan {$sp->type} - {$notifiable->name}")
            ->greeting("Yth. {$notifiable->name},")
            ->line("Anda menerima **Surat Peringatan {$sp->type}** tertanggal **{$sp->date->format('d M Y')}**.")
            ->line("**Keterangan:** {$sp->description}")
            ->line("**Berlaku hingga:** " . ($sp->valid_until ? $sp->valid_until->format('d M Y') : 'Tidak ditentukan'))
            ->line("Harap segera menghubungi HRD untuk klarifikasi lebih lanjut.")
            ->action('Hubungi HRD', url('/'))
            ->salutation('Hormat kami, Tim HRD');
    }

    public function toDatabase($notifiable): array
    {
        $sp = $this->sp;
        return [
            'title' => "Surat Peringatan {$sp->type} Diterbitkan",
            'body'  => "{$sp->date->format('d M Y')}: {$sp->description}",
            'icon'  => 'fas fa-exclamation-triangle',
            'bg'    => '#fff5f5',
            'color' => '#c53030',
            'url'   => url('/portal'),
        ];
    }
}
