<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Department;
use App\Models\Karyawan;
use App\Exports\PelanggaranExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $jenis         = $request->jenis ?? 'semua';

        $query = Absensi::with(['karyawan.department', 'shift:id,nama_shift,jam_masuk'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)));

        match ($jenis) {
            'alpha'           => $query->where('status', 'alpha'),
            'terlambat_tanpa' => $query->where('status', 'terlambat')->whereNull('keterangan_terlambat'),
            'terlambat_izin'  => $query->where('status', 'terlambat')->whereNotNull('keterangan_terlambat'),
            default           => $query->whereIn('status', ['alpha', 'terlambat']),
        };

        $detail = $query->orderBy('tanggal', 'desc')->paginate(30)->withQueryString();

        $baseQ = Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)));

        $totalAlpha          = (clone $baseQ)->where('status', 'alpha')->count();
        $totalTerlambatTanpa = (clone $baseQ)->where('status', 'terlambat')->whereNull('keterangan_terlambat')->count();
        $totalTerlambatIzin  = (clone $baseQ)->where('status', 'terlambat')->whereNotNull('keterangan_terlambat')->count();
        $totalSemua          = $totalAlpha + $totalTerlambatTanpa + $totalTerlambatIzin;

        $ranking = Absensi::whereIn('status', ['alpha', 'terlambat'])
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->with('karyawan.department')
            ->get()
            ->groupBy('karyawan_id')
            ->map(fn($rows) => (object)[
                'karyawan'        => $rows->first()->karyawan,
                'alpha'           => $rows->where('status', 'alpha')->count(),
                'terlambat_tanpa' => $rows->where('status','terlambat')->whereNull('keterangan_terlambat')->count(),
                'terlambat_izin'  => $rows->where('status','terlambat')->whereNotNull('keterangan_terlambat')->count(),
                'total'           => $rows->count(),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $deptList  = Department::orderBy('name')->get();
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return view('admin.laporan.pelanggaran', compact(
            'detail', 'ranking', 'bulan', 'tahun', 'department_id', 'jenis', 'deptList', 'namaBulan',
            'totalAlpha', 'totalTerlambatTanpa', 'totalTerlambatIzin', 'totalSemua'
        ));
    }

    public function exportCsv(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $jenis         = $request->jenis ?? 'semua';

        $query = Absensi::with(['karyawan.department','shift'])
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)));

        match ($jenis) {
            'alpha'           => $query->where('status', 'alpha'),
            'terlambat_tanpa' => $query->where('status', 'terlambat')->whereNull('keterangan_terlambat'),
            'terlambat_izin'  => $query->where('status', 'terlambat')->whereNotNull('keterangan_terlambat'),
            default           => $query->whereIn('status', ['alpha', 'terlambat']),
        };

        $data      = $query->orderBy('tanggal')->get();
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F_Y');
        $headers   = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=pelanggaran_{$namaBulan}.csv"];

        return Response::stream(function() use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['NIK','Nama','Departemen','Tanggal','Hari','Status','Jam Masuk','Keterangan']);
            foreach ($data as $a) {
                $status = $a->status === 'alpha' ? 'Alpha' : ($a->keterangan_terlambat ? 'Terlambat (Berizin)' : 'Terlambat');
                fputcsv($out, [
                    $a->karyawan?->nik ?? '-',
                    $a->karyawan?->nama ?? '-',
                    $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-',
                    $a->tanggal->format('d/m/Y'),
                    $a->tanggal->translatedFormat('l'),
                    $status,
                    $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-',
                    $a->keterangan_terlambat ?? '-',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $jenis         = $request->jenis ?? 'semua';

        $query = Absensi::with(['karyawan.department', 'shift'])
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)));

        match ($jenis) {
            'alpha'           => $query->where('status', 'alpha'),
            'terlambat_tanpa' => $query->where('status', 'terlambat')->whereNull('keterangan_terlambat'),
            'terlambat_izin'  => $query->where('status', 'terlambat')->whereNotNull('keterangan_terlambat'),
            default           => $query->whereIn('status', ['alpha', 'terlambat']),
        };

        $ranking = Absensi::whereIn('status', ['alpha', 'terlambat'])
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->with('karyawan.department')
            ->get()
            ->groupBy('karyawan_id')
            ->map(fn($rows) => (object)[
                'karyawan'        => $rows->first()->karyawan,
                'alpha'           => $rows->where('status', 'alpha')->count(),
                'terlambat_tanpa' => $rows->where('status', 'terlambat')->whereNull('keterangan_terlambat')->count(),
                'terlambat_izin'  => $rows->where('status', 'terlambat')->whereNotNull('keterangan_terlambat')->count(),
                'total'           => $rows->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $data      = $query->orderBy('tanggal')->get();
        $dept             = $department_id ? Department::find($department_id) : null;
        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $totalAlpha          = $data->where('status', 'alpha')->count();
        $totalTerlambatTanpa = $data->where('status', 'terlambat')->whereNull('keterangan_terlambat')->count();
        $totalTerlambatIzin  = $data->where('status', 'terlambat')->whereNotNull('keterangan_terlambat')->count();

        $pdf = Pdf::loadView('admin.laporan.pelanggaran-pdf', compact(
            'data', 'ranking', 'bulan', 'tahun', 'namaBulan', 'dept', 'jenis',
            'totalAlpha', 'totalTerlambatTanpa', 'totalTerlambatIzin',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("pelanggaran_{$namaBulan}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $namaBulan = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F-Y');
        return Excel::download(
            new PelanggaranExport($bulan, $tahun, $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null), $request->jenis ?? 'semua'),
            "pelanggaran-{$namaBulan}.xlsx"
        );
    }
}
