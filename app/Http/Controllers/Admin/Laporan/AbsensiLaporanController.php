<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class AbsensiLaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan      = $request->bulan ?? now()->month;
        $tahun      = $request->tahun ?? now()->year;
        $karyawanId = $request->karyawan_id;

        $deptIds = $this->scopedDepartmentIds();

        $query = Absensi::with(['karyawan.department', 'shift'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])));

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        $absensi  = $query->orderBy('tanggal')->paginate(20)->withQueryString();
        $karyawan = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->orderBy('nama')->get();

        $rekap = Absensi::selectRaw('status, COUNT(*) as total')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($karyawanId, fn($q) => $q->where('karyawan_id', $karyawanId))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.laporan.index', compact('absensi', 'karyawan', 'bulan', 'tahun', 'rekap'));
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $deptIds = $this->scopedDepartmentIds();

        $absensi = Absensi::with(['karyawan.department', 'shift'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($deptIds !== null, fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($request->karyawan_id, fn($q) => $q->where('karyawan_id', $request->karyawan_id))
            ->orderBy('tanggal')
            ->get();

        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.pdf', compact('absensi', 'bulan', 'tahun', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download("laporan-absensi-{$bulan}-{$tahun}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        return Excel::download(
            new AbsensiExport($bulan, $tahun, $request->karyawan_id, $this->scopedDepartmentIds()),
            "laporan-absensi-{$bulan}-{$tahun}.xlsx"
        );
    }
}
