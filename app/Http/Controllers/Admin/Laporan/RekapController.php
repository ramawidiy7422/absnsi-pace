<?php

namespace App\Http\Controllers\Admin\Laporan;

use App\Exports\RekapBulananExport;
use App\Exports\RekapDepartemenExport;
use App\Exports\RekapKaryawanExport;
use App\Exports\TrendKehadiranExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Department;
use App\Models\HariLibur;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Leaverequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    // ── Helper: batch load absensi untuk kumpulan karyawan ────────────────
    private function batchAbsensi(Collection $ids, int $bulan, int $tahun): Collection
    {
        return Absensi::whereIn('karyawan_id', $ids)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('karyawan_id');
    }

    private function batchCuti(Collection $ids, string $startDate, string $endDate): Collection
    {
        return Leaverequest::whereIn('karyawan_id', $ids)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get()
            ->groupBy('karyawan_id');
    }

    private function batchIzin(Collection $ids, string $startDate, string $endDate): Collection
    {
        return Izin::whereIn('karyawan_id', $ids)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('karyawan_id');
    }

    // ─────────────────────────────────────────────────────────────────────

    public function rekapBulanan(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift','department']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $absensiMap = $this->batchAbsensi($karyawanList->pluck('id'), $bulan, $tahun);

        $rekap = $karyawanList->map(function ($k) use ($absensiMap) {
            $absensi = $absensiMap->get($k->id, collect());
            return [
                'karyawan'  => $k,
                'hadir'     => $absensi->where('status', 'hadir')->count(),
                'terlambat' => $absensi->where('status', 'terlambat')->count(),
                'izin'      => $absensi->where('status', 'izin')->count(),
                'sakit'     => $absensi->where('status', 'sakit')->count(),
                'alpha'     => $absensi->where('status', 'alpha')->count(),
                'cuti'      => $absensi->where('status', 'cuti')->count(),
                'total'     => $absensi->count(),
            ];
        });

        $departemenList = Department::orderBy('name')->get();

        return view('admin.laporan.rekap-bulanan', compact(
            'rekap', 'bulan', 'tahun', 'departemenList', 'department_id'
        ));
    }

    public function rekapPerDepartemen(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        // Supervisor hanya boleh lihat baris departemennya sendiri, bukan
        // perbandingan lintas departemen milik orang lain.
        $scopedDept = $this->scopedDepartmentId(null);
        $departemenList = Department::orderBy('name')
            ->when($scopedDept, fn($q) => $q->where('id', $scopedDept))
            ->get();

        // Batch: semua karyawan aktif sekaligus, grouped by department_id
        $semuaKaryawan = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->get(['id', 'department_id']);

        $allIds     = $semuaKaryawan->pluck('id');
        $absensiAll = $this->batchAbsensi($allIds, $bulan, $tahun);
        $byDept     = $semuaKaryawan->groupBy('department_id');

        $rekapDept = $departemenList->map(function ($dept) use ($byDept, $absensiAll) {
            $ids     = $byDept->get($dept->id, collect())->pluck('id');
            $absensi = $ids->flatMap(fn($id) => $absensiAll->get($id, collect()));

            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();

            return [
                'department_id'   => $dept->id,
                'departemen'      => $dept->name,
                'jumlah_karyawan' => $ids->count(),
                'hadir'           => $hadir,
                'terlambat'       => $terlambat,
                'izin'            => $absensi->where('status', 'izin')->count(),
                'sakit'           => $absensi->where('status', 'sakit')->count(),
                'alpha'           => $absensi->where('status', 'alpha')->count(),
                'total'           => $total,
                'persen_hadir'    => $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0,
            ];
        });

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return view('admin.laporan.rekap-departemen', compact(
            'rekapDept', 'bulan', 'tahun', 'departemenList', 'namaBulan'
        ));
    }

    public function rekapKaryawan(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $search        = $request->search;

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift','department']);
        if ($department_id) $query->where('department_id', $department_id);
        if ($search)        $query->where(fn($q) => $q->where('nama', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%"));
        $karyawanList = $query->orderBy('nama')->get();

        $absensiMap = $this->batchAbsensi($karyawanList->pluck('id'), $bulan, $tahun);

        $rekap = $karyawanList->map(function ($k) use ($absensiMap) {
            $absensi = $absensiMap->get($k->id, collect());
            $durasi  = $absensi->sum('durasi_menit');
            $jam     = intdiv((int) $durasi, 60);
            $menit   = (int) $durasi % 60;

            return [
                'karyawan'      => $k,
                'hadir'         => $absensi->where('status', 'hadir')->count(),
                'terlambat'     => $absensi->where('status', 'terlambat')->count(),
                'izin'          => $absensi->where('status', 'izin')->count(),
                'sakit'         => $absensi->where('status', 'sakit')->count(),
                'alpha'         => $absensi->where('status', 'alpha')->count(),
                'cuti'          => $absensi->where('status', 'cuti')->count(),
                'total'         => $absensi->count(),
                'durasi_menit'  => $durasi,
                'durasi_format' => $durasi > 0 ? "{$jam}j {$menit}m" : '-',
            ];
        });

        $departemenList = Department::orderBy('name')->get();
        $namaBulan      = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return view('admin.laporan.rekap-karyawan', compact(
            'rekap', 'bulan', 'tahun', 'departemenList', 'department_id', 'search', 'namaBulan'
        ));
    }

    public function rekapCutiIzin(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift','department']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $ids     = $karyawanList->pluck('id');
        $cutiMap = $this->batchCuti($ids, $startDate, $endDate);
        $izinMap = $this->batchIzin($ids, $startDate, $endDate);

        $rekap = $karyawanList->map(function ($k) use ($cutiMap, $izinMap) {
            $cuti = $cutiMap->get($k->id, collect());
            $izin = $izinMap->get($k->id, collect());
            return [
                'karyawan'       => $k,
                'cuti_disetujui' => $cuti->where('status','disetujui')->count(),
                'cuti_pending'   => $cuti->where('status','pending')->count(),
                'cuti_ditolak'   => $cuti->where('status','ditolak')->count(),
                'izin_sakit'     => $izin->where('jenis','sakit')->where('status','disetujui')->count(),
                'izin_izin'      => $izin->where('jenis','izin')->where('status','disetujui')->count(),
                'izin_alfa'      => $izin->where('jenis','alfa')->count(),
                'izin_pending'   => $izin->where('status','pending')->count(),
            ];
        });

        // Ringkasan per departemen — data sudah ada di cutiMap/izinMap, groupBy karyawan
        $ringkasanDept = $karyawanList
            ->groupBy(fn($k) => $k->department?->name ?? $k->departemen ?? 'Lainnya')
            ->map(function ($group) use ($cutiMap, $izinMap) {
                $cuti = $group->flatMap(fn($k) => $cutiMap->get($k->id, collect()));
                $izin = $group->flatMap(fn($k) => $izinMap->get($k->id, collect()));
                return [
                    'karyawan' => $group->count(),
                    'cuti'     => $cuti->where('status','disetujui')->count(),
                    'sakit'    => $izin->where('jenis','sakit')->where('status','disetujui')->count(),
                    'izin'     => $izin->where('jenis','izin')->where('status','disetujui')->count(),
                    'alfa'     => $izin->where('jenis','alfa')->count(),
                    'pending'  => $cuti->where('status','pending')->count() + $izin->where('status','pending')->count(),
                ];
            });

        $departemenList = Department::orderBy('name')->get();
        $namaBulan      = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return view('admin.laporan.rekap-cuti-izin', compact(
            'rekap', 'ringkasanDept', 'bulan', 'tahun', 'department_id', 'departemenList', 'namaBulan'
        ));
    }

    public function exportCutiIzinCsv(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $query = Karyawan::with('department')->whereIn('status', ['kontrak','tetap','magang','training']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $ids     = $karyawanList->pluck('id');
        $cutiMap = $this->batchCuti($ids, $startDate, $endDate);
        $izinMap = $this->batchIzin($ids, $startDate, $endDate);

        $rows[] = ['NIK', 'Nama', 'Departemen', 'Cuti Disetujui', 'Cuti Pending', 'Cuti Ditolak', 'Sakit', 'Izin', 'Alfa', 'Izin Pending'];

        foreach ($karyawanList as $k) {
            $cuti   = $cutiMap->get($k->id, collect());
            $izin   = $izinMap->get($k->id, collect());
            $rows[] = [
                $k->nik ?? '',
                $k->nama,
                $k->department?->name ?? '-',
                $cuti->where('status','disetujui')->count(),
                $cuti->where('status','pending')->count(),
                $cuti->where('status','ditolak')->count(),
                $izin->where('jenis','sakit')->where('status','disetujui')->count(),
                $izin->where('jenis','izin')->where('status','disetujui')->count(),
                $izin->where('jenis','alfa')->count(),
                $izin->where('status','pending')->count(),
            ];
        }

        $namaBulan = sprintf('%02d', $bulan);
        $filename  = "rekap-cuti-izin-{$namaBulan}-{$tahun}-" . now()->format('Ymd') . '.csv';

        return Response::stream(function () use ($rows) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF");
            foreach ($rows as $row) fputcsv($fp, $row);
            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportCutiIzinPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $departemen    = $department_id ? Department::find($department_id)?->name : null;

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $query = Karyawan::with('department')->whereIn('status', ['kontrak','tetap','magang','training']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $ids     = $karyawanList->pluck('id');
        $cutiMap = $this->batchCuti($ids, $startDate, $endDate);
        $izinMap = $this->batchIzin($ids, $startDate, $endDate);

        $rekap = $karyawanList->map(function ($k) use ($cutiMap, $izinMap) {
            $cuti = $cutiMap->get($k->id, collect());
            $izin = $izinMap->get($k->id, collect());
            return [
                'karyawan'       => $k,
                'cuti_disetujui' => $cuti->where('status','disetujui')->count(),
                'cuti_pending'   => $cuti->where('status','pending')->count(),
                'izin_sakit'     => $izin->where('jenis','sakit')->where('status','disetujui')->count(),
                'izin_izin'      => $izin->where('jenis','izin')->where('status','disetujui')->count(),
                'izin_alfa'      => $izin->where('jenis','alfa')->count(),
                'izin_pending'   => $izin->where('status','pending')->count(),
            ];
        })->filter(fn($r) => array_sum(\Illuminate\Support\Arr::except($r, ['karyawan'])) > 0);

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.rekap-cuti-izin-pdf', compact(
            'rekap', 'bulan', 'tahun', 'departemen', 'namaBulan',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("rekap-cuti-izin-{$bulan}-{$tahun}.pdf");
    }

    public function exportRekapPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $departemen    = $department_id ? Department::find($department_id)?->name : null;

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift', 'department']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $absensiMap = $this->batchAbsensi($karyawanList->pluck('id'), $bulan, $tahun);

        $rekap = $karyawanList->map(function ($k) use ($absensiMap) {
            $absensi   = $absensiMap->get($k->id, collect());
            $hadir     = $absensi->where('status','hadir')->count();
            $terlambat = $absensi->where('status','terlambat')->count();
            $total     = $absensi->count();
            return [
                'karyawan'  => $k,
                'hadir'     => $hadir,
                'terlambat' => $terlambat,
                'izin'      => $absensi->where('status','izin')->count(),
                'sakit'     => $absensi->where('status','sakit')->count(),
                'alpha'     => $absensi->where('status','alpha')->count(),
                'total'     => $total,
                'persen'    => $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0,
            ];
        });

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.rekap-pdf', compact(
            'rekap', 'namaBulan', 'departemen', 'bulan', 'tahun',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-' . str_replace(' ', '-', strtolower($namaBulan)) . ($departemen ? '-'.str_replace(' ','_',$departemen) : '') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift', 'department']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $absensiMap = $this->batchAbsensi($karyawanList->pluck('id'), $bulan, $tahun);
        $namaBulan  = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        $rows   = [];
        $rows[] = ['REKAP ABSENSI BULANAN - PT GLOBAL MULTI PHARMALAB'];
        $rows[] = ['Periode: ' . $namaBulan];
        $rows[] = [];
        $rows[] = ['No', 'ID Karyawan', 'Nama Karyawan', 'Departemen', 'Jabatan', 'Shift',
                   'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Total Hadir', 'Total Absen', '% Kehadiran', 'Durasi (Menit)'];

        $no = 1;
        foreach ($karyawanList as $k) {
            $absensi    = $absensiMap->get($k->id, collect());
            $hadir      = $absensi->where('status', 'hadir')->count();
            $terlambat  = $absensi->where('status', 'terlambat')->count();
            $totalHadir = $hadir + $terlambat;
            $totalAbsen = $absensi->count();

            $rows[] = [
                $no++,
                $k->nik,
                $k->nama,
                $k->department?->name ?? $k->departemen ?? '-',
                $k->jabatan ?? '-',
                $k->shift->nama_shift ?? '-',
                $hadir,
                $terlambat,
                $absensi->where('status', 'izin')->count(),
                $absensi->where('status', 'sakit')->count(),
                $absensi->where('status', 'alpha')->count(),
                $totalHadir,
                $totalAbsen,
                ($totalAbsen > 0 ? round($totalHadir / $totalAbsen * 100) : 0) . '%',
                $absensi->sum('durasi_menit'),
            ];
        }

        $filename = "rekap-absensi-{$bulan}-{$tahun}.csv";
        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportRekapDepartemenExcel(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $filename = "rekap-departemen-{$bulan}-{$tahun}-" . now()->format('Ymd') . '.xlsx';

        return Excel::download(new RekapDepartemenExport($bulan, $tahun, $this->scopedDepartmentId(null)), $filename);
    }

    public function exportRekapDepartemenCsv(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $scopedDept = $this->scopedDepartmentId(null);
        $departemenList = Department::orderBy('name')
            ->when($scopedDept, fn($q) => $q->where('id', $scopedDept))
            ->get();
        $semuaKaryawan  = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->get(['id', 'department_id']);

        $allIds     = $semuaKaryawan->pluck('id');
        $absensiAll = $this->batchAbsensi($allIds, $bulan, $tahun);
        $byDept     = $semuaKaryawan->groupBy('department_id');

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        $rows   = [];
        $rows[] = ['REKAP ABSENSI PER DEPARTEMEN - PT GLOBAL MULTI PHARMALAB'];
        $rows[] = ['Periode: ' . $namaBulan];
        $rows[] = [];
        $rows[] = ['Departemen', 'Karyawan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Total Record', '% Kehadiran'];

        foreach ($departemenList as $dept) {
            $ids     = $byDept->get($dept->id, collect())->pluck('id');
            $absensi = $ids->flatMap(fn($id) => $absensiAll->get($id, collect()));
            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();
            $persen    = $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0;

            $rows[] = [
                $dept->name,
                $ids->count(),
                $hadir,
                $terlambat,
                $absensi->where('status', 'izin')->count(),
                $absensi->where('status', 'sakit')->count(),
                $absensi->where('status', 'alpha')->count(),
                $total,
                $persen . '%',
            ];
        }

        $filename = "rekap-departemen-{$bulan}-{$tahun}-" . now()->format('Ymd') . '.csv';

        return Response::stream(function () use ($rows) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF");
            foreach ($rows as $row) fputcsv($fp, $row);
            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportRekapDepartemenPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $scopedDept = $this->scopedDepartmentId(null);
        $departemenList = Department::orderBy('name')
            ->when($scopedDept, fn($q) => $q->where('id', $scopedDept))
            ->get();
        $semuaKaryawan  = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->get(['id', 'department_id', 'nama']);

        $allIds     = $semuaKaryawan->pluck('id');
        $absensiAll = $this->batchAbsensi($allIds, $bulan, $tahun);
        $byDept     = $semuaKaryawan->groupBy('department_id');

        $rekapDept = $departemenList->map(function ($dept) use ($byDept, $absensiAll) {
            $ids     = $byDept->get($dept->id, collect())->pluck('id');
            $absensi = $ids->flatMap(fn($id) => $absensiAll->get($id, collect()));
            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();

            return [
                'departemen'      => $dept->name,
                'jumlah_karyawan' => $ids->count(),
                'hadir'           => $hadir,
                'terlambat'       => $terlambat,
                'izin'            => $absensi->where('status', 'izin')->count(),
                'sakit'           => $absensi->where('status', 'sakit')->count(),
                'alpha'           => $absensi->where('status', 'alpha')->count(),
                'total'           => $total,
                'persen_hadir'    => $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0,
            ];
        })->filter(fn($r) => $r['jumlah_karyawan'] > 0)->values();

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.laporan.rekap-departemen-pdf', compact(
            'rekapDept', 'bulan', 'tahun', 'namaBulan',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("rekap-departemen-{$bulan}-{$tahun}.pdf");
    }

    public function exportRekapKaryawanPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = (int) ($request->bulan ?? now()->month);
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->with(['shift','department']);
        if ($department_id) $query->where('department_id', $department_id);
        $karyawanList = $query->orderBy('nama')->get();

        $absensiMap = $this->batchAbsensi($karyawanList->pluck('id'), $bulan, $tahun);

        $rekap = $karyawanList->map(function ($k) use ($absensiMap) {
            $absensi   = $absensiMap->get($k->id, collect());
            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();
            return [
                'karyawan'   => $k,
                'hadir'      => $hadir,
                'terlambat'  => $terlambat,
                'izin'       => $absensi->where('status', 'izin')->count(),
                'sakit'      => $absensi->where('status', 'sakit')->count(),
                'alpha'      => $absensi->where('status', 'alpha')->count(),
                'cuti'       => $absensi->where('status', 'cuti')->count(),
                'total'      => $total,
                'pct_hadir'  => $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0,
            ];
        });

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');
        $dept             = $department_id ? Department::find($department_id) : null;

        $pdf = Pdf::loadView('admin.laporan.rekap-karyawan-pdf', compact(
            'rekap', 'namaBulan', 'dept', 'bulan', 'tahun',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("rekap-karyawan-{$bulan}-{$tahun}.pdf");
    }

    public function trendKehadiran(Request $request)
    {
        $tahun         = (int) ($request->tahun ?? now()->year);
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $bulanRange    = min(24, max(1, (int) ($request->bulan_range ?? 6))); // 3, 6, atau 12 bulan terakhir — dibatasi maksimal 24 bulan

        $scopedDept = $this->scopedDepartmentId(null);
        $departemenList = Department::orderBy('name')
            ->when($scopedDept, fn($q) => $q->where('id', $scopedDept))
            ->get();

        // Cache karyawan IDs sekali (sama untuk semua bulan)
        $karyawanQuery = Karyawan::whereIn('status', ['kontrak','tetap','magang','training']);
        if ($department_id) $karyawanQuery->where('department_id', $department_id);
        $karyawanIds = $karyawanQuery->pluck('id');

        // Ambil data per bulan untuk rentang yang dipilih
        $trendData = [];
        for ($i = $bulanRange - 1; $i >= 0; $i--) {
            $dt    = now()->startOfMonth()->subMonths($i);
            $bulan = (int) $dt->month;
            $th    = (int) $dt->year;

            $absensi = Absensi::whereIn('karyawan_id', $karyawanIds)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $th)
                ->get();

            $hadir     = $absensi->where('status', 'hadir')->count();
            $terlambat = $absensi->where('status', 'terlambat')->count();
            $total     = $absensi->count();

            $trendData[] = [
                'label'      => $dt->translatedFormat('M Y'),
                'bulan'      => $bulan,
                'tahun'      => $th,
                'hadir'      => $hadir,
                'terlambat'  => $terlambat,
                'izin'       => $absensi->where('status', 'izin')->count(),
                'sakit'      => $absensi->where('status', 'sakit')->count(),
                'alpha'      => $absensi->where('status', 'alpha')->count(),
                'total'      => $total,
                'persen'     => $total > 0 ? round(($hadir + $terlambat) / $total * 100) : 0,
            ];
        }

        // Trend per departemen untuk bulan terkini
        $bulanNow  = now()->month;
        $tahunNow  = now()->year;
        $bulanPrev = now()->subMonth()->month;
        $tahunPrev = now()->subMonth()->year;

        // Batch load semua karyawan department_id → ids
        $allKaryawanDept = Karyawan::whereIn('status', ['kontrak','tetap','magang','training'])
            ->whereIn('department_id', $departemenList->pluck('id'))
            ->get(['id','department_id'])
            ->groupBy('department_id');

        // Batch load semua absensi untuk 2 bulan sekaligus
        $allDeptKaryawanIds = $allKaryawanDept->flatten()->pluck('id');
        $absensiPool = Absensi::whereIn('karyawan_id', $allDeptKaryawanIds)
            ->where(function($q) use ($bulanNow, $tahunNow, $bulanPrev, $tahunPrev) {
                $q->where(fn($s) => $s->whereMonth('tanggal', $bulanNow)->whereYear('tanggal', $tahunNow))
                  ->orWhere(fn($s) => $s->whereMonth('tanggal', $bulanPrev)->whereYear('tanggal', $tahunPrev));
            })
            ->get(['karyawan_id','tanggal','status'])
            ->groupBy('karyawan_id');

        $trendDept = $departemenList->map(function ($dept) use ($allKaryawanDept, $absensiPool, $bulanNow, $tahunNow, $bulanPrev, $tahunPrev) {
            $deptKaryawan = $allKaryawanDept->get($dept->id);
            if (!$deptKaryawan || $deptKaryawan->isEmpty()) return null;

            $ids = $deptKaryawan->pluck('id');
            $absensiAll = collect();
            foreach ($ids as $kid) {
                if ($absensiPool->has($kid)) $absensiAll = $absensiAll->merge($absensiPool->get($kid));
            }

            $absensiNow  = $absensiAll->filter(fn($a) => (int)$a->tanggal->month === $bulanNow && (int)$a->tanggal->year === $tahunNow);
            $absensiPrev = $absensiAll->filter(fn($a) => (int)$a->tanggal->month === $bulanPrev && (int)$a->tanggal->year === $tahunPrev);

            $persenNow  = $absensiNow->count()  > 0 ? round($absensiNow->whereIn('status',['hadir','terlambat'])->count()  / $absensiNow->count()  * 100) : 0;
            $persenPrev = $absensiPrev->count() > 0 ? round($absensiPrev->whereIn('status',['hadir','terlambat'])->count() / $absensiPrev->count() * 100) : 0;

            return [
                'nama'       => $dept->name,
                'persen_now' => $persenNow,
                'persen_prev'=> $persenPrev,
                'delta'      => $persenNow - $persenPrev,
            ];
        })->filter()->values();

        return view('admin.laporan.trend-kehadiran', compact(
            'trendData', 'trendDept', 'tahun', 'department_id', 'departemenList', 'bulanRange'
        ));
    }

    public function exportTrendExcel(Request $request)
    {
        $bulanRange    = min(24, max(1, (int) ($request->bulan_range ?? 6)));
        $department_id = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);

        $filename = 'trend-kehadiran-' . $bulanRange . 'bulan-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new TrendKehadiranExport($bulanRange, $department_id), $filename);
    }

    public function slipPdf(Request $request, Karyawan $karyawan)
    {
        ini_set('memory_limit', '256M');
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        // Supervisor hanya boleh cetak slip karyawan di departemennya sendiri.
        $scopedDept = $this->scopedDepartmentId(null);
        if ($scopedDept && $karyawan->department_id !== $scopedDept) {
            abort(403, 'Anda tidak berwenang mengakses slip karyawan di luar departemen Anda.');
        }

        $karyawan->loadMissing('department');

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->with('shift')->orderBy('tanggal')->get();

        $hariKerja = 0;
        $start = Carbon::createFromDate($tahun, $bulan, 1);
        $end   = $start->copy()->endOfMonth();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY && !HariLibur::isLibur($d)) $hariKerja++;
        }

        $rekap = [
            'hadir'      => $absensi->where('status', 'hadir')->count(),
            'terlambat'  => $absensi->where('status', 'terlambat')->count(),
            'izin'       => $absensi->where('status', 'izin')->count(),
            'sakit'      => $absensi->where('status', 'sakit')->count(),
            'alpha'      => $absensi->where('status', 'alpha')->count(),
            'total'      => $absensi->count(),
            'hari_kerja' => $hariKerja,
        ];
        $rekap['persen'] = $hariKerja > 0
            ? round(($rekap['hadir'] + $rekap['terlambat']) / $hariKerja * 100)
            : 0;

        $namaBulan        = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');
        $slipFooter       = \App\Models\Pengaturan::get('slip_footer', '');

        $pdf = Pdf::loadView('admin.laporan.slip-pdf', compact(
            'karyawan', 'absensi', 'rekap', 'bulan', 'tahun', 'namaBulan',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'slipFooter'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('slip-absensi-' . $karyawan->nik . "-{$bulan}-{$tahun}.pdf");
    }

    public function exportRekapBulananExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $namaBulan = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F-Y');
        return Excel::download(
            new RekapBulananExport($bulan, $tahun, $this->scopedDepartmentId($request->departemen_id ? (int) $request->departemen_id : null)),
            "rekap-bulanan-{$namaBulan}.xlsx"
        );
    }

    public function exportRekapKaryawanExcel(Request $request)
    {
        $bulan      = $request->bulan ?? now()->month;
        $tahun      = $request->tahun ?? now()->year;
        $karyawanId = $request->karyawan_id;
        if (!$karyawanId) return back()->with('error', 'Pilih karyawan terlebih dahulu.');
        $karyawan  = \App\Models\Karyawan::findOrFail($karyawanId);

        // Supervisor hanya boleh export rekap karyawan di departemennya sendiri.
        $scopedDept = $this->scopedDepartmentId(null);
        if ($scopedDept && $karyawan->department_id !== $scopedDept) {
            abort(403, 'Anda tidak berwenang mengakses rekap karyawan di luar departemen Anda.');
        }

        $namaBulan = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F-Y');
        return Excel::download(
            new RekapKaryawanExport($karyawanId, $bulan, $tahun),
            "rekap-{$karyawan->nik}-{$namaBulan}.xlsx"
        );
    }

    public function rekapLembur(Request $request)
    {
        $bulan  = (int) ($request->bulan ?? now()->month);
        $tahun  = (int) ($request->tahun ?? now()->year);
        $deptId = $this->scopedDepartmentId($request->departemen_id ? (int) $request->departemen_id : null);
        $jenis  = $request->jenis; // spk / mandiri / auto
        $status = $request->status ?? 'approved';

        $query = \App\Models\Lemburan::with(['karyawan.department'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($status !== 'semua') $query->where('status', $status);
        if ($jenis) $query->where('jenis', $jenis);
        if ($deptId) $query->whereHas('karyawan', fn($q) => $q->where('department_id', $deptId));

        $lemburan = $query->orderBy('tanggal')->get();

        // Group per departemen
        $perDept = $lemburan->groupBy(fn($l) => $l->karyawan?->department?->name ?? 'Tanpa Dept')
            ->map(fn($items, $dept) => [
                'departemen'  => $dept,
                'total_karyawan' => $items->pluck('karyawan_id')->unique()->count(),
                'total_jam'   => round($items->sum('total_jam'), 2),
                'total_hari'  => $items->count(),
                'approved'    => $items->where('status','approved')->count(),
                'pending'     => $items->where('status','pending')->count(),
                'rejected'    => $items->where('status','rejected')->count(),
                'tarif'       => (float) \App\Models\Pengaturan::get('tarif_lembur_per_jam', 0),
                'items'       => $items,
            ])->sortByDesc('total_jam')->values();

        // Group per karyawan (ranking siapa paling banyak lembur)
        $perKaryawan = $lemburan->groupBy('karyawan_id')->map(function ($rows) {
            $k = $rows->first()->karyawan;
            return [
                'karyawan'   => $k,
                'total_jam'  => round($rows->sum('total_jam'), 2),
                'total_hari' => $rows->count(),
            ];
        })->sortByDesc('total_jam')->values();

        $departments = Department::orderBy('name')->get();
        $bulanList   = collect(range(1,12))->mapWithKeys(fn($m) => [$m => Carbon::create(null,$m)->translatedFormat('F')]);

        $ringkasan = [
            'total_jam'    => round($lemburan->sum('total_jam'), 2),
            'total_record' => $lemburan->count(),
            'total_karyawan' => $lemburan->pluck('karyawan_id')->unique()->count(),
            'approved'     => $lemburan->where('status','approved')->count(),
            'pending'      => $lemburan->where('status','pending')->count(),
            'total_biaya'  => round($lemburan->where('status','approved')->sum('total_jam') * (float) \App\Models\Pengaturan::get('tarif_lembur_per_jam', 0)),
        ];

        return view('admin.laporan.rekap-lembur', compact(
            'lemburan','perDept','perKaryawan','departments','bulanList',
            'bulan','tahun','deptId','jenis','status','ringkasan'
        ));
    }

    public function exportRekapLemburExcel(Request $request)
    {
        $bulan  = (int) ($request->bulan ?? now()->month);
        $tahun  = (int) ($request->tahun ?? now()->year);
        $deptId = $this->scopedDepartmentId($request->departemen_id ? (int) $request->departemen_id : null);
        $jenis  = $request->jenis;
        $status = $request->status ?? 'approved';

        $query = \App\Models\Lemburan::with(['karyawan.department'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($status !== 'semua') $query->where('status', $status);
        if ($jenis) $query->where('jenis', $jenis);
        if ($deptId) $query->whereHas('karyawan', fn($q) => $q->where('department_id', $deptId));

        $lemburan = $query->orderBy('tanggal')->get();
        $tarif    = (float) \App\Models\Pengaturan::get('tarif_lembur_per_jam', 0);
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F-Y');

        $rows = [['No','Tanggal','NIK','Nama','Departemen','Jenis','Jam Mulai','Jam Selesai','Total Jam','Estimasi Upah','Status']];
        foreach ($lemburan as $i => $l) {
            $rows[] = [
                $i + 1,
                $l->tanggal->format('d/m/Y'),
                $l->karyawan?->nik ?? '-',
                $l->karyawan?->nama ?? '-',
                $l->karyawan?->department?->name ?? '-',
                strtoupper($l->jenis),
                substr($l->jam_mulai, 0, 5),
                substr($l->jam_selesai, 0, 5),
                $l->total_jam,
                $tarif > 0 ? 'Rp ' . number_format($l->total_jam * $tarif, 0, ',', '.') : '-',
                strtoupper($l->status),
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            foreach ($rows as $row) fputcsv($out, $row, ';');
            fclose($out);
        }, "rekap-lembur-{$namaBulan}.csv", ['Content-Type' => 'text/csv']);
    }

    private function buildRekapPotonganTunjangan(Request $request): array
    {
        $bulan          = (int) ($request->bulan ?? now()->month);
        $tahun          = (int) ($request->tahun ?? now()->year);
        $dept           = $this->scopedDepartmentId($request->department_id ? (int) $request->department_id : null);
        $nilaiAlpha     = (float) ($request->nilai_alpha     ?? 0);
        $nilaiTerlambat = (float) ($request->nilai_terlambat ?? 0);
        $nilaiHadir     = (float) ($request->nilai_hadir     ?? 0);
        $nilaiLembur    = (float) ($request->nilai_lembur    ?? 0);

        $karyawanList = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','harian'])
            ->when($dept, fn($q) => $q->where('department_id', $dept))
            ->with('department')
            ->orderBy('nama')->get();

        $ids       = $karyawanList->pluck('id');
        $startDate = "{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $absensiMap = Absensi::whereIn('karyawan_id', $ids)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->get()->groupBy('karyawan_id');

        $lemburMap = \App\Models\Lemburan::whereIn('karyawan_id', $ids)
            ->where('status', 'disetujui')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()->groupBy('karyawan_id');

        $rekap = $karyawanList->map(function ($k) use ($absensiMap, $lemburMap, $nilaiAlpha, $nilaiTerlambat, $nilaiHadir, $nilaiLembur) {
            $absensi  = $absensiMap->get($k->id, collect());
            $lemburan = $lemburMap->get($k->id, collect());

            $jmlHadir     = $absensi->whereIn('status', ['hadir','terlambat'])->count();
            $jmlTerlambat = $absensi->where('status', 'terlambat')->count();
            $jmlAlpha     = $absensi->where('status', 'alpha')->count();
            $jmlLembur    = $lemburan->sum('total_jam');

            $potonganAlpha     = $jmlAlpha     * $nilaiAlpha;
            $potonganTerlambat = $jmlTerlambat * $nilaiTerlambat;
            $tunjanganHadir    = $jmlHadir     * $nilaiHadir;
            $tunjanganLembur   = $jmlLembur    * $nilaiLembur;

            $totalPotongan  = $potonganAlpha + $potonganTerlambat;
            $totalTunjangan = $tunjanganHadir + $tunjanganLembur;

            return [
                'karyawan'           => $k,
                'jml_hadir'          => $jmlHadir,
                'jml_terlambat'      => $jmlTerlambat,
                'jml_alpha'          => $jmlAlpha,
                'jml_lembur_jam'     => round($jmlLembur, 2),
                'potongan_alpha'     => $potonganAlpha,
                'potongan_terlambat' => $potonganTerlambat,
                'tunjangan_hadir'    => $tunjanganHadir,
                'tunjangan_lembur'   => $tunjanganLembur,
                'total_potongan'     => $totalPotongan,
                'total_tunjangan'    => $totalTunjangan,
                'netto'              => $totalTunjangan - $totalPotongan,
            ];
        });

        return compact('rekap', 'bulan', 'tahun', 'dept', 'nilaiAlpha', 'nilaiTerlambat', 'nilaiHadir', 'nilaiLembur');
    }

    public function rekapPotonganTunjangan(Request $request)
    {
        $data           = $this->buildRekapPotonganTunjangan($request);
        $departemenList = Department::orderBy('name')->get();

        return view('admin.laporan.rekap-potongan-tunjangan', array_merge($data, compact('departemenList')));
    }

    public function exportPotonganTunjanganCsv(Request $request)
    {
        $data  = $this->buildRekapPotonganTunjangan($request);
        $rekap = $data['rekap'];

        $bulan     = $request->bulan ?? now()->month;
        $tahun     = $request->tahun ?? now()->year;
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F-Y');

        $rows = [];
        $rows[] = ['NIK', 'Nama', 'Departemen', 'Hadir', 'Terlambat', 'Alpha', 'Lembur (Jam)',
                   'Potongan Alpha', 'Potongan Terlambat', 'Total Potongan',
                   'Tunjangan Hadir', 'Tunjangan Lembur', 'Total Tunjangan', 'Netto'];

        foreach ($rekap as $r) {
            $rows[] = [
                $r['karyawan']->nik,
                $r['karyawan']->nama,
                $r['karyawan']->department?->name ?? '-',
                $r['jml_hadir'],
                $r['jml_terlambat'],
                $r['jml_alpha'],
                $r['jml_lembur_jam'],
                $r['potongan_alpha'],
                $r['potongan_terlambat'],
                $r['total_potongan'],
                $r['tunjangan_hadir'],
                $r['tunjangan_lembur'],
                $r['total_tunjangan'],
                $r['netto'],
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($rows as $row) fputcsv($out, $row, ';');
            fclose($out);
        }, "rekap-potongan-tunjangan-{$namaBulan}.csv", ['Content-Type' => 'text/csv']);
    }
}
