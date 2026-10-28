<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Department;
use App\Models\Karyawan;
use App\Exports\KeterlambatanExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class KeterlambatanController extends Controller
{
    public function index(Request $request)
    {
        $bulan         = $request->bulan ?? now()->month;
        $tahun         = $request->tahun ?? now()->year;
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $minKali       = $request->min_kali ?? 1;

        // Ranking karyawan terlambat
        $ranking = Absensi::where('status', 'terlambat')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->with('karyawan.department')
            ->get()
            ->groupBy('karyawan_id')
            ->map(function($rows) {
                return (object)[
                    'karyawan'        => $rows->first()->karyawan,
                    'karyawan_id'     => $rows->first()->karyawan_id,
                    'jumlah_terlambat'=> $rows->count(),
                    'tanggal_list'    => $rows->pluck('tanggal'),
                ];
            })
            ->filter(fn($r) => $r->jumlah_terlambat >= $minKali)
            ->sortByDesc('jumlah_terlambat')
            ->values();

        // Detail terlambat bulan ini (semua record)
        $detailQuery = Absensi::with(['karyawan.department','shift'])
            ->where('status', 'terlambat')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->orderBy('tanggal');
        $detail = $detailQuery->paginate(25)->withQueryString();

        // Stats
        $totalTerlambat    = Absensi::where('status','terlambat')->whereMonth('tanggal',$bulan)->whereYear('tanggal',$tahun)->count();
        $karyawanTerlambat = $ranking->count();
        $totalHadir        = Absensi::whereIn('status',['hadir','terlambat'])->whereMonth('tanggal',$bulan)->whereYear('tanggal',$tahun)->count();
        $pctTerlambat      = $totalHadir > 0 ? round($totalTerlambat / $totalHadir * 100, 1) : 0;

        $deptList = Department::orderBy('name')->get();

        return view('admin.laporan.keterlambatan', compact(
            'ranking','detail','bulan','tahun','department_id','deptList','minKali',
            'totalTerlambat','karyawanTerlambat','pctTerlambat','totalHadir'
        ));
    }

    public function exportCsv(Request $request)
    {
        $bulan         = $request->bulan ?? now()->month;
        $tahun         = $request->tahun ?? now()->year;
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $data = Absensi::with(['karyawan.department','shift'])
            ->where('status', 'terlambat')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->orderBy('tanggal')
            ->get();

        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F_Y');
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=keterlambatan_{$namaBulan}.csv"];

        $callback = function() use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID Karyawan','Nama','Departemen','Tanggal','Hari','Jam Masuk','Jam Shift','Shift']);
            foreach ($data as $a) {
                fputcsv($out, [
                    $a->karyawan?->nik ?? '-',
                    $a->karyawan?->nama ?? '-',
                    $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-',
                    $a->tanggal->format('d/m/Y'),
                    $a->tanggal->translatedFormat('l'),
                    $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-',
                    $a->shift ? substr($a->shift->jam_masuk, 0, 5) : '-',
                    $a->shift?->nama_shift ?? '-',
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = $request->bulan ?? now()->month;
        $tahun         = $request->tahun ?? now()->year;
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $minKali       = $request->min_kali ?? 1;

        $ranking = Absensi::where('status', 'terlambat')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->with('karyawan.department')
            ->get()
            ->groupBy('karyawan_id')
            ->map(function($rows) {
                return (object)[
                    'karyawan'         => $rows->first()->karyawan,
                    'jumlah_terlambat' => $rows->count(),
                    'tanggal_list'     => $rows->pluck('tanggal'),
                ];
            })
            ->filter(fn($r) => $r->jumlah_terlambat >= $minKali)
            ->sortByDesc('jumlah_terlambat')
            ->values();

        $dept             = $department_id ? Department::find($department_id) : null;
        $namaBulan        = Carbon::create($tahun, $bulan)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.keterlambatan-pdf', compact('ranking', 'bulan', 'tahun', 'namaBulan', 'dept', 'minKali', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("keterlambatan_{$namaBulan}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $dept  = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $namaBulan = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F-Y');
        return Excel::download(new KeterlambatanExport($bulan, $tahun, $dept), "keterlambatan-{$namaBulan}.xlsx");
    }
}
