<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['karyawan', 'shift']);

        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            $query->whereDate('tanggal', today());
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%");
            });
        }

        $absensi = $query->latest()->paginate(15);
        $tanggal = $request->tanggal ?? today()->format('Y-m-d');

        return view('admin.absensi.index', compact('absensi', 'tanggal'));
    }

    public function create()
    {
        $karyawan = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.absensi.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal'     => 'required|date',
            'jam_masuk'   => 'nullable|date_format:H:i',
            'jam_keluar'  => 'nullable|date_format:H:i',
            'status'      => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan'  => 'nullable|string',
        ]);

        $karyawan = Karyawan::findOrFail($request->karyawan_id);

        Absensi::updateOrCreate(
            ['karyawan_id' => $request->karyawan_id, 'tanggal' => $request->tanggal],
            array_merge($request->all(), ['shift_id' => $karyawan->shift_id])
        );

        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Absensi berhasil disimpan!');
    }

    public function edit(Absensi $absensi)
    {
        $karyawan = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.absensi.edit', compact('absensi', 'karyawan'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'jam_masuk'  => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);

        $absensi->update($request->only('jam_masuk', 'jam_keluar', 'status', 'keterangan'));

        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Absensi berhasil diupdate!');
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return redirect()->route('admin.absensi.index')
                         ->with('success', 'Data absensi dihapus!');
    }

    public function show(Absensi $absensi)
    {
        return view('admin.absensi.show', compact('absensi'));
    }
}
