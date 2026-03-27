@extends('layouts.admin')
@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-clipboard-list"></i> Daftar Absensi</h6>
        @if(in_array(auth()->user()->role, ['admin','hrd']))
        <a href="{{ route('admin.absensi.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Input Manual</a>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
            <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" style="max-width:180px;">
            <select name="status" class="form-select" style="max-width:150px;">
                <option value="">Semua Status</option>
                <option value="hadir" {{ request('status')=='hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="terlambat" {{ request('status')=='terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="izin" {{ request('status')=='izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ request('status')=='sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="alpha" {{ request('status')=='alpha' ? 'selected' : '' }}>Alpha</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Cari nama / NIK..." value="{{ request('search') }}" style="max-width:200px;">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary"><i class="fas fa-refresh"></i></a>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Shift</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th>Akurasi AI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $a->karyawan->nama ?? '-' }}</strong><br>
                            <small style="color:#718096;">{{ $a->karyawan->nik ?? '' }}</small>
                        </td>
                        <td>{{ $a->shift->nama_shift ?? '-' }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td>{{ $a->jam_keluar ?? '-' }}</td>
                        <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td>{{ $a->confidence_masuk ? $a->confidence_masuk . '%' : '-' }}</td>
                        <td>
                            @if(in_array(auth()->user()->role, ['admin','hrd']))
                            <a href="{{ route('admin.absensi.edit', $a) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(auth()->user()->role === 'admin')
                            <form method="POST" action="{{ route('admin.absensi.destroy', $a) }}" style="display:inline;" onsubmit="return confirm('Hapus data absensi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center; padding:30px; color:#718096;">Tidak ada data absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $absensi->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
