<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Pengumuman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PengumumanNotification extends Notification
{
    use Queueable;

    public function __construct(public Pengumuman $pengumuman) {}

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
        $p = $this->pengumuman;
        return (new MailMessage)
            ->subject("[{$p->badge_prioritas['label']}] {$p->judul}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Ada **{$p->badge_jenis['label']}** baru untuk Anda.")
            ->line("**{$p->judul}**")
            ->line($p->isi)
            ->line("Berlaku: {$p->tanggal_mulai->format('d M Y')}" . ($p->tanggal_selesai ? " s/d {$p->tanggal_selesai->format('d M Y')}" : ''))
            ->action('Lihat di Portal', url('/portal'))
            ->salutation('Sistem Absensi');
    }

    public function toDatabase($notifiable): array
    {
        $p   = $this->pengumuman;
        $bp  = $p->badge_prioritas;
        $bj  = $p->badge_jenis;
        return [
            'title' => $bj['label'] . ': ' . $p->judul,
            'body'  => \Illuminate\Support\Str::limit(strip_tags($p->isi), 120),
            'icon'  => $bj['icon'],
            'bg'    => $bp['bg'],
            'color' => $bp['text'],
            'url'   => url('/portal'),
        ];
    }
}
