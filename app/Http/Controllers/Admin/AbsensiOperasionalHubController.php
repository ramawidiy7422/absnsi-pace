<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class AbsensiOperasionalHubController extends Controller
{
    public function index(Request $request)
    {
        $u = auth()->user();

        $totalAbsensiHariIni = null;
        $absensiTerbaru = collect();
        if ($u->hasPermission('absensi.lihat')) {
            $totalAbsensiHariIni = Absensi::whereDate('tanggal', today())->count();
            $absensiTerbaru = Absensi::with('karyawan')
                ->whereDate('tanggal', today())
                ->latest()->limit(4)->get();
        }

        $totalBelumAbsen = null;
        $belumAbsenTerbaru = collect();
        if ($u->hasPermission('absensi.lihat')) {
            $sudahAbsenIds = Absensi::whereDate('tanggal', today())->pluck('karyawan_id');
            $queryBelum = Karyawan::whereIn('status', ['kontrak', 'tetap', 'magang', 'training'])
                ->where('bebas_absen', false)
                ->whereNotIn('jabatan', Karyawan::JABATAN_BEBAS_ABSEN)
                ->whereNotIn('id', $sudahAbsenIds);
            $totalBelumAbsen = $queryBelum->count();
            $belumAbsenTerbaru = (clone $queryBelum)->orderBy('nama')->limit(4)->get();
        }

        $alphaBulanIni = null;
        if ($u->hasPermission('absensi.edit')) {
            $alphaBulanIni = Absensi::where('status', 'alpha')
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->count();
        }

        return view('admin.absensi-operasional.index', compact(
            'totalAbsensiHariIni', 'absensiTerbaru',
            'totalBelumAbsen', 'belumAbsenTerbaru',
            'alphaBulanIni'
        ));
    }
}
