@extends('layouts.admin')
@section('title','Audit Log')
@section('page-title','Audit Log')

@section('content')
<div class="card">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-history" style="color:#4299e1;"></i> Audit Log Sistem</h6>
        <a href="{{ route('admin.audit-log.export-csv', request()->query()) }}"
           style="padding:6px 14px;background:#2f855a;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>
    <div class="card-body" style="padding:12px;">
        {{-- Filter --}}
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <select name="action" class="form-select" style="width:auto;">
                <option value="">Semua Aksi</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action')==$a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
            <select name="model" class="form-select" style="width:auto;">
                <option value="">Semua Model</option>
                @foreach($models as $m)
                    <option value="{{ $m }}" {{ request('model')==$m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control" style="width:220px;" placeholder="Cari deskripsi..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            <a href="{{ route('admin.audit-log.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Waktu</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">User</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Aksi</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Deskripsi</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $actionColor = [
                            'create'  => '#38a169',
                            'update'  => '#d69e2e',
                            'delete'  => '#e53e3e',
                            'approve' => '#3182ce',
                            'reject'  => '#e53e3e',
                            'login'   => '#805ad5',
                            'logout'  => '#718096',
                        ];
                        $color = $actionColor[$log->action] ?? '#718096';
                    @endphp
                    <tr style="border-bottom:1px solid #f0f0f0;" onmouseenter="this.style.background='#f7fafc'" onmouseleave="this.style.background=''">
                        <td style="padding:10px 12px;color:#718096;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding:10px 12px;font-weight:600;color:#2d3748;">
                            {{ $log->user?->name ?? 'Sistem' }}
                            @if($log->user)
                            <div style="font-size:10px;color:#a0aec0;">{{ $log->user->role }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 12px;">
                            <span style="background:{{ $color }}22;color:{{ $color }};padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="padding:10px 12px;color:#4a5568;">
                            {{ $log->deskripsi }}
                            @if($log->model)
                            <span style="font-size:10px;color:#a0aec0;margin-left:4px;">[{{ $log->model }}#{{ $log->model_id }}]</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;color:#a0aec0;font-family:monospace;font-size:11px;">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-history" style="font-size:36px;opacity:0.2;display:block;margin-bottom:10px;"></i>
                            Belum ada aktivitas tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
