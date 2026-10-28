<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // superadmin lewat semua
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // Cek apakah user punya salah satu dari permission yang diminta
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
