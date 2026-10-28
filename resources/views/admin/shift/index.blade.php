@extends('layouts.admin')
@section('title', 'Shift Kerja')
@section('page-title', 'Manajemen Shift Kerja')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-clock"></i> Daftar Shift</h6>
        @if(auth()->user()->hasPermission('shift.kelola'))
        <a href="{{ route('admin.shift.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Shift</a>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive"><table>
            <thead>
                <tr>
                    <th>Nama Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Toleransi Keterlambatan</th>
                    <th>Jumlah Karyawan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td><strong>{{ $shift->nama_shift }}</strong></td>
                    <td><span style="color:#48bb78; font-weight:600;">{{ $shift->jam_masuk }}</span></td>
                    <td><span style="color:#ed8936; font-weight:600;">{{ $shift->jam_keluar }}</span></td>
                    <td>{{ $shift->toleransi_menit }} menit</td>
                    <td>
                        <span style="background:#bee3f8; color:#2c5282; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                            {{ $shift->karyawan_count }} karyawan
                        </span>
                    </td>
                    <td><span class="badge badge-{{ $shift->status }}">{{ ucfirst($shift->status) }}</span></td>
                    <td>
                        @if(auth()->user()->hasPermission('shift.kelola'))
                        <a href="{{ route('admin.shift.edit', $shift) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        @endif
                        @if(auth()->user()->hasPermission('shift.kelola'))
                        <form method="POST" action="{{ route('admin.shift.destroy', $shift) }}" style="display:inline;" onsubmit="return confirm('Nonaktifkan shift ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:30px; color:#718096;">Belum ada shift</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
</div>

{{-- Bulk Assign Shift per Departemen --}}
@if(auth()->user()->hasPermission('shift.kelola'))
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-layer-group" style="color:#7c3aed;"></i> Bulk Assign Shift per Departemen</h6>
    </div>
    <div class="card-body">
        <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
            Assign shift ke semua karyawan aktif dalam satu departemen sekaligus. Hanya karyawan yang belum punya shift yang akan diubah (centang "Timpa semua" untuk override semua).
        </p>
        <form method="POST" action="{{ route('admin.shift.bulk-assign') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Departemen</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Semua Departemen --</option>
                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Shift yang Diassign</label>
                    <select name="shift_id" class="form-select" required>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ substr($shift->jam_masuk,0,5) }}–{{ substr($shift->jam_keluar,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;">Opsi</label>
                    <div style="display:flex;align-items:center;gap:8px;height:38px;">
                        <input type="checkbox" name="overwrite" value="1" id="cb_overwrite" style="width:16px;height:16px;">
                        <label for="cb_overwrite" style="font-size:13px;font-weight:400;margin:0;cursor:pointer;">Timpa semua</label>
                    </div>
                </div>
                <div style="padding-bottom:1px;">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Assign shift ke departemen terpilih?')">
                        <i class="fas fa-check"></i> Assign
                    </button>
                </div>
            </div>
        </form>
        @if(session('shift_assign_result'))
        <div style="margin-top:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:13px;color:#166534;">
            <i class="fas fa-check-circle"></i> {{ session('shift_assign_result') }}
        </div>
        @endif
    </div>
</div>
@endif
@endsection
