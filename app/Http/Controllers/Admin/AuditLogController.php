<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->model,  fn($q) => $q->where('model', $request->model))
            ->when($request->search, fn($q) => $q->where('deskripsi','like',"%{$request->search}%"))
            ->orderByDesc('created_at')
            ->paginate(50)->withQueryString();
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $models  = AuditLog::distinct()->whereNotNull('model')->pluck('model')->sort()->values();
        return view('admin.audit-log.index', compact('logs','actions','models'));
    }

    public function exportCsv(Request $request)
    {
        $data = AuditLog::with('user')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->model,  fn($q) => $q->where('model', $request->model))
            ->when($request->search, fn($q) => $q->where('deskripsi','like',"%{$request->search}%"))
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Waktu', 'User', 'Aksi', 'Model', 'Deskripsi']);
            foreach ($data as $log) {
                fputcsv($out, [
                    $log->created_at?->format('d/m/Y H:i:s') ?? '-',
                    $log->user?->name ?? 'Sistem',
                    ucfirst($log->action ?? '-'),
                    $log->model ?? '-',
                    $log->deskripsi ?? '-',
                ]);
            }
            fclose($out);
        };

        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';
        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
