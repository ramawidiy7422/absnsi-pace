<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Education;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'level'       => 'required|string|max:50',
            'major'       => 'nullable|string|max:100',
            'institution' => 'required|string|max:150',
            'year'        => 'nullable|digits:4|integer|min:1950|max:' . (date('Y') + 1),
        ]);

        $karyawan->educations()->create($request->only('level', 'major', 'institution', 'year'));

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Riwayat pendidikan berhasil ditambahkan!')
                         ->withFragment('tab-education');
    }

    public function update(Request $request, Education $education)
    {
        $request->validate([
            'level'       => 'required|string|max:50',
            'major'       => 'nullable|string|max:100',
            'institution' => 'required|string|max:150',
            'year'        => 'nullable|digits:4|integer|min:1950|max:' . (date('Y') + 1),
        ]);

        $education->update($request->only('level', 'major', 'institution', 'year'));

        return redirect()->route('admin.karyawan.show', $education->karyawan_id)
                         ->with('success', 'Riwayat pendidikan berhasil diupdate!')
                         ->withFragment('tab-education');
    }

    public function destroy(Education $education)
    {
        $karyawanId = $education->karyawan_id;
        AuditLog::catat('delete', "Hapus riwayat pendidikan karyawan ID {$karyawanId}: {$education->level} - {$education->institution}", $education);
        $education->delete();

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', 'Riwayat pendidikan berhasil dihapus!')
                         ->withFragment('tab-education');
    }
}
