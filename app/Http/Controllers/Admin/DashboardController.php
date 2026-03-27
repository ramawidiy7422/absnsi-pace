<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalKaryawan    = Karyawan::where('status', 'aktif')->count();
        $hadirHariIni     = Absensi::whereDate('tanggal', today())->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatHariIni = Absensi::whereDate('tanggal', today())->where('status', 'terlambat')->count();
        $absenHariIni     = Absensi::whereDate('tanggal', today())->whereIn('status', ['izin', 'sakit', 'alpha'])->count();
        $belumAbsen       = max(0, $totalKaryawan - $hadirHariIni - $absenHariIni);

        // ── Grafik 7 hari terakhir ──────────────────────────────────────────
        $grafik7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);
            $grafik7Hari[] = [
                'label'     => $tgl->format('d/m'),
                'hadir'     => Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count(),
                'terlambat' => Absensi::whereDate('tanggal', $tgl)->where('status', 'terlambat')->count(),
                'alpha'     => Absensi::whereDate('tanggal', $tgl)->whereIn('status', ['alpha', 'izin', 'sakit'])->count(),
            ];
        }

        // ── Status pie chart hari ini ───────────────────────────────────────
        $pieData = [
            'hadir'     => Absensi::whereDate('tanggal', today())->where('status', 'hadir')->count(),
            'terlambat' => $terlambatHariIni,
            'izin'      => Absensi::whereDate('tanggal', today())->where('status', 'izin')->count(),
            'sakit'     => Absensi::whereDate('tanggal', today())->where('status', 'sakit')->count(),
            'alpha'     => Absensi::whereDate('tanggal', today())->where('status', 'alpha')->count(),
            'belum'     => $belumAbsen,
        ];

        // ── Alert: karyawan terlambat hari ini ─────────────────────────────
        $karyawanTerlambat = Absensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->where('status', 'terlambat')
            ->orderBy('jam_masuk')
            ->get();

        // ── Alert: karyawan belum absen ────────────────────────────────────
        $sudahAbsenIds = Absensi::whereDate('tanggal', today())
            ->pluck('karyawan_id')->toArray();
        $belumAbsenList = Karyawan::where('status', 'aktif')
            ->whereNotIn('id', $sudahAbsenIds)
            ->orderBy('departemen')
            ->orderBy('nama')
            ->get();

        // ── Absensi terbaru hari ini ───────────────────────────────────────
        $absensiTerbaru = Absensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan', 'hadirHariIni', 'terlambatHariIni',
            'absenHariIni', 'belumAbsen',
            'grafik7Hari', 'pieData',
            'karyawanTerlambat', 'belumAbsenList',
            'absensiTerbaru'
        ));
    }
}
