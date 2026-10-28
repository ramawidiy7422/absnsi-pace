<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\IzinTerlambat;
use App\Models\Karyawan;
use App\Models\User;
use App\Notifications\IzinTerlambatPengajuanNotification;
use App\Notifications\IzinTerlambatStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IzinTerlambatController extends Controller
{
    public function index(Request $request)
    {
        $deptIds = $this->scopedDepartmentIds();

        $query = IzinTerlambat::with('karyawan.department', 'inputOleh', 'approver')
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($deptIds !== null,       fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($deptIds === null && $request->department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $request->department_id)))
            ->when($request->search,        fn($q) => $q->whereHas('karyawan', fn($k) =>
                $k->where('nama', 'like', "%{$request->search}%")->orWhere('nik', 'like', "%{$request->search}%")
            ))
            ->when($request->bulan,         fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->tahun,         fn($q) => $q->whereYear('tanggal', $request->tahun));

        $izinTerlambatList = $query->latest('tanggal')->paginate(20)->withQueryString();
        $departemen        = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.izin-terlambat.index', compact('izinTerlambatList', 'departemen'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tanggal'    => 'required|date|after_or_equal:today',
            'keterangan' => 'required|string|max:255',
        ]);

        // Cek duplikasi: tidak boleh ada pengajuan aktif di tanggal yang sama
        $duplikat = IzinTerlambat::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $request->tanggal)
            ->whereIn('status', ['pending', 'disetujui'])
            ->first();
        if ($duplikat) {
            return back()->withInput()->with('error', "Sudah ada pengajuan izin terlambat di tanggal tersebut (status: {$duplikat->status}).");
        }

        $izinTerlambat = IzinTerlambat::create([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $request->keterangan,
            'status'      => 'pending',
            'input_by'    => Auth::id(),
        ]);

        $tglIzin = $izinTerlambat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('create', "Input izin terlambat (via WA) untuk {$karyawan->nama} ({$tglIzin})", $izinTerlambat);

        User::whereIn('role', ['superadmin', 'admin', 'hrd'])->get()
            ->each(fn($u) => $u->notify(new IzinTerlambatPengajuanNotification($izinTerlambat)));

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Pengajuan izin terlambat berhasil dicatat!')
                         ->withFragment('tab-ketidakhadiran');
    }

    public function approve(Request $request, IzinTerlambat $izinTerlambat)
    {
        if (Gate::denies('approve-izin-terlambat', $izinTerlambat)) {
            return back()->with('error', 'Anda tidak berwenang menyetujui izin terlambat karyawan ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izinTerlambat, $request) {
            $locked = IzinTerlambat::where('id', $izinTerlambat->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
            }
            $locked->update([
                'status'           => 'disetujui',
                'approved_by'      => Auth::id(),
                'approved_at'      => now(),
                'catatan_approver' => $request->catatan_approver,
            ]);
            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Pengajuan ini sudah tidak dalam status menunggu persetujuan.');
        }

        $tglIzin = $izinTerlambat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('approve', "Izin terlambat {$izinTerlambat->karyawan?->nama} ({$tglIzin}) disetujui", $izinTerlambat);

        if ($izinTerlambat->karyawan?->user) {
            $izinTerlambat->karyawan->user->notify(new IzinTerlambatStatusNotification($izinTerlambat));
        }

        ApprovalSayaController::invalidasiCache($izinTerlambat->karyawan?->department);

        return back()->with('success', 'Izin terlambat disetujui. Karyawan dapat absen masuk dengan status Terlambat (Berizin).');
    }

    public function reject(Request $request, IzinTerlambat $izinTerlambat)
    {
        if (Gate::denies('approve-izin-terlambat', $izinTerlambat)) {
            return back()->with('error', 'Anda tidak berwenang menolak izin terlambat karyawan ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izinTerlambat, $request) {
            $locked = IzinTerlambat::where('id', $izinTerlambat->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return true;
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
            return back()->with('error', 'Pengajuan ini sudah tidak dalam status menunggu persetujuan.');
        }

        $tglIzin = $izinTerlambat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('reject', "Izin terlambat {$izinTerlambat->karyawan?->nama} ({$tglIzin}) ditolak", $izinTerlambat);

        if ($izinTerlambat->karyawan?->user) {
            $izinTerlambat->karyawan->user->notify(new IzinTerlambatStatusNotification($izinTerlambat));
        }

        ApprovalSayaController::invalidasiCache($izinTerlambat->karyawan?->department);

        return back()->with('success', 'Izin terlambat ditolak.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = IzinTerlambat::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $izinTerlambat) {
            if (Gate::denies('approve-izin-terlambat', $izinTerlambat)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($izinTerlambat) {
                $locked = IzinTerlambat::where('id', $izinTerlambat->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'disetujui', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($izinTerlambat->karyawan?->user) {
                $izinTerlambat->karyawan->user->notify(new IzinTerlambatStatusNotification($izinTerlambat));
            }
            $count++;
        }

        AuditLog::catat('approve', "Bulk approve {$count} izin terlambat");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} izin terlambat berhasil disetujui.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = IzinTerlambat::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $izinTerlambat) {
            if (Gate::denies('approve-izin-terlambat', $izinTerlambat)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($izinTerlambat) {
                $locked = IzinTerlambat::where('id', $izinTerlambat->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'ditolak', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($izinTerlambat->karyawan?->user) {
                $izinTerlambat->karyawan->user->notify(new IzinTerlambatStatusNotification($izinTerlambat));
            }
            $count++;
        }

        AuditLog::catat('reject', "Bulk reject {$count} izin terlambat");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} izin terlambat berhasil ditolak.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    public function destroy(IzinTerlambat $izinTerlambat)
    {
        $izinTerlambat->load('karyawan');
        $karyawanNama = $izinTerlambat->karyawan->nama ?? '-';
        $tglIzin      = $izinTerlambat->tanggal?->format('d M Y') ?? '-';

        // Batalkan status "Terlambat (Berizin)" pada absensi yang terikat, supaya badge tidak tetap
        // menampilkan berizin setelah persetujuannya dicabut/dihapus.
        \App\Models\Absensi::where('izin_terlambat_id', $izinTerlambat->id)
            ->update(['keterangan_terlambat' => null, 'izin_terlambat_id' => null]);

        AuditLog::catat('delete', "Hapus izin terlambat {$karyawanNama} ({$tglIzin})", $izinTerlambat);
        $izinTerlambat->delete();

        ApprovalSayaController::invalidasiCache($izinTerlambat->karyawan?->department);

        return back()->with('success', 'Data izin terlambat dihapus. Status keterlambatan terkait telah dikembalikan ke tanpa izin.');
    }
}
