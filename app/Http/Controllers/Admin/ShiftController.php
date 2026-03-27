<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('karyawan')->latest()->get();
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

        Shift::create($request->all());

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

        $shift->update($request->all());

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
        $karyawan = $shift->karyawan()->paginate(10);
        return view('admin.shift.show', compact('shift', 'karyawan'));
    }
}
