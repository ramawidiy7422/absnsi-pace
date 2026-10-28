<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\HariLibur;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Leaverequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlphaGeneratorController extends Controller
{
    public function index()
    {
        // Statistik alpha bulan ini
        $bulan = now()->month;
        $tahun = now()->year;

        $alphaBulanIni = Absensi::where('status', 'alpha')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->count();

        $alphaKaryawan = Absensi::where('status', 'alpha')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->distinct('karyawan_id')
            ->count('karyawan_id');

        // Alpha 7 hari terakhir per tanggal
        $alpha7Hari = Absensi::where('status', 'alpha')
            ->whereBetween('tanggal', [now()->subDays(6)->toDateString(), today()->toDateString()])
            ->selectRaw('tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn($a) => $a->tanggal instanceof \Carbon\Carbon ? $a->tanggal->format('Y-m-d') : $a->tanggal);

        // Preview: siapa yang akan di-alpha kemarin (jika belum dijalankan)
        $kemarin = Carbon::yesterday();
        $preview = $this->getAlphaPreview($kemarin);

        // Riwayat 10 log alpha dari AuditLog
        $riwayatLog = AuditLog::where('action', 'create')
            ->where('deskripsi', 'like', 'Generate alpha%')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.absensi.alpha-generator', compact(
            'alphaBulanIni', 'alphaKaryawan', 'alpha7Hari',
            'preview', 'kemarin', 'riwayatLog'
        ));
    }

    public function run(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
        ]);

        $tanggal = Carbon::parse($request->tanggal);

        // Cek hari libur
        if (HariLibur::isLibur($tanggal)) {
            $namaLibur = HariLibur::whereDate('tanggal', $tanggal)->value('nama');
            return back()->with('error', "Tanggal {$tanggal->format('d/m/Y')} adalah hari libur ({$namaLibur}). Tidak ada alpha yang di-generate.");
        }

        $result = $this->generateAlpha($tanggal);

        if ($result['berhasil'] === 0 && empty($result['skipped'])) {
            return back()->with('info', "Semua karyawan sudah tercatat absensinya pada {$tanggal->format('d/m/Y')}. Tidak ada alpha yang perlu di-generate.");
        }

        $msg = "Generate alpha {$tanggal->format('d/m/Y')} selesai: <strong>{$result['berhasil']}</strong> record alpha dibuat.";
        if ($result['sudahAda'] > 0) {
            $msg .= " <strong>{$result['sudahAda']}</strong> karyawan sudah punya absensi/izin/cuti.";
        }

        AuditLog::catat('create', "Generate alpha manual {$tanggal->format('d/m/Y')}: {$result['berhasil']} karyawan");

        return back()->with('success', $msg);
    }

    private function getAlphaPreview(Carbon $tanggal): array
    {
        if (HariLibur::isLibur($tanggal)) return [];

        $karyawanAktif = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])->get()
            ->filter(fn($k) => !$k->isBebasAbsen());

        $sudahAbsen = Absensi::whereDate('tanggal', $tanggal)->pluck('karyawan_id')->toArray();
        $punyaIzin  = Izin::where('status','disetujui')->whereDate('tanggal', $tanggal)->pluck('karyawan_id')->toArray();
        $punyaCuti  = Leaverequest::where('status','disetujui')
            ->where('start_date','<=',$tanggal->toDateString())
            ->where('end_date','>=',$tanggal->toDateString())
            ->pluck('karyawan_id')->toArray();

        $dikecualikan = array_unique(array_merge($sudahAbsen, $punyaIzin, $punyaCuti));

        return $karyawanAktif->whereNotIn('id', $dikecualikan)->values()->toArray();
    }

    private function generateAlpha(Carbon $tanggal): array
    {
        $karyawanAktif = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])->get()
            ->filter(fn($k) => !$k->isBebasAbsen());

        $sudahAbsen = Absensi::whereDate('tanggal', $tanggal)->pluck('karyawan_id')->toArray();
        $punyaIzin  = Izin::where('status','disetujui')->whereDate('tanggal', $tanggal)->pluck('karyawan_id')->toArray();
        $punyaCuti  = Leaverequest::where('status','disetujui')
            ->where('start_date','<=',$tanggal->toDateString())
            ->where('end_date','>=',$tanggal->toDateString())
            ->pluck('karyawan_id')->toArray();

        $dikecualikan = array_unique(array_merge($sudahAbsen, $punyaIzin, $punyaCuti));
        $alphaList    = $karyawanAktif->whereNotIn('id', $dikecualikan);

        $berhasil = 0;
        $skipped  = [];

        foreach ($alphaList as $k) {
            // Double-check: jangan buat duplikat
            $exists = Absensi::where('karyawan_id', $k->id)
                ->whereDate('tanggal', $tanggal)->exists();
            if ($exists) {
                $skipped[] = $k->nama;
                continue;
            }

            Absensi::create([
                'karyawan_id' => $k->id,
                'shift_id'    => $k->resolveShift($tanggal)?->id ?? $k->shift_id,
                'tanggal'     => $tanggal->toDateString(),
                'jam_masuk'   => null,
                'jam_keluar'  => null,
                'status'      => 'alpha',
                'metode'      => 'manual',
                'keterangan'  => 'Auto-generate: tidak hadir tanpa keterangan',
            ]);
            $berhasil++;
        }

        return [
            'berhasil' => $berhasil,
            'sudahAda' => count($dikecualikan),
            'skipped'  => $skipped,
        ];
    }
}
