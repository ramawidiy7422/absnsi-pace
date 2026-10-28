<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Warninglettter;
use Illuminate\Console\Command;

class AutoCloseSP extends Command
{
    protected $signature   = 'sp:auto-close';
    protected $description = 'Otomatis tutup (selesai) SP yang valid_until-nya sudah lewat';

    public function handle(): void
    {
        $expired = Warninglettter::where('status', 'aktif')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->startOfDay())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('Tidak ada SP yang perlu ditutup.');
            return;
        }

        foreach ($expired as $sp) {
            $sp->update(['status' => 'selesai']);
            AuditLog::catat('update', "Auto-close {$sp->type} karyawan ID {$sp->karyawan_id} — valid_until {$sp->valid_until->format('d/m/Y')} sudah lewat", $sp);
        }

        $this->info("Auto-close {$expired->count()} SP berhasil.");
    }
}
