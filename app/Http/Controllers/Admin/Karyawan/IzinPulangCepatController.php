<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Admin\ApprovalSayaController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\IzinPulangCepat;
use App\Models\Karyawan;
use App\Models\User;
use App\Notifications\IzinPulangCepatPengajuanNotification;
use App\Notifications\IzinPulangCepatStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IzinPulangCepatController extends Controller
{
    public function index(Request $request)
    {
        $deptIds = $this->scopedDepartmentIds();

        $query = IzinPulangCepat::with('karyawan.department', 'inputOleh', 'approver')
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($deptIds !== null,       fn($q) => $q->whereHas('karyawan', fn($k) => $k->whereIn('department_id', $deptIds ?: [0])))
            ->when($deptIds === null && $request->department_id, fn($q) => $q->whereHas('karyawan', fn($k) => $k->where('department_id', $request->department_id)))
            ->when($request->search,        fn($q) => $q->whereHas('karyawan', fn($k) =>
                $k->where('nama', 'like', "%{$request->search}%")->orWhere('nik', 'like', "%{$request->search}%")
            ))
            ->when($request->bulan,         fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->tahun,         fn($q) => $q->whereYear('tanggal', $request->tahun));

        $izinPulangCepatList = $query->latest('tanggal')->paginate(20)->withQueryString();
        $departemen          = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.izin-pulang-cepat.index', compact('izinPulangCepatList', 'departemen'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        // Cek duplikasi: tidak boleh ada pengajuan aktif di tanggal yang sama
        $duplikat = IzinPulangCepat::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $request->tanggal)
            ->whereIn('status', ['pending', 'disetujui'])
            ->first();
        if ($duplikat) {
            return back()->withInput()->with('error', "Sudah ada pengajuan izin pulang cepat di tanggal tersebut (status: {$duplikat->status}).");
        }

        $izinPulangCepat = IzinPulangCepat::create([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $request->keterangan,
            'status'      => 'pending',
            'input_by'    => Auth::id(),
        ]);

        $tglIzin = $izinPulangCepat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('create', "Input izin pulang cepat untuk {$karyawan->nama} ({$tglIzin})", $izinPulangCepat);

        User::whereIn('role', ['superadmin', 'admin', 'hrd'])->get()
            ->each(fn($u) => $u->notify(new IzinPulangCepatPengajuanNotification($izinPulangCepat)));

        ApprovalSayaController::invalidasiCache($karyawan->department);

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Pengajuan izin pulang cepat berhasil dicatat!')
                         ->withFragment('tab-ketidakhadiran');
    }

    public function approve(Request $request, IzinPulangCepat $izinPulangCepat)
    {
        if (Gate::denies('approve-izin-pulang-cepat', $izinPulangCepat)) {
            return back()->with('error', 'Anda tidak berwenang menyetujui izin pulang cepat karyawan ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izinPulangCepat, $request) {
            $locked = IzinPulangCepat::where('id', $izinPulangCepat->id)->lockForUpdate()->first();
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

        $tglIzin = $izinPulangCepat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('approve', "Izin pulang cepat {$izinPulangCepat->karyawan?->nama} ({$tglIzin}) disetujui", $izinPulangCepat);

        if ($izinPulangCepat->karyawan?->user) {
            $izinPulangCepat->karyawan->user->notify(new IzinPulangCepatStatusNotification($izinPulangCepat));
        }

        ApprovalSayaController::invalidasiCache($izinPulangCepat->karyawan?->department);

        return back()->with('success', 'Izin pulang cepat disetujui. Karyawan dapat absen keluar tanpa tercatat pulang cepat.');
    }

    public function reject(Request $request, IzinPulangCepat $izinPulangCepat)
    {
        if (Gate::denies('approve-izin-pulang-cepat', $izinPulangCepat)) {
            return back()->with('error', 'Anda tidak berwenang menolak izin pulang cepat karyawan ini.');
        }

        $sudahDiproses = DB::transaction(function () use ($izinPulangCepat, $request) {
            $locked = IzinPulangCepat::where('id', $izinPulangCepat->id)->lockForUpdate()->first();
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

        $tglIzin = $izinPulangCepat->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('reject', "Izin pulang cepat {$izinPulangCepat->karyawan?->nama} ({$tglIzin}) ditolak", $izinPulangCepat);

        if ($izinPulangCepat->karyawan?->user) {
            $izinPulangCepat->karyawan->user->notify(new IzinPulangCepatStatusNotification($izinPulangCepat));
        }

        ApprovalSayaController::invalidasiCache($izinPulangCepat->karyawan?->department);

        return back()->with('success', 'Izin pulang cepat ditolak.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = IzinPulangCepat::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $izinPulangCepat) {
            if (Gate::denies('approve-izin-pulang-cepat', $izinPulangCepat)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($izinPulangCepat) {
                $locked = IzinPulangCepat::where('id', $izinPulangCepat->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'disetujui', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($izinPulangCepat->karyawan?->user) {
                $izinPulangCepat->karyawan->user->notify(new IzinPulangCepatStatusNotification($izinPulangCepat));
            }
            $count++;
        }

        AuditLog::catat('approve', "Bulk approve {$count} izin pulang cepat");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} izin pulang cepat berhasil disetujui.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', 'Tidak ada data yang dipilih.');

        $list = IzinPulangCepat::with('karyawan.user', 'karyawan.department')->whereIn('id', $ids)->where('status', 'pending')->get();
        $count = 0;
        $skipped = 0;

        foreach ($list as $izinPulangCepat) {
            if (Gate::denies('approve-izin-pulang-cepat', $izinPulangCepat)) {
                $skipped++;
                continue;
            }
            $sudahDiproses = DB::transaction(function () use ($izinPulangCepat) {
                $locked = IzinPulangCepat::where('id', $izinPulangCepat->id)->lockForUpdate()->first();
                if ($locked->status !== 'pending') return true;
                $locked->update(['status' => 'ditolak', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                return false;
            });
            if ($sudahDiproses) { $skipped++; continue; }
            if ($izinPulangCepat->karyawan?->user) {
                $izinPulangCepat->karyawan->user->notify(new IzinPulangCepatStatusNotification($izinPulangCepat));
            }
            $count++;
        }

        AuditLog::catat('reject', "Bulk reject {$count} izin pulang cepat");
        if ($count > 0) ApprovalSayaController::invalidasiCache();
        $pesan = "{$count} izin pulang cepat berhasil ditolak.";
        if ($skipped > 0) $pesan .= " {$skipped} dilewati karena bukan wewenang Anda atau sudah diproses.";
        return back()->with('success', $pesan);
    }

    public function destroy(IzinPulangCepat $izinPulangCepat)
    {
        $izinPulangCepat->load('karyawan');
        $karyawanNama = $izinPulangCepat->karyawan->nama ?? '-';
        $tglIzin      = $izinPulangCepat->tanggal?->format('d M Y') ?? '-';

        // Batalkan status berizin pada absensi yang terikat, supaya badge tidak tetap
        // menampilkan berizin setelah persetujuannya dicabut/dihapus.
        \App\Models\Absensi::where('izin_pulang_cepat_id', $izinPulangCepat->id)
            ->update(['keterangan_pulang_cepat' => null, 'izin_pulang_cepat_id' => null]);

        AuditLog::catat('delete', "Hapus izin pulang cepat {$karyawanNama} ({$tglIzin})", $izinPulangCepat);
        $izinPulangCepat->delete();

        ApprovalSayaController::invalidasiCache($izinPulangCepat->karyawan?->department);

        return back()->with('success', 'Data izin pulang cepat dihapus. Status pulang cepat terkait telah dikembalikan ke tanpa izin.');
    }
}
