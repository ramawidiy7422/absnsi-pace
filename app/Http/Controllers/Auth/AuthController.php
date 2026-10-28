<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordResetRequestedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $history = LoginHistory::create([
                'user_id'    => auth()->id(),
                'email'      => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);
            session(['login_history_id' => $history->id, 'login_token' => uniqid()]);
            $role = auth()->user()->role;
            if (in_array($role, ['superadmin', 'admin', 'hrd', 'supervisor', 'operator'])) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('portal.index');
        }

        LoginHistory::create([
            'user_id'    => null,
            'email'      => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'failed',
        ]);

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function showLupaPassword()
    {
        return view('auth.lupa-password');
    }

    public function lupaPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nik'   => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'nik.required'   => 'ID Karyawan / NIK wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verifikasi: email harus ada & terhubung ke karyawan dengan NIK yang cocok
        if (!$user || !$user->karyawan || strtoupper(trim($user->karyawan->nik)) !== strtoupper(trim($request->nik))) {
            return back()->withInput()->withErrors(['email' => 'Email atau ID Karyawan tidak cocok.']);
        }

        // Tidak reset langsung — NIK bukan rahasia (tercetak di ID card/QR kiosk),
        // jadi permintaan harus disetujui superadmin/HRD sebelum password baru dibuat.
        $sudahAdaPending = PasswordResetRequest::where('user_id', $user->id)->where('status', 'pending')->exists();
        if (!$sudahAdaPending) {
            $req = PasswordResetRequest::create([
                'user_id'         => $user->id,
                'email_submitted' => $request->email,
                'nik_submitted'   => $request->nik,
                'ip_address'      => $request->ip(),
            ]);
            AuditLog::catat('create', "Pengajuan reset password untuk {$user->name} ({$user->email})", $req);
            User::whereIn('role', ['superadmin', 'hrd'])->get()
                ->each(fn ($u) => $u->notify(new PasswordResetRequestedNotification($req)));
        }

        return redirect()->route('lupa-password.sukses')->with(['reset_name' => $user->name]);
    }

    public function lupaPasswordSukses()
    {
        if (!session('reset_name')) {
            return redirect()->route('login');
        }
        return view('auth.lupa-password-sukses');
    }

    public function logout(Request $request)
    {
        $historyId = $request->session()->get('login_history_id');
        if ($historyId) {
            LoginHistory::where('id', $historyId)->update(['logout_at' => now()]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
