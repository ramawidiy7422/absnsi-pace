<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalShiftLaporanController extends Controller
{
    private const JABATAN_ORDER = [
        'Direktur', 'Plant Manager', 'Manager', 'Asst. Manager', 'Supervisor',
        'Apoteker', 'Analis', 'AI INTERAKTIF',
        'Admin', 'HRD Staff', 'IT Staff', 'Staff', 'Staff Accounting', 'Med Rep',
        'Senior Operator', 'Teknisi', 'Operator', 'Pelaksana',
        'Satpam / Security', 'Sopir / Driver', 'Kebersihan / CS',
    ];

    private function jabatanOrderSql(string $col = 'jabatan'): string
    {
        $cases = implode(',', array_map(fn($j) => '"' . addslashes($j) . '"', self::JABATAN_ORDER));
        return "FIELD({$col}, {$cases}) = 0, FIELD({$col}, {$cases})";
    }
    public function index(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $view          = in_array($request->view, ['grid', 'kalender']) ? $request->view : 'grid';
        $department_id = $request->department_id;
        $shiftId       = $request->shift_id;
        $karyawanId    = $request->karyawan_id;

        $startDate  = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate    = $startDate->copy()->endOfMonth();
        $jumlahHari = $startDate->daysInMonth;

        $shifts         = Shift::where('status', 'aktif')->orderBy('nama_shift')->get();

        $deptIds = $this->scopedDepartmentIds();
        $departemenList = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift', 'department', 'employeshifts' => function ($q) use ($startDate, $endDate) {
                $q->with('shift')
                  ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            }]);

        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds ?: [0]);
        } elseif ($department_id) {
            $query->where('department_id', $department_id);
        }
        if ($shiftId)       $query->where('shift_id', $shiftId);

        $karyawanList = $query
            ->leftJoin('departments', 'departments.id', '=', 'karyawan.department_id')
            ->orderBy('departments.name')
            ->orderByRaw($this->jabatanOrderSql('karyawan.jabatan'))
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->get();

        $jadwal = [];
        foreach ($karyawanList as $k) {
            $jadwal[$k->id] = [];
            foreach ($k->employeshifts as $es) {
                $jadwal[$k->id][$es->date->format('Y-m-d')] = $es->shift;
            }
        }

        $karyawan     = null;
        $jadwalKalend = collect();

        if ($karyawanId) {
            $karyawan = Karyawan::with(['shift','department'])
                ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
                ->find($karyawanId);
            if ($karyawan) {
                $jadwalKalend = \App\Models\Employeshift::with('shift')
                    ->where('karyawan_id', $karyawan->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get()
                    ->keyBy(fn($j) => $j->date->format('Y-m-d'));
            }
        }

        $namaBulan = $startDate->translatedFormat('F Y');

        return view('admin.laporan.jadwal-shift', compact(
            'bulan', 'tahun', 'view', 'department_id', 'shiftId',
            'shifts', 'departemenList', 'karyawanList',
            'jadwal', 'jumlahHari', 'startDate', 'namaBulan',
            'karyawanId', 'karyawan', 'jadwalKalend'
        ));
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $request->department_id;
        $departemen    = $department_id ? Department::find($department_id)?->name : null;

        $startDate  = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate    = $startDate->copy()->endOfMonth();
        $jumlahHari = $startDate->daysInMonth;

        $shifts = Shift::where('status', 'aktif')->orderBy('nama_shift')->get();

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null && $department_id && !in_array((int) $department_id, $deptIds, true)) {
            abort(403);
        }

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift', 'department', 'employeshifts' => function ($q) use ($startDate, $endDate) {
                $q->with('shift')->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            }]);

        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds ?: [0]);
        } elseif ($department_id) {
            $query->where('department_id', $department_id);
        }

        $karyawanList = $query
            ->leftJoin('departments', 'departments.id', '=', 'karyawan.department_id')
            ->orderBy('departments.name')
            ->orderByRaw($this->jabatanOrderSql('karyawan.jabatan'))
            ->orderBy('karyawan.nama')
            ->select('karyawan.*')
            ->get();

        $jadwal = [];
        foreach ($karyawanList as $k) {
            $jadwal[$k->id] = [];
            foreach ($k->employeshifts as $es) {
                $jadwal[$k->id][$es->date->format('Y-m-d')] = $es->shift;
            }
        }

        $namaBulan        = $startDate->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.jadwal-shift-pdf', compact(
            'bulan', 'tahun', 'departemen', 'shifts',
            'karyawanList', 'jadwal', 'jumlahHari', 'startDate', 'namaBulan',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a3', 'landscape');

        $filename = 'jadwal-shift-' . strtolower(str_replace(' ', '-', $namaBulan)) . ($departemen ? '-' . str_replace(' ', '_', $departemen) : '') . '.pdf';

        return $pdf->download($filename);
    }

    public function jadwalKaryawan(Request $request)
    {
        $params = $request->all();
        $params['view'] = 'kalender';
        return redirect()->route('admin.laporan.jadwal-shift', $params);
    }

    public function buatJadwal(Request $request)
    {
        $shifts  = Shift::where('status', 'aktif')->orderBy('nama_shift')->get();
        $deptIds = $this->scopedDepartmentIds();
        $departments = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        return view('admin.laporan.buat-jadwal-shift', compact('shifts', 'departments', 'bulan', 'tahun'));
    }
}
