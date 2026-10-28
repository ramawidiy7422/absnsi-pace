<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LogSistemController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bisaAuditLog     = $user->hasPermission('audit-log.lihat');
        $bisaLoginHistory = $user->hasPermission('login-history.lihat');

        $logs = $actions = $models = null;
        if ($bisaAuditLog) {
            $logs = AuditLog::with('user')
                ->when($request->action, fn($q) => $q->where('action', $request->action))
                ->when($request->model,  fn($q) => $q->where('model', $request->model))
                ->when($request->search, fn($q) => $q->where('deskripsi', 'like', "%{$request->search}%"))
                ->orderByDesc('created_at')
                ->paginate(50, ['*'], 'audit_page')->withQueryString();
            $actions = AuditLog::distinct()->pluck('action')->sort()->values();
            $models  = AuditLog::distinct()->whereNotNull('model')->pluck('model')->sort()->values();
        }

        $loginLogs = $users = $stats = null;
        if ($bisaLoginHistory) {
            $loginLogs = LoginHistory::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->status,  fn($q) => $q->where('status', $request->status))
                ->when($request->ip,      fn($q) => $q->where('ip_address', 'like', "%{$request->ip}%"))
                ->when($request->tanggal, fn($q) => $q->whereDate('login_at', $request->tanggal))
                ->orderByDesc('login_at')
                ->paginate(50, ['*'], 'login_page')->withQueryString();
            $users = User::orderBy('name')->get(['id', 'name', 'email']);
            $stats = [
                'total'   => LoginHistory::count(),
                'success' => LoginHistory::where('status', 'success')->count(),
                'failed'  => LoginHistory::where('status', 'failed')->count(),
                'today'   => LoginHistory::whereDate('login_at', today())->count(),
            ];
        }

        return view('admin.log-sistem.index', [
            'bisaAuditLog'     => $bisaAuditLog,
            'bisaLoginHistory' => $bisaLoginHistory,
            'logs'             => $logs,
            'actions'          => $actions,
            'models'           => $models,
            'loginLogs'        => $loginLogs,
            'users'            => $users,
            'stats'            => $stats,
        ]);
    }
}
