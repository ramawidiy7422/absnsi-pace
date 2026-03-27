@extends('layouts.admin')
@section('title', 'Shift Kerja')
@section('page-title', 'Manajemen Shift Kerja')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-clock"></i> Daftar Shift</h6>
        @if(in_array(auth()->user()->role, ['admin','hrd']))
        <a href="{{ route('admin.shift.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Shift</a>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        <table>
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
                        @if(in_array(auth()->user()->role, ['admin','hrd']))
                        <a href="{{ route('admin.shift.edit', $shift) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        @endif
                        @if(auth()->user()->role === 'admin')
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
        </table>
    </div>
</div>
@endsection
