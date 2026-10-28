<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['karyawan.department', 'shift']);

        $query->whereDate('tanggal', $request->tanggal ?? today());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereHas('karyawan', fn($q) => $q->whereIn('department_id', $deptIds ?: [0]));
        } elseif ($request->department_id) {
            $query->whereHas('karyawan', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->search) {
            $query->whereHas('karyawan', fn($q) =>
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%")
            );
        }

        $absensi       = $query->latest()->paginate(20)->withQueryString();
        $tanggal       = $request->tanggal ?? today()->format('Y-m-d');
        $departemenList = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.absensi.index', compact('absensi', 'tanggal', 'departemenList'));
    }

    public function create()
    {
        $karyawan = Karyawan::with('department')->whereIn('status', ['kontrak','tetap','magang','training'])->orderBy('nama')->get();
        return view('admin.absensi.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'          => 'required|exists:karyawan,id',
            'tanggal'              => 'required|date',
            'jam_masuk'            => 'nullable|date_format:H:i',
            'jam_keluar'           => 'nullable|date_format:H:i',
            'status'               => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan'           => 'nullable|string',
            'keterangan_terlambat' => 'nullable|string|max:500',
            'metode'               => 'nullable|in:manual,wajah,qrcode,face,sistem',
        ]);

        $karyawan = Karyawan::findOrFail($request->karyawan_id);

        // Hitung durasi jika ada jam_masuk dan jam_keluar (cross-midnight aware)
        $durasiMenit = null;
        if ($request->jam_masuk && $request->jam_keluar) {
            $detik = strtotime($request->jam_keluar) - strtotime($request->jam_masuk);
            if ($detik < 0) $detik += 86400;
            $durasiMenit = (int) round($detik / 60);
        }

        $tanggalAbsensi = \Carbon\Carbon::parse($request->tanggal);
        $shiftEfektif   = $karyawan->resolveShift($tanggalAbsensi);

        // Lock baris karyawan supaya input manual ini tidak race dengan kiosk/approval
        // lain yang menulis absensi karyawan+tanggal yang sama secara bersamaan.
        DB::transaction(function () use ($request, $karyawan, $shiftEfektif, $durasiMenit) {
            Karyawan::where('id', $karyawan->id)->lockForUpdate()->first();

            Absensi::updateOrCreate(
                ['karyawan_id' => $request->karyawan_id, 'tanggal' => $request->tanggal],
                array_merge(
                    $request->only('karyawan_id','tanggal','jam_masuk','jam_keluar','status','keterangan','keterangan_terlambat'),
                    [
                        'shift_id'     => $shiftEfektif?->id ?? $karyawan->shift_id,
                        'metode'       => $request->metode ?? 'manual',
                        'durasi_menit' => $durasiMenit,
                    ]
                )
            );
        });

        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Absensi berhasil disimpan!');
    }

    public function edit(Absensi $absensi)
    {
        $karyawan = Karyawan::with('department')->whereIn('status', ['kontrak','tetap','magang','training'])->orderBy('nama')->get();
        return view('admin.absensi.edit', compact('absensi', 'karyawan'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'jam_masuk'            => 'nullable|date_format:H:i',
            'jam_keluar'           => 'nullable|date_format:H:i',
            'status'               => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan'           => 'nullable|string',
            'keterangan_terlambat' => 'nullable|string|max:500',
        ]);

        $durasiMenit = null;
        if ($request->jam_masuk && $request->jam_keluar) {
            $detik = strtotime($request->jam_keluar) - strtotime($request->jam_masuk);
            if ($detik < 0) $detik += 86400;
            $durasiMenit = (int) round($detik / 60);
        }

        $tglAbsensi = $absensi->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('update', "Edit absensi {$absensi->karyawan?->nama} tgl {$tglAbsensi} → {$request->status}", $absensi);
        $absensi->update(array_merge(
            $request->only('jam_masuk', 'jam_keluar', 'status', 'keterangan', 'keterangan_terlambat'),
            ['durasi_menit' => $durasiMenit]
        ));

        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Absensi berhasil diupdate!');
    }

    public function destroy(Absensi $absensi)
    {
        $tglAbsensi = $absensi->tanggal?->format('d M Y') ?? '-';
        AuditLog::catat('delete', "Hapus absensi {$absensi->karyawan?->nama} tgl {$tglAbsensi}", $absensi);
        $absensi->delete();
        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Data absensi dihapus!');
    }

    public function show(Absensi $absensi)
    {
        return redirect()->route('admin.absensi.edit', $absensi);
    }

    public function updateAlasanTerlambat(Request $request, Absensi $absensi)
    {
        $request->validate([
            'keterangan_terlambat' => 'required|string|max:500',
        ]);

        if ($absensi->status !== 'terlambat') {
            return response()->json(['error' => 'Absensi bukan status terlambat.'], 422);
        }

        $absensi->update(['keterangan_terlambat' => $request->keterangan_terlambat]);

        AuditLog::catat('update', "Isi alasan terlambat {$absensi->karyawan->nama} tgl {$absensi->tanggal->format('d M Y')}: {$request->keterangan_terlambat}", $absensi);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Alasan keterlambatan berhasil disimpan.');
    }

    public function belumAbsen(Request $request)
    {
        $tanggal = $request->tanggal ? \Carbon\Carbon::parse($request->tanggal) : today();

        $sudahAbsenIds = Absensi::whereDate('tanggal', $tanggal)->pluck('karyawan_id');

        $query = Karyawan::with(['shift','department'])
            ->whereIn('status', ['kontrak', 'tetap', 'magang', 'training'])
            ->where('bebas_absen', false)
            ->whereNotIn('jabatan', \App\Models\Karyawan::JABATAN_BEBAS_ABSEN)
            ->whereNotIn('id', $sudahAbsenIds)
            ->orderBy('nama');

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds ?: [0]);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%");
            });
        }

        $karyawan   = $query->paginate(20)->withQueryString();
        $departemen = Department::orderBy('name')->get();

        return view('admin.absensi.belum-absen', compact('karyawan', 'departemen', 'tanggal'));
    }

    public function absenMassal(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'exists:karyawan,id',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'tanggal'=> 'required|date',
        ]);

        $tanggal = $request->tanggal;
        $status  = $request->status;
        $jamMasuk = now()->format('H:i:s');
        $count   = 0;

        $karyawanList = Karyawan::whereIn('id', $request->ids)->get()->keyBy('id');

        foreach ($request->ids as $karyawanId) {
            $karyawan = $karyawanList->get($karyawanId);
            if (!$karyawan) continue;

            $tglCarbon    = \Carbon\Carbon::parse($tanggal);
            $shiftEfektif = $karyawan->resolveShift($tglCarbon);

            // Cek shift untuk status terlambat — cross-midnight aware
            $finalStatus = $status;
            if ($status === 'hadir' && $shiftEfektif) {
                $batas = date('H:i:s', strtotime($shiftEfektif->jam_masuk) + ($shiftEfektif->toleransi_menit * 60));
                $detikKeScan  = strtotime($jamMasuk) - strtotime($shiftEfektif->jam_masuk);
                if ($detikKeScan < 0) $detikKeScan += 86400;
                $detikKeBatas = strtotime($batas) - strtotime($shiftEfektif->jam_masuk);
                if ($detikKeBatas < 0) $detikKeBatas += 86400;
                if ($detikKeScan > $detikKeBatas && $detikKeScan <= 12 * 3600) {
                    $finalStatus = 'terlambat';
                }
            }

            // Lock baris karyawan supaya absen massal ini tidak race dengan kiosk/approval
            // lain yang menulis absensi karyawan+tanggal yang sama secara bersamaan.
            DB::transaction(function () use ($karyawanId, $tanggal, $shiftEfektif, $karyawan, $finalStatus, $jamMasuk) {
                Karyawan::where('id', $karyawanId)->lockForUpdate()->first();

                Absensi::updateOrCreate(
                    ['karyawan_id' => $karyawanId, 'tanggal' => $tanggal],
                    [
                        'shift_id'  => $shiftEfektif?->id ?? $karyawan->shift_id,
                        // hadir & terlambat (termasuk auto-detect) tetap punya jam_masuk
                        'jam_masuk' => in_array($finalStatus, ['hadir', 'terlambat']) ? $jamMasuk : null,
                        'status'    => $finalStatus,
                        'metode'    => 'manual',
                        'keterangan'=> 'Absensi massal oleh admin',
                    ]
                );
            });
            $count++;
        }

        AuditLog::catat('create', "Absensi massal {$count} karyawan tgl {$tanggal} status: {$status}");

        return back()->with('success', "{$count} karyawan berhasil diabsenkan dengan status: " . ucfirst($status) . ".");
    }
}
