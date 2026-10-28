<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetRequestController extends Controller
{
    public function index()
    {
        $requests = PasswordResetRequest::with(['user.karyawan.department', 'approver'])
            ->latest()
            ->paginate(20);

        return view('admin.reset-password-requests.index', compact('requests'));
    }

    public function approve(Request $request, PasswordResetRequest $resetPasswordRequest)
    {
        if (Gate::denies('approve-reset-password', $resetPasswordRequest)) {
            return back()->with('error', 'Anda tidak berwenang menyetujui permintaan ini.');
        }

        if ($resetPasswordRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $newPassword = Str::random(8);
        $resetPasswordRequest->user->update(['password' => Hash::make($newPassword)]);

        $resetPasswordRequest->update([
            'status'           => 'disetujui',
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'catatan_approver' => $request->catatan_approver,
        ]);

        AuditLog::catat('approve', "Setujui reset password untuk {$resetPasswordRequest->user->name}", $resetPasswordRequest);

        return back()->with([
            'success'          => 'Permintaan disetujui. Sampaikan password baru berikut kepada karyawan secara langsung.',
            'generated_password' => $newPassword,
            'generated_for'       => $resetPasswordRequest->user->name,
        ]);
    }

    public function reject(Request $request, PasswordResetRequest $resetPasswordRequest)
    {
        if (Gate::denies('approve-reset-password', $resetPasswordRequest)) {
            return back()->with('error', 'Anda tidak berwenang menolak permintaan ini.');
        }

        if ($resetPasswordRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $resetPasswordRequest->update([
            'status'           => 'ditolak',
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'catatan_approver' => $request->catatan_approver,
        ]);

        AuditLog::catat('reject', "Tolak reset password untuk {$resetPasswordRequest->user->name}", $resetPasswordRequest);

        return back()->with('success', 'Permintaan reset password ditolak.');
    }
}
