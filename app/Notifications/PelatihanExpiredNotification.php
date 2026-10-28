<?php
namespace App\Notifications;

use App\Helpers\NotifHelper;
use App\Models\Pelatihan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PelatihanExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(public Pelatihan $pelatihan, public int $sisaHari) {}

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
        $p    = $this->pelatihan;
        $kary = $p->karyawan;
        $label = $this->sisaHari <= 0
            ? 'sudah EXPIRED'
            : "akan expired dalam {$this->sisaHari} hari";

        return (new MailMessage)
            ->subject("Pelatihan Expired: {$p->nama_pelatihan} — {$kary->nama}")
            ->greeting('Yth. Tim HRD,')
            ->line("Sertifikat pelatihan berikut {$label}:")
            ->line("Karyawan: {$kary->nama} ({$kary->nik})")
            ->line("Pelatihan: {$p->nama_pelatihan} ({$p->jenis})")
            ->line('Tanggal Expired: ' . ($p->tanggal_expired?->format('d M Y') ?? '-'))
            ->line('Segera lakukan pembaruan sertifikasi.')
            ->action('Lihat Detail', url('/admin/karyawan/' . $kary->id))
            ->salutation('Sistem Absensi PACE');
    }

    public function toDatabase($notifiable): array
    {
        $p     = $this->pelatihan;
        $kary  = $p->karyawan;
        $label = $this->sisaHari <= 0 ? 'EXPIRED' : "exp. {$this->sisaHari} hari";

        return [
            'title' => "Pelatihan Expired: {$p->nama_pelatihan}",
            'body'  => "{$kary->nama} — {$label}",
            'icon'  => 'fas fa-certificate',
            'bg'    => $this->sisaHari <= 0 ? '#fff5f5' : '#fffaf0',
            'color' => $this->sisaHari <= 0 ? '#c53030' : '#c05621',
            'url'   => url('/admin/karyawan/' . $kary->id),
        ];
    }
}
