<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupNotifikasi extends Command
{
    protected $signature = 'notifikasi:cleanup
                            {--hari=30 : Hapus notifikasi yang sudah dibaca lebih dari N hari}
                            {--dry-run  : Tampilkan jumlah saja, tidak hapus}';

    protected $description = 'Bersihkan notifikasi lama yang sudah dibaca untuk menjaga performa DB';

    public function handle(): int
    {
        $hari   = (int) $this->option('hari');
        $batas  = Carbon::now()->subDays($hari);
        $dryRun = $this->option('dry-run');

        $query = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $batas);

        $jumlah = $query->count();

        if ($dryRun) {
            $this->info("Dry-run: {$jumlah} notifikasi yang sudah dibaca lebih dari {$hari} hari akan dihapus.");
            return self::SUCCESS;
        }

        if ($jumlah === 0) {
            $this->info('Tidak ada notifikasi lama yang perlu dibersihkan.');
            return self::SUCCESS;
        }

        $query->delete();
        $this->info("Berhasil menghapus {$jumlah} notifikasi lama (sudah dibaca > {$hari} hari).");

        return self::SUCCESS;
    }
}
