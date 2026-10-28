<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\Workhistory;
use Illuminate\Http\Request;

class WorkhistoryController extends Controller
{
    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'type'            => 'required|string|max:50',
            'jabatan'         => 'nullable|string|max:100',
            'departemen'      => 'nullable|string|max:100',
            'status_karyawan' => 'nullable|in:kontrak,tetap,magang,nonaktif',
            'description'     => 'nullable|string|max:500',
        ]);

        $karyawan->workhistories()->create($request->only('start_date', 'end_date', 'type', 'jabatan', 'departemen', 'status_karyawan', 'description'));

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Riwayat kerja berhasil ditambahkan!')
                         ->withFragment('tab-workhistory');
    }

    public function update(Request $request, Workhistory $workhistory)
    {
        $request->validate([
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'type'            => 'required|string|max:50',
            'jabatan'         => 'nullable|string|max:100',
            'departemen'      => 'nullable|string|max:100',
            'status_karyawan' => 'nullable|in:kontrak,tetap,magang,nonaktif',
            'description'     => 'nullable|string|max:500',
        ]);

        $workhistory->update($request->only('start_date', 'end_date', 'type', 'jabatan', 'departemen', 'status_karyawan', 'description'));

        return redirect()->route('admin.karyawan.show', $workhistory->karyawan_id)
                         ->with('success', 'Riwayat kerja berhasil diupdate!')
                         ->withFragment('tab-workhistory');
    }

    public function destroy(Workhistory $workhistory)
    {
        $karyawanId = $workhistory->karyawan_id;
        AuditLog::catat('delete', "Hapus riwayat kerja karyawan ID {$karyawanId}: {$workhistory->type} ({$workhistory->jabatan})", $workhistory);
        $workhistory->delete();

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', 'Riwayat kerja berhasil dihapus!')
                         ->withFragment('tab-workhistory');
    }
}
