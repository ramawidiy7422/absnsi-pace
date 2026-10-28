<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginHistory::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->ip, fn($q) => $q->where('ip_address', 'like', "%{$request->ip}%"))
            ->when($request->tanggal, fn($q) => $q->whereDate('login_at', $request->tanggal));

        $logs = $query->orderByDesc('login_at')->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id','name','email']);

        $stats = [
            'total'   => LoginHistory::count(),
            'success' => LoginHistory::where('status','success')->count(),
            'failed'  => LoginHistory::where('status','failed')->count(),
            'today'   => LoginHistory::whereDate('login_at', today())->count(),
        ];

        return view('admin.login-history.index', compact('logs','users','stats'));
    }

    public function exportCsv(Request $request)
    {
        $data = LoginHistory::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->ip, fn($q) => $q->where('ip_address', 'like', "%{$request->ip}%"))
            ->when($request->tanggal, fn($q) => $q->whereDate('login_at', $request->tanggal))
            ->orderByDesc('login_at')
            ->limit(5000)
            ->get();

        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Waktu Login', 'User', 'Email', 'Status', 'IP Address', 'User Agent']);
            foreach ($data as $log) {
                fputcsv($out, [
                    $log->login_at?->format('d/m/Y H:i:s') ?? '-',
                    $log->user?->name ?? '-',
                    $log->user?->email ?? '-',
                    ucfirst($log->status ?? '-'),
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                ]);
            }
            fclose($out);
        };

        $filename = 'login_history_' . now()->format('Ymd_His') . '.csv';
        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
