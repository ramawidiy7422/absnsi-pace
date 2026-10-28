<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Warninglettter;
use App\Models\AuditLog;
use App\Helpers\NotifHelper;
use App\Notifications\SuratPeringatanNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WarningletterController extends Controller
{
    public function index(Request $request)
    {
        $type          = $request->type;
        $status        = $request->status;
        $department_id = $request->department_id;
        $tahun         = $request->tahun ?? now()->year;

        $spList = Warninglettter::with('karyawan.department')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->when($tahun, fn($q) => $q->whereYear('date', $tahun))
            ->orderByDesc('date')
            ->paginate(25)
            ->withQueryString();

        $deptList = Department::orderBy('name')->get();

        $stats = [
            'total'  => Warninglettter::whereYear('date', $tahun)->count(),
            'sp1'    => Warninglettter::where('type','SP1')->whereYear('date', $tahun)->count(),
            'sp2'    => Warninglettter::where('type','SP2')->whereYear('date', $tahun)->count(),
            'sp3'    => Warninglettter::where('type','SP3')->whereYear('date', $tahun)->count(),
            'aktif'  => Warninglettter::where('status','aktif')->whereYear('date', $tahun)->count(),
        ];

        return view('admin.warninglettter.index', compact('spList','deptList','type','status','department_id','tahun','stats'));
    }

    public function exportCsv(Request $request)
    {
        $tahun         = $request->tahun ?? now()->year;
        $type          = $request->type;
        $status        = $request->status;
        $department_id = $request->department_id;

        $data = Warninglettter::with('karyawan.department')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $department_id)))
            ->whereYear('date', $tahun)
            ->orderByDesc('date')
            ->get();

        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['NIK', 'Nama Karyawan', 'Departemen', 'Tipe SP', 'Status', 'Tanggal', 'Berlaku Hingga', 'Deskripsi']);
            foreach ($data as $sp) {
                fputcsv($out, [
                    $sp->karyawan?->nik ?? '-',
                    $sp->karyawan?->nama ?? '-',
                    $sp->karyawan?->department?->name ?? $sp->karyawan?->departemen ?? '-',
                    $sp->type ?? '-',
                    ucfirst($sp->status ?? '-'),
                    $sp->date?->format('d/m/Y') ?? '-',
                    $sp->valid_until?->format('d/m/Y') ?? '-',
                    $sp->description ?? '-',
                ]);
            }
            fclose($out);
        };

        $filename = "surat_peringatan_{$tahun}_" . now()->format('Ymd') . '.csv';
        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Karyawan $karyawan)
    {
        ini_set('memory_limit', '256M');
        $karyawan->load(['department', 'warningletters']);
        $spList         = $karyawan->warningletters->sortByDesc('date');
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.warninglettter.pdf', compact(
            'karyawan', 'spList', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))
            ->setPaper('a4', 'portrait');

        return $pdf->download("surat_peringatan_{$karyawan->nik}.pdf");
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'type'        => 'required|in:SP1,SP2,SP3',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:500',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'status'      => 'required|in:aktif,selesai',
        ]);

        $sp = $karyawan->warningletters()->create(
            $request->only('type', 'date', 'description', 'valid_until', 'status')
        );
        AuditLog::catat('create', "Tambah {$sp->type} untuk karyawan {$karyawan->nama}", $sp);
        NotifHelper::keKaryawan($karyawan->id, new SuratPeringatanNotification($sp));

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Surat peringatan berhasil ditambahkan!')
                         ->withFragment('tab-warninglettter');
    }

    public function update(Request $request, Warninglettter $warninglettter)
    {
        $request->validate([
            'type'        => 'required|in:SP1,SP2,SP3',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:500',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'status'      => 'required|in:aktif,selesai',
        ]);

        $warninglettter->update(
            $request->only('type', 'date', 'description', 'valid_until', 'status')
        );

        return redirect()->route('admin.karyawan.show', $warninglettter->karyawan_id)
                         ->with('success', 'Surat peringatan berhasil diupdate!')
                         ->withFragment('tab-warninglettter');
    }

    public function destroy(Warninglettter $warninglettter)
    {
        $karyawanId = $warninglettter->karyawan_id;
        AuditLog::catat('delete', "Hapus {$warninglettter->type} karyawan ID {$karyawanId}", $warninglettter);
        $warninglettter->delete();

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', 'Surat peringatan berhasil dihapus!')
                         ->withFragment('tab-warninglettter');
    }
}
