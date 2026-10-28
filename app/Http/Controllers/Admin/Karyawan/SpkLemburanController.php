<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Karyawan;
use App\Models\Lemburan;
use App\Models\SpkLembur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpkLemburanController extends Controller
{
    public function index(Request $request)
    {
        $query = SpkLembur::with(['karyawan.department', 'department', 'pembuat'])
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->tanggal,       fn($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->search, fn($q) => $q->where(function ($s) use ($request) {
                $s->where('nomor_spk', 'like', "%{$request->search}%")
                  ->orWhere('pekerjaan', 'like', "%{$request->search}%")
                  ->orWhereHas('karyawan', fn($k) => $k->where('nama', 'like', "%{$request->search}%"));
            }))
            ->latest('tanggal')
            ->latest('id');

        $spkList    = $query->paginate(20)->withQueryString();
        $departments = Department::orderBy('name')->get();

        $statTotal   = SpkLembur::count();
        $statAktif   = SpkLembur::where('status', 'aktif')->count();
        $statHariIni = SpkLembur::whereDate('tanggal', today())->count();

        return view('admin.spk-lembur.index', compact(
            'spkList', 'departments', 'statTotal', 'statAktif', 'statHariIni'
        ));
    }

    public function create()
    {
        $karyawan    = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing'])
                               ->orderBy('nama')->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.spk-lembur.create', compact('karyawan', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target'        => 'required|in:karyawan,departemen',
            'karyawan_id'   => 'required_if:target,karyawan|nullable|exists:karyawan,id',
            'department_id' => 'required_if:target,departemen|nullable|exists:departments,id',
            'tanggal'       => 'required|date',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i',
            'pekerjaan'     => 'required|string|max:500',
            'lokasi'        => 'nullable|string|max:100',
            'estimasi_jam'  => 'nullable|numeric|min:0.5|max:12',
            'catatan'       => 'nullable|string|max:300',
            'langsung_aktif'=> 'nullable|boolean',
        ]);

        $status = $request->boolean('langsung_aktif') ? 'aktif' : 'draft';

        $spk = SpkLembur::createWithNomor([
            'karyawan_id'    => $request->target === 'karyawan'   ? $request->karyawan_id   : null,
            'department_id'  => $request->target === 'departemen' ? $request->department_id : null,
            'tanggal'        => $request->tanggal,
            'jam_mulai'      => $request->jam_mulai,
            'jam_selesai'    => $request->jam_selesai,
            'pekerjaan'      => $request->pekerjaan,
            'lokasi'         => $request->lokasi,
            'estimasi_jam'   => $request->estimasi_jam,
            'catatan'        => $request->catatan,
            'status'         => $status,
            'dibuat_oleh'    => auth()->id(),
            'disetujui_oleh' => $status === 'aktif' ? auth()->id() : null,
            'disetujui_at'   => $status === 'aktif' ? now()         : null,
        ]);

        // Kirim notif ke karyawan yang bersangkutan
        if ($status === 'aktif') {
            $this->kirimNotifSpk($spk);
        }

        AuditLog::catat('create', "Buat SPK Lembur {$spk->nomor_spk} untuk {$spk->target_label}");

        return redirect()->route('admin.spk-lembur.index')
            ->with('success', "SPK {$spk->nomor_spk} berhasil dibuat" . ($status === 'aktif' ? ' dan langsung diaktifkan.' : '. Status: Draft.'));
    }

    public function show(SpkLembur $spkLembur)
    {
        $spkLembur->load(['karyawan.department', 'department', 'pembuat', 'penyetuju', 'lemburan.karyawan']);
        return view('admin.spk-lembur.show', compact('spkLembur'));
    }

    public function aktifkan(SpkLembur $spkLembur)
    {
        if (!in_array($spkLembur->status, ['draft'])) {
            return back()->with('error', 'Hanya SPK draft yang bisa diaktifkan.');
        }

        $spkLembur->update([
            'status'         => 'aktif',
            'disetujui_oleh' => auth()->id(),
            'disetujui_at'   => now(),
        ]);

        $this->kirimNotifSpk($spkLembur);

        AuditLog::catat('update', "Aktifkan SPK Lembur {$spkLembur->nomor_spk}");
        return back()->with('success', "SPK {$spkLembur->nomor_spk} diaktifkan. Karyawan sudah diberitahu.");
    }

    public function selesaikan(SpkLembur $spkLembur)
    {
        if ($spkLembur->status !== 'aktif') {
            return back()->with('error', 'Hanya SPK aktif yang bisa diselesaikan.');
        }

        $spkLembur->update(['status' => 'selesai']);
        AuditLog::catat('update', "Selesaikan SPK Lembur {$spkLembur->nomor_spk}");
        return back()->with('success', "SPK {$spkLembur->nomor_spk} ditandai selesai.");
    }

    public function batalkan(SpkLembur $spkLembur)
    {
        if ($spkLembur->status === 'selesai') {
            return back()->with('error', 'SPK yang sudah selesai tidak bisa dibatalkan.');
        }

        $spkLembur->update(['status' => 'dibatalkan']);
        AuditLog::catat('update', "Batalkan SPK Lembur {$spkLembur->nomor_spk}");
        return back()->with('success', "SPK {$spkLembur->nomor_spk} dibatalkan.");
    }

    public function destroy(SpkLembur $spkLembur)
    {
        if ($spkLembur->status !== 'draft') {
            return back()->with('error', 'Hanya SPK draft yang bisa dihapus.');
        }

        $nomor = $spkLembur->nomor_spk;
        $spkLembur->delete();
        AuditLog::catat('delete', "Hapus SPK Lembur {$nomor}");
        return redirect()->route('admin.spk-lembur.index')->with('success', "SPK {$nomor} dihapus.");
    }

    private function kirimNotifSpk(SpkLembur $spk): void
    {
        // Tentukan user yang perlu dinotif
        $karyawanList = collect();

        if ($spk->karyawan_id) {
            $karyawanList = Karyawan::where('id', $spk->karyawan_id)->with('user')->get();
        } elseif ($spk->department_id) {
            $karyawanList = Karyawan::where('department_id', $spk->department_id)
                ->whereIn('status', ['kontrak','tetap','magang','training'])
                ->with('user')
                ->get();
        }

        foreach ($karyawanList as $k) {
            if (!$k->user) continue;

            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'spk_lembur',
                'notifiable_type' => User::class,
                'notifiable_id'   => $k->user->id,
                'data'            => json_encode([
                    'title' => "📋 SPK Lembur: {$spk->nomor_spk}",
                    'body'  => "Anda mendapat perintah lembur pada {$spk->tanggal->translatedFormat('l, d F Y')} pukul " .
                               substr($spk->jam_mulai,0,5)." – ".substr($spk->jam_selesai,0,5).
                               ".\nPekerjaan: {$spk->pekerjaan}",
                    'url'   => '/portal/lembur',
                    'icon'  => 'fas fa-file-alt',
                    'color' => '#1e40af',
                    'bg'    => '#dbeafe',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
