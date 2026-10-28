<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Lemburan;
use App\Models\AuditLog;
use App\Models\User;
use App\Helpers\NotifHelper;
use App\Notifications\LemburanPengajuanNotification;
use App\Notifications\LemburanStatusNotification;
use Illuminate\Http\Request;
use App\Exports\LemburanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LemburanController extends Controller
{
    public function index(Request $request)
    {
        $deptIds = $this->scopedDepartmentIds();
        $scopeKaryawan = fn($q) => $deptIds !== null ? $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])) : $q;

        $query = Lemburan::with(['karyawan.department', 'approver']);
        $scopeKaryawan($query);

        if ($request->search) {
            $query->whereHas('karyawan', fn($q) =>
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%")
            );
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $lemburan = $query->latest('tanggal')->paginate(20)->withQueryString();
        $totalPending  = $scopeKaryawan(Lemburan::where('status', 'pending'))->count();
        $totalApproved = $scopeKaryawan(Lemburan::where('status', 'approved')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year))
            ->count();
        $totalJamBulanIni = $scopeKaryawan(Lemburan::where('status', 'approved')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year))
            ->sum('total_jam');

        return view('admin.lemburan.index', compact(
            'lemburan', 'totalPending', 'totalApproved', 'totalJamBulanIni'
        ));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i',
            'alasan'     => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Cross-midnight safe: jika jam_selesai < jam_mulai, tambah 1 hari
        $mulai   = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $selesai = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
        if ($selesai->lte($mulai)) $selesai->addDay();
        $totalJam = round($mulai->diffInMinutes($selesai) / 60, 2);

        if ($totalJam <= 0 || $totalJam > 12) {
            return back()->withErrors(['jam_selesai' => 'Durasi lembur harus antara 0–12 jam.'])->withInput();
        }

        $lemburan = Lemburan::create([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai . ':00',
            'jam_selesai' => $request->jam_selesai . ':00',
            'total_jam'   => $totalJam,
            'alasan'      => $request->alasan,
            'keterangan'  => $request->keterangan,
            'status'      => 'pending',
        ]);

        $tglLembur = $lemburan->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('create', "Tambah lembur {$karyawan->nama} ({$tglLembur}, {$lemburan->total_jam} jam)", $lemburan);

        // Notifikasi ke admin & HRD
        User::whereIn('role', ['superadmin','admin','hrd'])->get()
            ->each(fn($u) => $u->notify(new LemburanPengajuanNotification($lemburan)));

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return back()->withFragment('tab-lemburan')
                     ->with('success', 'Pengajuan lembur berhasil ditambahkan.');
    }

    public function update(Request $request, Lemburan $lemburan)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i',
            'alasan'     => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $mulai   = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $selesai = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
        if ($selesai->lte($mulai)) $selesai->addDay();
        $totalJam = round($mulai->diffInMinutes($selesai) / 60, 2);

        if ($totalJam <= 0 || $totalJam > 12) {
            return back()->withErrors(['jam_selesai' => 'Durasi lembur harus antara 0–12 jam.'])->withInput();
        }

        $lemburan->update([
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai . ':00',
            'jam_selesai' => $request->jam_selesai . ':00',
            'total_jam'   => $totalJam,
            'alasan'      => $request->alasan,
            'keterangan'  => $request->keterangan,
            'status'      => 'pending', // reset ke pending setelah edit
        ]);

        return back()->withFragment('tab-lemburan')
                     ->with('success', 'Data lembur berhasil diperbarui.');
    }

    public function approve(Lemburan $lemburan)
    {
        if (Gate::denies('approve-lemburan', $lemburan)) {
            return back()->with('error', 'Anda bukan approver untuk lembur ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($lemburan) {
            $locked = Lemburan::where('id', $lemburan->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $locked->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $lemburan->setAttribute('status', 'approved');
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Lembur ini sudah diproses sebelumnya.');
        }

        $tglLembur = $lemburan->tanggal?->format('d/m/Y') ?? '-';
        AuditLog::catat('approve', "Approve lembur {$lemburan->karyawan?->nama} tgl {$tglLembur}", $lemburan);
        if ($lemburan->karyawan?->user) {
            $lemburan->karyawan->user->notify(new LemburanStatusNotification($lemburan));
        }

        ApprovalSayaController::invalidasiCache($lemburan->karyawan?->department);

        return back()->with('success', 'Lembur disetujui.');
    }

    public function reject(Request $request, Lemburan $lemburan)
    {
        if (Gate::denies('approve-lemburan', $lemburan)) {
            return back()->with('error', 'Anda bukan approver untuk lembur ini.');
        }

        $request->validate(['alasan_tolak' => 'nullable|string|max:500']);

        $sudahDiproses = DB::transaction(function () use ($lemburan, $request) {
            $locked = Lemburan::where('id', $lemburan->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $locked->update([
                'status'       => 'rejected',
                'approved_by'  => auth()->id(),
                'approved_at'  => now(),
                'alasan_tolak' => $request->alasan_tolak,
            ]);
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Lembur ini sudah diproses sebelumnya.');
        }

        $tglLembur = $lemburan->tanggal?->format('d/m/Y') ?? '-';
        AuditLog::catat('reject', "Tolak lembur {$lemburan->karyawan?->nama} tgl {$tglLembur}", $lemburan);
        if ($lemburan->karyawan?->user) {
            $lemburan->karyawan->user->notify(new LemburanStatusNotification($lemburan));
        }

        ApprovalSayaController::invalidasiCache($lemburan->karyawan?->department);

        return back()->with('success', 'Lembur ditolak.');
    }

    /**
     * Halaman ini digabung ke admin.laporan.rekap-lembur (tab "Per Karyawan").
     * Redirect supaya link/bookmark lama tetap berfungsi.
     */
    public function rekap(Request $request)
    {
        return redirect()->route('admin.laporan.rekap-lembur', [
            'bulan'         => $request->bulan,
            'tahun'         => $request->tahun,
            'departemen_id' => $request->department_id,
        ] + ['_tab' => 'tab-per-karyawan']);
    }

    public function rekapExportCsv(Request $request)
    {
        $bulan         = $request->bulan ?? now()->month;
        $tahun         = $request->tahun ?? now()->year;
        $department_id = $request->department_id;

        $query = Lemburan::with('karyawan.department')
            ->where('status', 'approved')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal');

        if ($department_id) {
            $query->whereHas('karyawan', fn($q) => $q->where('department_id', $department_id));
        }

        $data = $query->get();
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F_Y');

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=rekap_lembur_{$namaBulan}.csv"];
        $callback = function() use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID Karyawan','Nama','Departemen','Tanggal','Jam Mulai','Jam Selesai','Total Jam','Alasan']);
            foreach ($data as $lb) {
                fputcsv($out, [
                    $lb->karyawan?->nik ?? '-',
                    $lb->karyawan?->nama ?? '-',
                    $lb->karyawan?->department?->name ?? $lb->karyawan?->departemen ?? '-',
                    $lb->tanggal->format('d/m/Y'),
                    substr($lb->jam_mulai, 0, 5),
                    substr($lb->jam_selesai, 0, 5),
                    $lb->total_jam,
                    $lb->alasan,
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $lemburanList = Lemburan::with('karyawan.user', 'karyawan.department')
            ->whereIn('id', $ids)
            ->where('status', 'pending')
            ->get();

        $count = 0;
        $skipped = 0;
        foreach ($lemburanList as $lemburan) {
            if (Gate::denies('approve-lemburan', $lemburan)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($lemburan) {
                $locked = Lemburan::where('id', $lemburan->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($lemburan->karyawan?->user) {
                $lemburan->karyawan->user->notify(new LemburanStatusNotification($lemburan));
            }
            $count++;
        }

        AuditLog::catat('approve', "Bulk approve {$count} pengajuan lembur");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} pengajuan lembur berhasil disetujui.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda.";
        return back()->with('success', $pesan);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $lemburanList = Lemburan::with('karyawan.user', 'karyawan.department')
            ->whereIn('id', $ids)
            ->where('status', 'pending')
            ->get();

        $count = 0;
        $skipped = 0;
        foreach ($lemburanList as $lemburan) {
            if (Gate::denies('approve-lemburan', $lemburan)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($lemburan) {
                $locked = Lemburan::where('id', $lemburan->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($lemburan->karyawan?->user) {
                $lemburan->karyawan->user->notify(new LemburanStatusNotification($lemburan));
            }
            $count++;
        }

        AuditLog::catat('reject', "Bulk reject {$count} pengajuan lembur");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} pengajuan lembur berhasil ditolak.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda.";
        return back()->with('success', $pesan);
    }

    public function rekapExportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $bulan         = $request->bulan ?? now()->month;
        $tahun         = $request->tahun ?? now()->year;
        $department_id = $request->department_id;

        $query = Lemburan::with('karyawan.department')
            ->where('status', 'approved')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($department_id) {
            $query->whereHas('karyawan', fn($q) => $q->where('department_id', $department_id));
        }

        $lemburan = $query->get();

        $rekapKaryawan = $lemburan->groupBy('karyawan_id')->map(function ($rows) {
            $k = $rows->first()->karyawan;
            return [
                'karyawan'   => $k,
                'total_jam'  => $rows->sum('total_jam'),
                'total_hari' => $rows->count(),
                'detail'     => $rows->sortBy('tanggal'),
            ];
        })->sortByDesc('total_jam');

        $rekapDept = $lemburan->groupBy(fn($l) => $l->karyawan?->department?->name ?? $l->karyawan?->departemen ?? '-')
            ->map(fn($rows) => [
                'total_jam'  => $rows->sum('total_jam'),
                'total_kary' => $rows->pluck('karyawan_id')->unique()->count(),
            ])->sortByDesc('total_jam');

        $totalJam   = $lemburan->sum('total_jam');
        $totalKary  = $lemburan->pluck('karyawan_id')->unique()->count();
        $namaBulan      = Carbon::createFromDate($tahun, $bulan)->translatedFormat('F Y');
        $dept             = $department_id ? Department::find($department_id) : null;
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $pdf = Pdf::loadView('admin.lemburan.rekap-pdf', compact(
            'rekapKaryawan', 'rekapDept', 'totalJam', 'totalKary',
            'namaBulan', 'dept', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("rekap-lembur-{$bulan}-{$tahun}.pdf");
    }

    public function rekapExportExcel(Request $request)
    {
        // Wajibkan bulan/tahun (default bulan berjalan) — tanpa ini export bisa
        // menarik SELURUH riwayat lembur sekaligus, berisiko timeout/memory untuk
        // data bertahun-tahun.
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        return Excel::download(
            new LemburanExport($bulan, $tahun, $request->status),
            'rekap-lembur-' . now()->format('Y-m') . '.xlsx'
        );
    }

    public function destroy(Lemburan $lemburan)
    {
        $lemburan->load('karyawan');
        AuditLog::catat('delete', "Hapus lembur karyawan ID {$lemburan->karyawan_id} tgl {$lemburan->tanggal->format('d M Y')}", $lemburan);
        $lemburan->delete();

        ApprovalSayaController::invalidasiCache($lemburan->karyawan?->department);

        return back()->withFragment('tab-lemburan')
                     ->with('success', 'Data lembur dihapus.');
    }
}
