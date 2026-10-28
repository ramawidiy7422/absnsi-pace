<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users             = User::with('karyawan')->orderBy('role')->orderBy('name')->get();
        $karyawan          = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])->orderBy('nama')->get();
        $karyawanTanpaAkun = Karyawan::whereIn('status', ['kontrak','tetap','magang','training','outsourcing','harian'])
            ->whereDoesntHave('user')
            ->with('department')
            ->orderBy('nama')
            ->get();
        return view('admin.user.index', compact('users', 'karyawan', 'karyawanTanpaAkun'));
    }

    // Hanya superadmin boleh membuat/mengubah akun ke role superadmin/admin —
    // cegah role admin (pemegang users.kelola) melakukan privilege escalation.
    private function assertRoleAssignable(string $role): void
    {
        if (in_array($role, ['superadmin', 'admin']) && auth()->user()->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat menetapkan role ini.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:superadmin,admin,hrd,supervisor,karyawan,operator',
        ]);

        $this->assertRoleAssignable($request->role);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'karyawan_id' => $request->karyawan_id ?: null,
        ]);
        AuditLog::catat('create', "Tambah user: {$user->name} ({$user->email}), role: {$user->role}", $user);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => 'required|in:superadmin,admin,hrd,supervisor,karyawan,operator',
        ]);

        $this->assertRoleAssignable($request->role);
        if ($user->role !== $request->role) {
            $this->assertRoleAssignable($user->role);
        }

        $oldRole = $user->role;
        $user->update([
            'name'        => $request->name,
            'email'       => $request->email,
            'role'        => $request->role,
            'karyawan_id' => $request->karyawan_id ?: null,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $roleInfo = $oldRole !== $request->role ? " (role: {$oldRole} → {$request->role})" : '';
        AuditLog::catat('update', "Update user: {$user->name} ({$user->email}){$roleInfo}", $user);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);
        AuditLog::catat('update', "Reset password user {$user->name} ({$user->email})", $user);

        return back()->with('success', "Password {$user->name} berhasil direset.");
    }

    public function resetPasswordRandom(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mereset password akun sendiri.');
        }

        $newPassword = Str::random(10);
        $user->update(['password' => Hash::make($newPassword)]);
        AuditLog::catat('update', "Reset password acak user {$user->name} ({$user->email})", $user);

        return back()
            ->with('success', "Password {$user->name} berhasil direset secara acak.")
            ->with('new_password', $newPassword)
            ->with('new_password_user', $user->name);
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'karyawan_ids'   => 'required|array|min:1',
            'karyawan_ids.*' => 'exists:karyawan,id',
            'role'           => 'required|in:karyawan,operator,supervisor',
            'password_type'  => 'required|in:nik,random,custom',
            'custom_password'=> 'required_if:password_type,custom|min:6',
        ]);

        $karyawanList = Karyawan::whereIn('id', $request->karyawan_ids)
            ->whereDoesntHave('user')
            ->get();

        if ($karyawanList->isEmpty()) {
            return back()->with('error', 'Semua karyawan yang dipilih sudah memiliki akun.');
        }

        $created  = 0;
        $skipped  = 0;
        $results  = [];

        foreach ($karyawanList as $k) {
            $email = $k->email ?: strtolower(str_replace(' ', '.', $k->nama)) . '.' . $k->nik . '@pace.internal';

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $rawPassword = match($request->password_type) {
                'nik'    => $k->nik,
                'random' => Str::random(8),
                'custom' => $request->custom_password,
            };

            $user = User::create([
                'name'        => $k->nama,
                'email'       => $email,
                'password'    => Hash::make($rawPassword),
                'role'        => $request->role,
                'karyawan_id' => $k->id,
            ]);

            $results[] = ['nama' => $k->nama, 'nik' => $k->nik, 'email' => $email, 'password' => $rawPassword];
            $created++;
        }

        AuditLog::catat('create', "Bulk create {$created} akun user karyawan (role: {$request->role})");

        return back()
            ->with('success', "Berhasil membuat {$created} akun." . ($skipped ? " {$skipped} dilewati (email duplikat)." : ''))
            ->with('bulk_results', $results);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        AuditLog::catat('delete', "Hapus user {$user->name} ({$user->email})", $user);
        return back()->with('success', 'User berhasil dihapus.');
    }
}
