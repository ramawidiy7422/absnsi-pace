<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employeshift;
use App\Models\Karyawan;
use App\Models\Shift;
use Illuminate\Http\Request;

class EmployeshiftController extends Controller
{
    public function index(Karyawan $karyawan)
    {
        $employeshifts = $karyawan->employeshifts()->with('shift')->orderByDesc('date')->get();
        $shifts        = Shift::where('status', 'aktif')->orderBy('nama_shift')->get();

        return view('admin.karyawan.partials.employeshift', compact('karyawan', 'employeshifts', 'shifts'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'date'       => 'required|date',
            'shift_id'   => 'required|exists:shifts,id,status,aktif',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $karyawan->employeshifts()->updateOrCreate(
            ['date' => $request->date],
            ['shift_id' => $request->shift_id, 'keterangan' => $request->keterangan]
        );

        return $this->redirectAfter($request, $karyawan->id, 'Jadwal shift berhasil ditambahkan!');
    }

    public function update(Request $request, Employeshift $employeshift)
    {
        $request->validate([
            'date'       => 'required|date',
            'shift_id'   => 'required|exists:shifts,id,status,aktif',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $employeshift->update([
            'shift_id'   => $request->shift_id,
            'date'       => $request->date,
            'keterangan' => $request->keterangan,
        ]);

        return $this->redirectAfter($request, $employeshift->karyawan_id, 'Jadwal shift berhasil diperbarui!');
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
            'shift_id'       => 'required|exists:shifts,id,status,aktif',
            'karyawan_ids'   => 'required|array|min:1',
            'karyawan_ids.*' => 'exists:karyawan,id',
            'hari'           => 'nullable|array',
            'hari.*'         => 'integer|between:0,6',
            'keterangan'     => 'nullable|string|max:255',
        ], [
            'karyawan_ids.required' => 'Minimal 1 karyawan harus dipilih.',
            'karyawan_ids.min'      => 'Minimal 1 karyawan harus dipilih.',
        ]);

        $deptIds = $this->scopedDepartmentIds();

        $hariDipilih      = $request->input('hari', [0, 1, 2, 3, 4, 5, 6]);
        $from             = \Carbon\Carbon::parse($request->date_from);
        $to               = \Carbon\Carbon::parse($request->date_to);
        $karyawanIds      = $request->karyawan_ids;
        $ubahShiftDefault = $request->boolean('ubah_shift_default');
        $totalJadwal      = 0;
        $shiftBaru        = Shift::find($request->shift_id)?->nama_shift ?? '-';

        $karyawanList = Karyawan::with('shift')
            ->whereIn('id', $karyawanIds)
            ->when($deptIds !== null, fn($q) => $q->whereIn('department_id', $deptIds ?: [0]))
            ->get()
            ->keyBy('id');
        $totalKaryawan = $karyawanList->count();

        foreach ($karyawanIds as $karyawanId) {
            $karyawan = $karyawanList->get($karyawanId);
            if (!$karyawan) continue;

            if ($ubahShiftDefault) {
                $shiftLama = $karyawan->shift?->nama_shift ?? '(tidak ada)';
                $karyawan->update(['shift_id' => $request->shift_id]);
                AuditLog::catat('update', "Ubah shift default {$karyawan->nama}: {$shiftLama} → {$shiftBaru} (bulk)", $karyawan);
            }

            for ($tgl = $from->copy(); $tgl->lte($to); $tgl->addDay()) {
                if (in_array($tgl->dayOfWeek, $hariDipilih)) {
                    $karyawan->employeshifts()->updateOrCreate(
                        ['date' => $tgl->toDateString()],
                        ['shift_id' => $request->shift_id, 'keterangan' => $request->keterangan]
                    );
                    $totalJadwal++;
                }
            }
        }

        $msg = "Berhasil mengatur {$totalJadwal} jadwal untuk {$totalKaryawan} karyawan!";
        if ($ubahShiftDefault) {
            $msg .= " Shift default {$totalKaryawan} karyawan diubah ke {$shiftBaru}.";
        }
        $redirect = $request->filled('_redirect') ? $request->_redirect : route('admin.laporan.jadwal-shift');
        return redirect($redirect)->with('success', $msg);
    }

    public function storeRange(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
            'shift_id'   => 'required|exists:shifts,id,status,aktif',
            'hari'       => 'nullable|array',
            'hari.*'     => 'integer|between:0,6',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $hariDipilih = $request->input('hari', [0, 1, 2, 3, 4, 5, 6]);
        $from        = \Carbon\Carbon::parse($request->date_from);
        $to          = \Carbon\Carbon::parse($request->date_to);
        $count       = 0;

        for ($tgl = $from->copy(); $tgl->lte($to); $tgl->addDay()) {
            if (in_array($tgl->dayOfWeek, $hariDipilih)) {
                $karyawan->employeshifts()->updateOrCreate(
                    ['date' => $tgl->toDateString()],
                    ['shift_id' => $request->shift_id, 'keterangan' => $request->keterangan]
                );
                $count++;
            }
        }

        $msg = "Berhasil mengatur jadwal {$count} hari!";
        return $this->redirectAfter($request, $karyawan->id, $msg);
    }

    public function updateShiftDefault(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'shift_id' => 'required|exists:shifts,id,status,aktif',
        ]);

        $shiftLama = $karyawan->shift?->nama_shift ?? '(tidak ada)';
        $karyawan->update(['shift_id' => $request->shift_id]);
        $shiftBaru = Shift::find($request->shift_id)?->nama_shift ?? '-';

        AuditLog::catat('update', "Ubah shift default {$karyawan->nama}: {$shiftLama} → {$shiftBaru}", $karyawan);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'shift_nama' => $shiftBaru]);
        }
        return back()->with('success', "Shift default {$karyawan->nama} diubah ke {$shiftBaru}.");
    }

    public function destroy(Request $request, Employeshift $employeshift)
    {
        $karyawanId = $employeshift->karyawan_id;
        AuditLog::catat('delete', "Hapus jadwal shift karyawan ID {$karyawanId} tgl {$employeshift->date}", $employeshift);
        $employeshift->delete();

        return $this->redirectAfter($request, $karyawanId, 'Jadwal shift berhasil dihapus!');
    }

    private function redirectAfter(Request $request, int $karyawanId, string $msg)
    {
        if ($request->filled('_redirect')) {
            return redirect($request->_redirect)->with('success', $msg);
        }

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', $msg)
                         ->withFragment('tab-employeshift');
    }
}
