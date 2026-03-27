<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan      = $request->bulan ?? now()->month;
        $tahun      = $request->tahun ?? now()->year;
        $karyawanId = $request->karyawan_id;

        $query = Absensi::with(['karyawan', 'shift'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        $absensi  = $query->orderBy('tanggal')->paginate(20);
        $karyawan = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        $rekap = Absensi::selectRaw('status, COUNT(*) as total')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($karyawanId, fn($q) => $q->where('karyawan_id', $karyawanId))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.laporan.index', compact('absensi', 'karyawan', 'bulan', 'tahun', 'rekap'));
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $absensi = Absensi::with(['karyawan', 'shift'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($request->karyawan_id, fn($q) => $q->where('karyawan_id', $request->karyawan_id))
            ->orderBy('tanggal')
            ->get();

        $pdf = Pdf::loadView('admin.laporan.pdf', compact('absensi', 'bulan', 'tahun'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download("laporan-absensi-{$bulan}-{$tahun}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        return Excel::download(
            new AbsensiExport($bulan, $tahun, $request->karyawan_id),
            "laporan-absensi-{$bulan}-{$tahun}.xlsx"
        );
    }

    public function rekapBulanan(Request $request)
    {
        $bulan       = $request->bulan ?? now()->month;
        $tahun       = $request->tahun ?? now()->year;
        $departemen  = $request->departemen;

        $query = Karyawan::where('status', 'aktif')->with('shift');
        if ($departemen) {
            $query->where('departemen', $departemen);
        }
        $karyawanList = $query->orderBy('departemen')->orderBy('nama')->get();

        // Hitung rekap per karyawan
        $rekap = $karyawanList->map(function ($k) use ($bulan, $tahun) {
            $absensi = Absensi::where('karyawan_id', $k->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            return [
                'karyawan'  => $k,
                'hadir'     => $absensi->where('status', 'hadir')->count(),
                'terlambat' => $absensi->where('status', 'terlambat')->count(),
                'izin'      => $absensi->where('status', 'izin')->count(),
                'sakit'     => $absensi->where('status', 'sakit')->count(),
                'alpha'     => $absensi->where('status', 'alpha')->count(),
                'total'     => $absensi->count(),
            ];
        });

        $departemenList = Karyawan::where('status', 'aktif')
            ->distinct()->orderBy('departemen')->pluck('departemen');

        return view('admin.laporan.rekap-bulanan', compact(
            'rekap', 'bulan', 'tahun', 'departemenList', 'departemen'
        ));
    }

    public function slipPdf(Request $request, Karyawan $karyawan)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with('shift')
            ->orderBy('tanggal')
            ->get();

        // Hitung hari kerja dalam bulan tersebut (Senin-Sabtu)
        $hariKerja = 0;
        $start = Carbon::createFromDate($tahun, $bulan, 1);
        $end   = $start->copy()->endOfMonth();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY) $hariKerja++;
        }

        $rekap = [
            'hadir'     => $absensi->where('status', 'hadir')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
            'izin'      => $absensi->where('status', 'izin')->count(),
            'sakit'     => $absensi->where('status', 'sakit')->count(),
            'alpha'     => $absensi->where('status', 'alpha')->count(),
            'total'     => $absensi->count(),
            'hari_kerja'=> $hariKerja,
        ];
        $rekap['persen'] = $hariKerja > 0
            ? round(($rekap['hadir'] + $rekap['terlambat']) / $hariKerja * 100)
            : 0;

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('admin.laporan.slip-pdf', compact(
            'karyawan', 'absensi', 'rekap', 'bulan', 'tahun', 'namaBulan'
        ))->setPaper('a4', 'portrait');

        $filename = 'slip-absensi-' . $karyawan->nik . '-' . $bulan . '-' . $tahun . '.pdf';
        return $pdf->download($filename);
    }
}
