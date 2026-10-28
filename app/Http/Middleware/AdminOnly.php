<?php

namespace App\Http\Middleware;

use App\Models\UserPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $role = $user?->role;

        if (in_array($role, ['superadmin', 'admin', 'hrd', 'supervisor', 'operator'])) {
            return $next($request);
        }

        // Role dasar (mis. karyawan) tetap boleh masuk area admin KALAU dia punya
        // minimal 1 izin aktif yang mengarah ke halaman admin — baik lewat
        // pengaturan ROLE (mis. admin mengaktifkan izin untuk seluruh role
        // "Karyawan" di halaman Role & Permission > Per Role) MAUPUN lewat
        // override PER USER (Role & Permission > Per User). Tanpa cek dua-duanya,
        // pengaturan yang sudah diaktifkan admin tidak akan pernah berlaku karena
        // ditolak di gerbang ini duluan. Permission spesifik tiap halaman tetap
        // dijaga middleware CheckPermission masing-masing rute — ini cuma gerbang
        // awal "boleh masuk /admin sama sekali atau tidak".
        $punyaAksesDariRole = $role && DB::table('role_permissions')
            ->where('role', $role)
            ->exists();

        $punyaOverrideGrant = $user && UserPermission::where('user_id', $user->id)
            ->where('mode', 'grant')
            ->exists();

        if ($punyaAksesDariRole || $punyaOverrideGrant) {
            return $next($request);
        }

        return redirect()->route('portal.index');
    }
}
