<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CutiSaldo;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CutiSaldoController extends Controller
{
    public function index(Request $request)
    {
        $tahun         = $request->tahun ?? now()->year;
        $search        = $request->search;
        $department_id = $request->department_id;

        $deptIds = $this->scopedDepartmentIds();

        $karyawan = Karyawan::with('department')
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('nama', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")
            ))
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->when($deptIds === null && $department_id, fn($q) => $q->where('department_id', $department_id))
            ->orderBy('nama')
            ->get();

        $jatahDefault = (int) (Pengaturan::get('jatah_cuti_tahunan') ?? 12);
        $saldoMap = CutiSaldo::where('tahun', $tahun)
            ->whereIn('karyawan_id', $karyawan->pluck('id'))
            ->get()
            ->keyBy('karyawan_id');

        $deptList = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.cuti-saldo.index', compact('karyawan', 'saldoMap', 'tahun', 'jatahDefault', 'deptList', 'department_id'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tahun' => 'required|digits:4|integer',
            'jatah' => 'required|integer|min:0|max:365',
            'terpakai' => 'required|integer|min:0',
        ]);

        $saldo = CutiSaldo::updateOrCreate(
            ['karyawan_id' => $karyawan->id, 'tahun' => $request->tahun],
            ['jatah' => $request->jatah, 'terpakai' => $request->terpakai]
        );

        AuditLog::catat('update', "Update saldo cuti {$karyawan->nama} tahun {$request->tahun}: jatah={$request->jatah}, terpakai={$request->terpakai}", $saldo);

        return back()->with('success', "Saldo cuti {$karyawan->nama} berhasil diperbarui.");
    }

    public function exportCsv(Request $request)
    {
        $tahun         = $request->tahun ?? now()->year;
        $search        = $request->search;
        $department_id = $request->department_id;

        $deptIds = $this->scopedDepartmentIds();

        $karyawan = Karyawan::with('department')
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('nama', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")
            ))
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->when($deptIds === null && $department_id, fn($q) => $q->where('department_id', $department_id))
            ->orderBy('nama')
            ->get();

        $jatahDefault = (int) (Pengaturan::get('jatah_cuti_tahunan') ?? 12);
        $saldoMap = CutiSaldo::where('tahun', $tahun)
            ->whereIn('karyawan_id', $karyawan->pluck('id'))
            ->get()
            ->keyBy('karyawan_id');

        $callback = function () use ($karyawan, $saldoMap, $jatahDefault, $tahun) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['NIK', 'Nama', 'Departemen', 'Status', 'Tahun', 'Jatah Cuti', 'Terpakai', 'Sisa']);
            foreach ($karyawan as $k) {
                $saldo   = $saldoMap[$k->id] ?? null;
                $jatah   = $saldo?->jatah   ?? $jatahDefault;
                $terpakai = $saldo?->terpakai ?? 0;
                $sisa    = max(0, $jatah - $terpakai);
                fputcsv($out, [
                    $k->nik,
                    $k->nama,
                    $k->department?->name ?? $k->departemen ?? '-',
                    ucfirst($k->status),
                    $tahun,
                    $jatah,
                    $terpakai,
                    $sisa,
                ]);
            }
            fclose($out);
        };

        $filename = "saldo_cuti_{$tahun}_" . now()->format('Ymd') . '.csv';
        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $tahun         = $request->tahun ?? now()->year;
        $search        = $request->search;
        $department_id = $request->department_id;

        $deptIds = $this->scopedDepartmentIds();

        $karyawan = Karyawan::with('department')
            ->whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('nama', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")
            ))
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->when($deptIds === null && $department_id, fn($q) => $q->where('department_id', $department_id))
            ->orderBy('nama')
            ->get();

        $jatahDefault = (int) (Pengaturan::get('jatah_cuti_tahunan') ?? 12);
        $saldoMap = CutiSaldo::where('tahun', $tahun)
            ->whereIn('karyawan_id', $karyawan->pluck('id'))
            ->get()
            ->keyBy('karyawan_id');

        $namaPerusahaan   = Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = Pengaturan::get('no_telp_perusahaan', '');

        $labels = ["Tahun: {$tahun}"];
        if ($department_id) {
            $dept = Department::find($department_id);
            if ($dept) $labels[] = 'Dept: ' . $dept->name;
        }
        if ($search) $labels[] = 'Cari: ' . $search;
        $filterLabel = implode(' | ', $labels);

        $pdf = Pdf::loadView('admin.cuti-saldo.export-pdf', compact(
            'karyawan', 'saldoMap', 'jatahDefault', 'tahun',
            'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'filterLabel'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("saldo-cuti-{$tahun}-" . now()->format('Ymd_His') . '.pdf');
    }

    public function bulkSetJatah(Request $request)
    {
        $request->validate([
            'tahun'         => 'required|digits:4|integer',
            'jatah'         => 'required|integer|min:0|max:365',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $deptIds = $this->scopedDepartmentIds();

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian']);
        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds ?: [0]);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        $ids = $query->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            CutiSaldo::updateOrCreate(
                ['karyawan_id' => $id, 'tahun' => $request->tahun],
                ['jatah' => $request->jatah]
            );
            $count++;
        }

        $deptName = $request->department_id ? (Department::find($request->department_id)?->name ?? '') : null;
        $dept     = $deptName ? "departemen {$deptName}" : "semua karyawan";
        AuditLog::catat('update', "Bulk set jatah cuti {$dept} tahun {$request->tahun}: {$request->jatah} hari ({$count} karyawan)");

        return back()->with('success', "Jatah cuti {$request->jatah} hari berhasil diset untuk {$count} karyawan ({$dept}) tahun {$request->tahun}.");
    }
}
