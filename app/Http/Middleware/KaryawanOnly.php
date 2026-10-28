<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KaryawanOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        $role = $request->user()?->role;
        if (in_array($role, ['superadmin', 'admin', 'hrd', 'supervisor', 'operator'])) {
            return redirect()->route('admin.dashboard');
        }
        return $next($request);
    }
}
