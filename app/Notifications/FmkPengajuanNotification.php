<?php
namespace App\Notifications;

use App\Models\FormulirMeninggalkanKerja;
use Illuminate\Notifications\Notification;

class FmkPengajuanNotification extends Notification
{
    public function __construct(public FormulirMeninggalkanKerja $fmk) {}

    public function via($notifiable): array { return ['database']; }

    private function stageLabel(): string
    {
        return match ($this->fmk->currentStage()) {
            'spv'     => 'SPV',
            'manajer' => 'Manajer',
            'hrd'     => 'HRD',
            default   => '',
        };
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'FMK Baru: ' . $this->fmk->karyawan?->nama . ' — Menunggu ' . $this->stageLabel(),
            'body'  => 'Pengajuan Formulir Meninggalkan Kerja No. ' . $this->fmk->nomor_fmk . ' pada ' . $this->fmk->tanggal->translatedFormat('d F Y') . ' menunggu persetujuan Anda.',
            'icon'  => 'fas fa-walking',
            'bg'    => '#fffbeb',
            'color' => '#c05621',
            'url'   => '/admin/formulir/meninggalkan-kerja/' . $this->fmk->id,
        ];
    }
}
