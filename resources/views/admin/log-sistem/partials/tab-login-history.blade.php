{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
    <div style="background:white;border-radius:12px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #4a5568;">
        <div style="font-size:28px;font-weight:800;color:#2d3748;">{{ number_format($stats['total']) }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;">Total Login</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #38a169;">
        <div style="font-size:28px;font-weight:800;color:#276749;">{{ number_format($stats['success']) }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;">Login Berhasil</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #e53e3e;">
        <div style="font-size:28px;font-weight:800;color:#c53030;">{{ number_format($stats['failed']) }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;">Login Gagal</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #3182ce;">
        <div style="font-size:28px;font-weight:800;color:#2b6cb0;">{{ number_format($stats['today']) }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;">Hari Ini</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="_tab" value="tab-login-history">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">User</label>
                <select name="user_id" class="form-select" style="padding:8px 12px;font-size:13px;min-width:160px;">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="success" {{ request('status')=='success'?'selected':'' }}>Berhasil</option>
                    <option value="failed" {{ request('status')=='failed'?'selected':'' }}>Gagal</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">IP Address</label>
                <input type="text" name="ip" value="{{ request('ip') }}" placeholder="cari IP..." class="form-control" style="padding:8px 12px;font-size:13px;width:130px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control" style="padding:8px 12px;font-size:13px;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.log-sistem.index') }}#tab-login-history" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-list" style="color:#4a5568;"></i> Log Aktivitas Login</h6>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:12px;color:#718096;">{{ $loginLogs->total() }} record</span>
            <a href="{{ route('admin.login-history.export-csv', request()->query()) }}"
               style="padding:5px 12px;background:#2f855a;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">#</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">User</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">IP Address</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Browser / OS</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#4a5568;">Status</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Waktu Login</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Waktu Logout</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#4a5568;">Durasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loginLogs as $i => $log)
                <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f7fafc'" onmouseout="this.style.background=''">
                    <td style="padding:10px 12px;color:#a0aec0;font-size:11px;">{{ $loginLogs->firstItem() + $i }}</td>
                    <td style="padding:10px 12px;">
                        @if($log->user)
                        <div style="font-weight:700;color:#2d3748;">{{ $log->user->name }}</div>
                        <div style="font-size:11px;color:#a0aec0;">{{ $log->email }}</div>
                        @else
                        <div style="font-weight:700;color:#c53030;">{{ $log->email }}</div>
                        <div style="font-size:10px;color:#fc8181;">User tidak ditemukan</div>
                        @endif
                    </td>
                    <td style="padding:10px 12px;">
                        <code style="background:#f7fafc;padding:2px 6px;border-radius:4px;font-size:11px;color:#2d3748;">{{ $log->ip_address ?? '-' }}</code>
                    </td>
                    <td style="padding:10px 12px;">
                        <div style="font-size:12px;color:#4a5568;">
                            <i class="fas fa-globe" style="color:#4299e1;"></i> {{ $log->browser }}
                            &nbsp;·&nbsp;
                            <i class="fas fa-desktop" style="color:#718096;"></i> {{ $log->os }}
                        </div>
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($log->status === 'success')
                        <span style="background:#c6f6d5;color:#276749;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;"><i class="fas fa-check"></i> Berhasil</span>
                        @else
                        <span style="background:#fed7e2;color:#c53030;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;"><i class="fas fa-times"></i> Gagal</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;">
                        <div style="font-size:12px;color:#2d3748;">{{ $log->login_at?->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#718096;">{{ $log->login_at?->format('H:i:s') }}</div>
                    </td>
                    <td style="padding:10px 12px;">
                        @if($log->logout_at)
                        <div style="font-size:12px;color:#2d3748;">{{ $log->logout_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#718096;">{{ $log->logout_at->format('H:i:s') }}</div>
                        @else
                        <span style="color:#a0aec0;font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($log->logout_at && $log->login_at)
                            @php
                                $dur = $log->login_at->diff($log->logout_at);
                                $durStr = $dur->h > 0 ? $dur->h.'j ' : '';
                                $durStr .= $dur->i.'m';
                            @endphp
                            <span style="font-size:11px;color:#553c9a;font-weight:600;">{{ $durStr }}</span>
                        @elseif($log->status === 'success')
                            <span style="font-size:11px;color:#38a169;">Aktif</span>
                        @else
                            <span style="color:#a0aec0;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:40px;text-align:center;color:#a0aec0;">
                        <i class="fas fa-history" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                        Belum ada riwayat login
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($loginLogs->hasPages())
    <div class="card-footer" style="padding:12px 16px;">
        {{ $loginLogs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
