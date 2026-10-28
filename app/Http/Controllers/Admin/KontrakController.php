<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KontrakController extends Controller
{
    public function index(Request $request)
    {
        $rentang    = (int) ($request->rentang ?? 30); // hari ke depan
        $departemen = $request->department_id;
        $status     = $request->status ?? 'semua'; // sudah_habis | akan_habis | semua

        $today = now()->startOfDay();

        $deptIds = $this->scopedDepartmentIds();

        $query = Karyawan::with(['department'])
            ->whereIn('status', ['kontrak', 'magang', 'training', 'outsourcing'])
            ->whereNotNull('tanggal_kontrak_berakhir')
            ->whereNull('tanggal_keluar');

        if ($deptIds !== null) {
            $query->whereIn('department_id', $deptIds ?: [0]);
        } elseif ($departemen) {
            $query->where('department_id', $departemen);
        }

        $karyawanList = $query->orderBy('tanggal_kontrak_berakhir')->get();

        // Kelompokkan
        $sudahHabis  = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->lt($today));
        $akanHabis30 = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->gte($today) && $k->tanggal_kontrak_berakhir->lte($today->copy()->addDays(30)));
        $akanHabis60 = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->gt($today->copy()->addDays(30)) && $k->tanggal_kontrak_berakhir->lte($today->copy()->addDays(60)));
        $akanHabis90 = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->gt($today->copy()->addDays(60)) && $k->tanggal_kontrak_berakhir->lte($today->copy()->addDays(90)));

        // Filter tampilan sesuai pilihan
        if ($status === 'sudah_habis') {
            $tampil = $sudahHabis;
        } elseif ($status === 'akan_habis') {
            $batas = $today->copy()->addDays($rentang);
            $tampil = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->gte($today) && $k->tanggal_kontrak_berakhir->lte($batas));
        } else {
            $batas = $today->copy()->addDays(90);
            $tampil = $karyawanList->filter(fn($k) => $k->tanggal_kontrak_berakhir->lte($batas));
        }

        $departemenList = $deptIds !== null
            ? Department::whereIn('id', $deptIds ?: [0])->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return view('admin.karyawan.kontrak', compact(
            'tampil', 'sudahHabis', 'akanHabis30', 'akanHabis60', 'akanHabis90',
            'departemenList', 'rentang', 'status', 'departemen', 'today'
        ));
    }
}
