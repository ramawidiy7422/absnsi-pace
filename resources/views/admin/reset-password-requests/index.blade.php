@extends('layouts.admin')
@section('title', 'Permintaan Reset Password')
@section('page-title', 'Permintaan Reset Password')

@section('content')

<div style="background:linear-gradient(135deg,#c53030,#e53e3e);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;">
    <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-key"></i> Permintaan Reset Password</h4>
    <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Karyawan mengajukan lewat halaman "Lupa Password" — setujui untuk membuat password baru, lalu sampaikan langsung ke karyawan.</p>
</div>

@if(session('generated_password'))
<div class="card" style="margin-bottom:16px;border:2px solid #38a169;">
    <div class="card-body" style="padding:18px 22px;">
        <div style="font-size:12px;font-weight:700;color:#276749;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
            <i class="fas fa-check-circle"></i> Password Baru untuk {{ session('generated_for') }}
        </div>
        <div style="background:#1a202c;border-radius:10px;padding:14px 18px;display:inline-block;">
            <span id="genPw" style="font-family:monospace;font-size:22px;font-weight:800;color:#68d391;letter-spacing:3px;">{{ session('generated_password') }}</span>
        </div>
        <div style="font-size:12px;color:#718096;margin-top:10px;">
            <i class="fas fa-exclamation-triangle"></i> Catat/salin sekarang — sampaikan ke karyawan secara langsung (tatap muka/atasan). Halaman ini tidak menyimpan password ini lagi setelah direfresh.
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th style="padding:12px 16px;">Karyawan</th>
                    <th>Email/NIK Diajukan</th>
                    <th>Waktu Pengajuan</th>
                    <th>Status</th>
                    <th>Diproses Oleh</th>
                    <th style="text-align:right;padding-right:16px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600;">{{ $r->user->name }}</div>
                        <div style="font-size:11px;color:#a0aec0;">{{ $r->user->karyawan?->department?->name ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-size:12px;">{{ $r->email_submitted }}</div>
                        <div style="font-size:11px;color:#a0aec0;">NIK: {{ $r->nik_submitted }}</div>
                    </td>
                    <td style="font-size:12px;">{{ $r->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if($r->status === 'pending')
                            <span style="background:#fffbeb;color:#c05621;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;">Menunggu</span>
                        @elseif($r->status === 'disetujui')
                            <span style="background:#f0fff4;color:#276749;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;">Disetujui</span>
                        @else
                            <span style="background:#fff5f5;color:#c53030;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;">Ditolak</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#718096;">{{ $r->approver?->name ?? '-' }}</td>
                    <td style="text-align:right;padding-right:16px;">
                        @if($r->status === 'pending')
                        <form method="POST" action="{{ route('admin.reset-password-requests.approve', $r) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#38a169;color:white;border:none;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;"
                                onclick="return confirm('Setujui permintaan ini? Password baru akan dibuat.')">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.reset-password-requests.reject', $r) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#e53e3e;color:white;border:none;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;"
                                onclick="return confirm('Tolak permintaan ini?')">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </form>
                        @else
                        <span style="font-size:11px;color:#a0aec0;">{{ $r->approved_at?->format('d M Y H:i') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#a0aec0;">Belum ada permintaan reset password.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">
    {{ $requests->links() }}
</div>

@endsection
