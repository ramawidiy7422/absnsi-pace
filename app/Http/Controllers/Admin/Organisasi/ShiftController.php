<?php

namespace App\Http\Controllers\Admin\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $deptIds = $this->scopedDepartmentIds();
        $shifts = Shift::withCount(['karyawan' => function ($q) use ($deptIds) {
            if ($deptIds !== null) {
                $q->whereIn('department_id', $deptIds);
            }
        }])->latest()->get();
        return view('admin.shift.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shift.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_shift'       => 'required|string|max:50',
            'jam_masuk'        => 'required|date_format:H:i',
            'jam_keluar'       => 'required|date_format:H:i',
            'toleransi_menit'  => 'required|integer|min:0|max:60',
        ]);

        Shift::create($request->only(['nama_shift','jam_masuk','jam_keluar','toleransi_menit']));

        return redirect()->route('admin.shift.index')
                         ->with('success', 'Shift berhasil ditambahkan!');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shift.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'nama_shift'       => 'required|string|max:50',
            'jam_masuk'        => 'required|date_format:H:i',
            'jam_keluar'       => 'required|date_format:H:i',
            'toleransi_menit'  => 'required|integer|min:0|max:60',
        ]);

        $shift->update($request->only(['nama_shift','jam_masuk','jam_keluar','toleransi_menit']));

        return redirect()->route('admin.shift.index')
                         ->with('success', 'Shift berhasil diupdate!');
    }

    public function destroy(Shift $shift)
    {
        $shift->update(['status' => 'nonaktif']);
        return redirect()->route('admin.shift.index')
                         ->with('success', 'Shift dinonaktifkan!');
    }

    public function show(Shift $shift)
    {
        return redirect()->route('admin.shift.edit', $shift);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'shift_id'      => 'required|exists:shifts,id,status,aktif',
            'department_id' => 'nullable|exists:departments,id',
        ], [
            'shift_id.exists' => 'Shift yang dipilih tidak ditemukan atau sudah dinonaktifkan.',
        ]);

        $deptIds = $this->scopedDepartmentIds();
        if ($deptIds !== null && empty($deptIds)) {
            abort(403, 'Anda tidak terdaftar sebagai SPV/manajer departemen manapun.');
        }
        if ($deptIds !== null && $request->department_id && !in_array((int) $request->department_id, $deptIds, true)) {
            abort(403, 'Anda hanya boleh assign shift untuk departemen Anda sendiri.');
        }

        $query = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing']);

        if ($deptIds !== null) {
            // Supervisor/manajer: WAJIB dibatasi ke departemennya, tidak boleh
            // bulk-assign tanpa department_id sama sekali (yang berarti semua dept).
            $query->whereIn('department_id', $deptIds);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if (!$request->boolean('overwrite')) {
            $query->whereNull('shift_id');
        }

        $count = $query->count();
        $query->update(['shift_id' => $request->shift_id, 'updated_at' => now()]);

        $shift = Shift::find($request->shift_id);
        return back()->with('shift_assign_result', "$count karyawan berhasil diassign ke {$shift->nama_shift}.");
    }
}
