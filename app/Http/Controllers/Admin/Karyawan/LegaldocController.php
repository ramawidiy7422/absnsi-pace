<?php

namespace App\Http\Controllers\Admin\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\Legaldoc;
use Illuminate\Http\Request;

class LegaldocController extends Controller
{
    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik'            => 'nullable|string|max:20',
            'kk'             => 'nullable|string|max:20',
            'npwp'           => 'nullable|string|max:30',
            'bpjs_tk'        => 'nullable|string|max:30',
            'bpjs_kesehatan' => 'nullable|string|max:30',
        ]); 

        // upsert: satu karyawan satu legaldoc
        $karyawan->legaldoc()->updateOrCreate(
            ['karyawan_id' => $karyawan->id],
            $request->only('nik', 'kk', 'npwp', 'bpjs_tk', 'bpjs_kesehatan')
        );

        return redirect()->route('admin.karyawan.show', $karyawan)
                         ->with('success', 'Dokumen legal berhasil disimpan!')
                         ->withFragment('tab-legaldoc');
    }

    public function update(Request $request, Legaldoc $legaldoc)
    {
        $request->validate([
            'nik'            => 'nullable|string|max:20',
            'kk'             => 'nullable|string|max:20',
            'npwp'           => 'nullable|string|max:30',
            'bpjs_tk'        => 'nullable|string|max:30',
            'bpjs_kesehatan' => 'nullable|string|max:30',
        ]);

        $legaldoc->update($request->only('nik', 'kk', 'npwp', 'bpjs_tk', 'bpjs_kesehatan'));

        return redirect()->route('admin.karyawan.show', $legaldoc->karyawan_id)
                         ->with('success', 'Dokumen legal berhasil diupdate!')
                         ->withFragment('tab-legaldoc');
    }

    public function destroy(Legaldoc $legaldoc)
    {
        $karyawanId = $legaldoc->karyawan_id;
        AuditLog::catat('delete', "Hapus dokumen legal karyawan ID {$karyawanId}", $legaldoc);
        $legaldoc->delete();

        return redirect()->route('admin.karyawan.show', $karyawanId)
                         ->with('success', 'Dokumen legal berhasil dihapus!')
                         ->withFragment('tab-legaldoc');
    }
}
