<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormulirMeninggalkanKerja;
use App\Models\Leaverequest;
use App\Models\SuratDinas;
use Illuminate\Support\Facades\DB;

class FormulirHubController extends Controller
{
    public function index()
    {
        $u = auth()->user();

        // SPD: tidak ada middleware permission khusus di route resource-nya, semua admin/staff boleh lihat.
        $totalSpd = SuratDinas::count();
        $spdTerbaru = SuratDinas::orderByDesc('created_at')->limit(4)->get(['nomor_spd']);

        $totalFmk = $pendingFmk = $fmkTerbaru = null;
        if ($u->hasPermission('fmk.lihat')) {
            $totalFmk = FormulirMeninggalkanKerja::count();
            $pendingFmk = FormulirMeninggalkanKerja::where('status', 'menunggu')->count();
            $fmkTerbaru = FormulirMeninggalkanKerja::with('karyawan')
                ->orderByDesc('created_at')->limit(4)->get();
        }

        $totalCuti = $pendingCuti = $cutiTerbaru = null;
        if ($u->hasPermission('cuti.lihat')) {
            $totalCuti = Leaverequest::count();
            $pendingCuti = Leaverequest::whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])->count();
            $cutiTerbaru = Leaverequest::with('karyawan')
                ->orderByDesc('created_at')->limit(4)->get()
                ->map(function ($lr) {
                    $lr->label = $lr->type;
                    $lr->statusLabel = ucfirst(str_replace('pending_', 'Menunggu ', $lr->status));
                    [$lr->badgeBg, $lr->badgeColor] = match (true) {
                        $lr->status === 'disetujui' => ['#f0fff4', '#276749'],
                        $lr->status === 'ditolak'   => ['#fff5f5', '#c53030'],
                        default                     => ['#fffbeb', '#c05621'],
                    };
                    return $lr;
                });
        }

        $totalIzin = $pendingIzin = $izinTerbaru = null;
        if ($u->hasPermission('izin.lihat')) {
            $totalIzin = DB::table('izin')->count();
            $pendingIzin = DB::table('izin')->where('status', 'pending')->count();
            $izinTerbaru = DB::table('izin')
                ->join('karyawan', 'izin.karyawan_id', '=', 'karyawan.id')
                ->select('izin.*', 'karyawan.nama')
                ->orderByDesc('izin.created_at')->limit(4)->get()
                ->map(function ($row) {
                    $row->karyawan = (object) ['nama' => $row->nama];
                    $row->label = $row->jenis;
                    $row->statusLabel = ucfirst($row->status);
                    [$row->badgeBg, $row->badgeColor] = match (true) {
                        $row->status === 'approved' => ['#f0fff4', '#276749'],
                        $row->status === 'rejected' => ['#fff5f5', '#c53030'],
                        default                     => ['#fffbeb', '#c05621'],
                    };
                    return $row;
                });
        }

        $cutiIzinTerbaru = null;
        if ($cutiTerbaru !== null || $izinTerbaru !== null) {
            $cutiIzinTerbaru = collect($cutiTerbaru)->concat(collect($izinTerbaru))
                ->sortByDesc('created_at')->take(4)->values();
        }

        return view('admin.formulir.index', compact(
            'totalSpd','totalFmk','totalCuti','totalIzin',
            'pendingFmk','pendingCuti','pendingIzin',
            'spdTerbaru','fmkTerbaru','cutiIzinTerbaru'
        ));
    }
}
