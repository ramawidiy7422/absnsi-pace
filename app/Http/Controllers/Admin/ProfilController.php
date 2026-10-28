<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employeshift;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        $adminRoles = ['superadmin', 'admin', 'hrd'];

        // Riwayat login hanya untuk admin/hrd
        $loginHistory = in_array($role, $adminRoles)
            ? LoginHistory::where('user_id', $user->id)->latest('login_at')->limit(5)->get()
            : collect();

        // Audit log hanya untuk superadmin/admin
        $auditLog = in_array($role, ['superadmin', 'admin'])
            ? AuditLog::where('user_id', $user->id)->latest()->limit(10)->get()
            : collect();

        $karyawan = $user->karyawan?->load('department');

        return view('admin.profil', compact('loginHistory', 'auditLog', 'karyawan', 'role'));
    }

    public function jadwalSaya(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        $bulan    = (int)($request->bulan ?? now()->month);
        $tahun    = (int)($request->tahun ?? now()->year);
        $jumlahHari = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $jadwal = $karyawan
            ? Employeshift::with('shift')
                ->where('karyawan_id', $karyawan->id)
                ->whereYear('date', $tahun)
                ->whereMonth('date', $bulan)
                ->get()
                ->keyBy(fn($j) => $j->date->format('Y-m-d'))
            : collect();

        return view('admin.profil-jadwal', compact('karyawan', 'jadwal', 'bulan', 'tahun', 'jumlahHari'));
    }

    public function updateNama(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Auth::user()->update(['name' => $request->name]);
        return back()->with('success', 'Nama berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:6|confirmed',
        ], [
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min'       => 'Password baru minimal 6 karakter.',
        ]);

        if (!Hash::check($request->password_lama, Auth::user()->password)) {
            return back()->with('error_pass', 'Password lama tidak sesuai.');
        }

        Auth::user()->update(['password' => Hash::make($request->password_baru)]);
        return back()->with('success_pass', 'Password berhasil diubah.');
    }
}
