<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Absensi;
use App\Models\Department;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\HariLibur;
use App\Models\User;
use App\Notifications\IzinPengajuanNotification;
use App\Notifications\IzinStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Exports\IzinExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $deptIds = $this->scopedDepartmentIds();

        $query = Izin::with('karyawan.department', 'approver')
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->jenis,         fn($q) => $q->where('jenis', $request->jenis))
            ->when($deptIds !== null,       fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($deptIds === null && $request->department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $request->department_id)))
            ->when($request->search,        fn($q) => $q->whereHas('karyawan', fn($k) =>
                $k->where('nama', 'like', "%{$request->search}%")->orWhere('nik', 'like', "%{$request->search}%")
            ))
            ->when($request->bulan,         fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->tahun,         fn($q) => $q->whereYear('tanggal', $request->tahun));

        $izinList   = $query->latest('tanggal')->paginate(20)->withQueryString();
        $departemen = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.izin.index', compact('izinList', 'departemen'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'jenis'      => 'required|in:sakit,izin,alfa',
            'keterangan' => 'nullable|string|max:255',
            'lampiran'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Cek duplikasi: tidak boleh ada izin aktif di tanggal yang sama
        $duplikat = Izin::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $request->tanggal)
            ->whereIn('status', ['pending', 'disetujui'])
            ->first();
        if ($duplikat) {
            return back()->withInput()->with('error', "Sudah ada pengajuan {$duplikat->jenis} di tanggal tersebut (status: {$duplikat->status}).");
        }

        // Cek bentrok lintas jenis pengajuan (cuti/FMK) di tanggal yang sama
        $tgl = \Carbon\Carbon::parse($request->tanggal);
        $bentrok = $this->cekBentrokPengajuan($karyawan->id, $tgl, $tgl, kecualiIzinId: null);
        if ($bentrok) {
            return back()->withInput()->with('error', "Karyawan sudah punya pengajuan lain yang aktif di tanggal tersebut: {$bentrok}.");
        }

        $data = $request->only(['tanggal', 'jenis', 'keterangan']);
        $data['karyawan_id'] = $karyawan->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('izin', 'public');
        }

        $izin = Izin::create($data);
        $tglIzin = $izin->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('create', "Tambah izin {$izin->jenis} untuk {$karyawan->nama} ({$tglIzin})", $izin);

        // Notifikasi ke admin & HRD
        User::whereIn('role', ['superadmin','admin','hrd'])->get()
            ->each(fn($u) => $u->notify(new IzinPengajuanNotification($izin)));

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Izin berhasil ditambahkan!')
                         ->withFragment('tab-izin');
    }

    public function approve(Request $request, Izin $izin)
    {
        if (Gate::denies('approve-izin', $izin)) {
            return back()->with('error', 'Anda bukan approver untuk izin ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izin, $request) {
            $locked = Izin::where('id', $izin->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $locked->update([
                'status'           => 'disetujui',
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
                'catatan_approver' => $request->catatan_approver,
            ]);
            // Buat record absensi otomatis jika belum ada (di dalam transaction+lock
            // yang sama supaya tidak race dengan kiosk yang absen di saat bersamaan)
            $this->buatAbsensiOtomatis($locked);
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Izin ini sudah diproses sebelumnya.');
        }

        $tglIzin = $izin->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('approve', "Izin {$izin->jenis} {$izin->karyawan?->nama} ({$tglIzin}) disetujui", $izin);

        if ($izin->karyawan?->user) {
            $izin->karyawan->user->notify(new IzinStatusNotification($izin));
        }

        ApprovalSayaController::invalidasiCache($izin->karyawan?->department);

        return back()->with('success', 'Izin disetujui dan dicatat di absensi.');
    }

    public function reject(Request $request, Izin $izin)
    {
        if (Gate::denies('approve-izin', $izin)) {
            return back()->with('error', 'Anda bukan approver untuk izin ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izin, $request) {
            $locked = Izin::where('id', $izin->id)->lockForUpdate()->first();
            if ($locked->status === 'ditolak') {
                return true;
            }
            // Jika sebelumnya disetujui, hapus absensi otomatis
            if ($locked->status === 'disetujui') {
                $this->hapusAbsensiOtomatis($locked);
            }
            $locked->update([
                'status'           => 'ditolak',
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
                'catatan_approver' => $request->catatan_approver,
            ]);
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Izin ini sudah ditolak sebelumnya.');
        }

        $tglIzin = $izin->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('reject', "Izin {$izin->jenis} {$izin->karyawan?->nama} ({$tglIzin}) ditolak", $izin);

        if ($izin->karyawan?->user) {
            $izin->karyawan->user->notify(new IzinStatusNotification($izin));
        }

        ApprovalSayaController::invalidasiCache($izin->karyawan?->department);

        return back()->with('success', 'Izin ditolak.');
    }

    private function buatAbsensiOtomatis(Izin $izin): void
    {
        $tanggal = $izin->tanggal;

        // Skip hari libur nasional
        if (HariLibur::isLibur($tanggal)) return;

        $karyawan = $izin->karyawan ?? Karyawan::find($izin->karyawan_id);

        $existing = Absensi::where('karyawan_id', $izin->karyawan_id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (!$existing) {
            $status = match($izin->jenis) {
                'sakit' => 'sakit',
                default => 'izin',
            };
            Absensi::create([
                'karyawan_id' => $izin->karyawan_id,
                'shift_id'    => $karyawan->resolveShift($tanggal)?->id ?? $karyawan->shift_id,
                'tanggal'     => $tanggal->toDateString(),
                'jam_masuk'   => null,
                'jam_keluar'  => null,
                'status'      => $status,
                'metode'      => 'sistem',
                'keterangan'  => ucfirst($izin->jenis) . ' (disetujui)' . ($izin->keterangan ? ': ' . $izin->keterangan : ''),
                'izin_id'     => $izin->id,
            ]);
        }
    }

    private function hapusAbsensiOtomatis(Izin $izin): void
    {
        Absensi::where('izin_id', $izin->id)->delete();
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $izinList = Izin::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($izinList as $izin) {
            if (Gate::denies('approve-izin', $izin)) {
                $skipped++;
                continue;
            }
            $izin->update([
                'status'      => 'disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            $this->buatAbsensiOtomatis($izin);
            if ($izin->karyawan->user) {
                $izin->karyawan->user->notify(new IzinStatusNotification($izin));
            }
            $count++;
        }

        AuditLog::catat('approve', "Bulk approve {$count} pengajuan izin/sakit");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} pengajuan izin berhasil disetujui dan dicatat di absensi.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda.";
        return back()->with('success', $pesan);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $izinList = Izin::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($izinList as $izin) {
            if (Gate::denies('approve-izin', $izin)) {
                $skipped++;
                continue;
            }
            $izin->update([
                'status'      => 'ditolak',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            if ($izin->karyawan->user) {
                $izin->karyawan->user->notify(new IzinStatusNotification($izin));
            }
            $count++;
        }

        AuditLog::catat('reject', "Bulk reject {$count} pengajuan izin/sakit");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} pengajuan izin berhasil ditolak.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda.";
        return back()->with('success', $pesan);
    }

    public function exportCsv(Request $request)
    {
        // Wajibkan bulan/tahun (default bulan berjalan) — tanpa ini export bisa
        // menarik SELURUH riwayat izin sekaligus, berisiko timeout/memory.
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        return Excel::download(
            new IzinExport($bulan, $tahun, $request->status, $request->jenis, $this->scopedDepartmentIds()),
            'izin-' . now()->format('Y-m') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        $deptIds = $this->scopedDepartmentIds();
        $query = Izin::with('karyawan.department', 'approver')
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->jenis,         fn($q) => $q->where('jenis', $request->jenis))
            ->when($deptIds !== null,       fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($deptIds === null && $request->department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $request->department_id)))
            ->when($request->bulan,         fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->tahun,         fn($q) => $q->whereYear('tanggal', $request->tahun))
            ->orderBy('tanggal', 'desc');

        $izinList         = $query->get();
        $namaPerusahaan   = \App\Models\Pengaturan::get('nama_perusahaan', 'PT Global Multi Pharmalab');
        $alamatPerusahaan = \App\Models\Pengaturan::get('alamat_perusahaan', '');
        $kotaPerusahaan   = \App\Models\Pengaturan::get('kota_perusahaan', '');
        $noTelpPerusahaan = \App\Models\Pengaturan::get('no_telp_perusahaan', '');

        $bulan = $request->bulan;
        $tahun = $request->tahun ?? now()->year;
        $filterLabel = '';
        if ($bulan) $filterLabel .= 'Bulan: ' . Carbon::create()->month($bulan)->translatedFormat('F') . " {$tahun} | ";
        if ($request->status) $filterLabel .= 'Status: ' . ucfirst($request->status) . ' | ';
        if ($request->jenis)  $filterLabel .= 'Jenis: ' . ucfirst($request->jenis);
        $filterLabel = rtrim($filterLabel, ' | ');

        ini_set('memory_limit', '256M');
        $pdf = Pdf::loadView('admin.izin.export-pdf', compact(
            'izinList', 'namaPerusahaan', 'alamatPerusahaan', 'kotaPerusahaan', 'noTelpPerusahaan', 'filterLabel', 'bulan', 'tahun'
        ))->setPaper('a4', 'landscape');

        $filename = 'izin-sakit-' . ($bulan ? "{$bulan}-{$tahun}" : now()->format('Ymd')) . '.pdf';
        return $pdf->download($filename);
    }

    public function destroy(Izin $izin)
    {
        $izin->load('karyawan');
        $karyawanNama = $izin->karyawan->nama ?? '-';
        AuditLog::catat('delete', "Hapus izin {$izin->jenis} {$karyawanNama} ({$izin->tanggal->format('d M Y')})", $izin);
        if ($izin->lampiran) Storage::disk('public')->delete($izin->lampiran);
        $izin->delete();

        ApprovalSayaController::invalidasiCache($izin->karyawan?->department);

        return back()->with('success', 'Data izin dihapus.');
    }
}
