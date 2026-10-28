@extends('layouts.admin')
@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-clipboard-list"></i> Daftar Absensi</h6>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if(auth()->user()->hasPermission('absensi.tambah'))
            <a href="{{ route('admin.absensi.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Input Manual</a>
            <a href="{{ route('admin.absensi.import') }}" class="btn btn-secondary"><i class="fas fa-file-upload"></i> Import CSV</a>
            @endif
            @if(auth()->user()->hasPermission('absensi.hapus'))
            <a href="{{ route('admin.absensi.alpha-generator') }}" class="btn btn-danger btn-sm" style="align-self:center;">
                <i class="fas fa-user-slash"></i> Alpha Generator
            </a>
            @endif
        </div>
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
                <option value="cuti" {{ request('status')=='cuti' ? 'selected' : '' }}>Cuti</option>
            </select>
            <select name="department_id" class="form-select" style="max-width:180px;">
                <option value="">Semua Departemen</option>
                @foreach($departemenList as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
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
                        <th class="hide-mobile">Departemen</th>
                        <th class="hide-mobile">Shift</th>
                        <th>Jam Masuk</th>
                        <th class="hide-mobile">Jam Keluar</th>
                        <th>Status</th>
                        <th class="hide-mobile">Akurasi AI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('admin.karyawan.show', $a->karyawan_id) }}#tab-absensi"
                               style="font-weight:700;color:#2d3748;text-decoration:none;">
                                {{ $a->karyawan?->nama ?? '-' }}
                            </a><br>
                            <small style="color:#718096;">{{ $a->karyawan?->nik ?? '' }}</small>
                        </td>
                        <td class="hide-mobile" style="font-size:12px;color:#4a5568;">{{ $a->karyawan?->department?->name ?? '-' }}</td>
                        <td class="hide-mobile">{{ $a->shift?->nama_shift ?? '-' }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td class="hide-mobile">{{ $a->jam_keluar ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $a->badge_class }}" title="{{ $a->keterangan_terlambat ?? '' }}">
                                @if($a->status === 'terlambat')<i class="fas fa-clock"></i> @endif
                                {{ $a->badge_label }}
                                @if($a->has_alasan_terlambat)<i class="fas fa-comment-alt" style="font-size:9px;margin-left:2px;"></i>@endif
                            </span>
                            @if($a->status === 'terlambat')
                                @if($a->has_alasan_terlambat)
                                <div style="font-size:10px;color:#92400e;margin-top:2px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $a->keterangan_terlambat }}">
                                    {{ $a->keterangan_terlambat }}
                                </div>
                                @elseif(auth()->user()->hasPermission('absensi.edit'))
                                <button onclick="bukaModalAlasan({{ $a->id }}, '{{ addslashes($a->karyawan?->nama) }}', '{{ $a->tanggal->format('d/m/Y') }}')"
                                        style="margin-top:3px;background:#fef3c7;color:#92400e;border:1px solid #f6d860;border-radius:6px;padding:2px 8px;font-size:10px;font-weight:700;cursor:pointer;display:block;">
                                    <i class="fas fa-pencil-alt"></i> Isi Alasan
                                </button>
                                @endif
                            @endif
                        </td>
                        <td class="hide-mobile">{{ $a->confidence_masuk ? $a->confidence_masuk . '%' : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.karyawan.show', $a->karyawan_id) }}#tab-absensi"
                               class="btn btn-sm btn-secondary" title="Lihat Detail Karyawan">
                                <i class="fas fa-external-link-alt"></i> Detail
                            </a>
                            @if(auth()->user()->hasPermission('absensi.edit'))
                            <a href="{{ route('admin.absensi.edit', $a) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(auth()->user()->hasPermission('absensi.hapus'))
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
@if(auth()->user()->hasPermission('absensi.edit'))
{{-- Modal Isi Alasan Terlambat (HRD) --}}
<div id="modal-alasan-terlambat" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1050;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:14px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#2d3748;"><i class="fas fa-user-clock" style="color:#c05621;margin-right:6px;"></i>Isi Alasan Keterlambatan</div>
                <div id="modal-alasan-sub" style="font-size:12px;color:#718096;margin-top:2px;"></div>
            </div>
            <button onclick="tutupModalAlasan()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#718096;">&times;</button>
        </div>
        <form id="form-alasan-terlambat" method="POST" style="padding:20px;">
            @csrf @method('PATCH')
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:6px;">Alasan Umum</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;">
                    @foreach(['Macet / Kondisi Lalu Lintas','Kendaraan Bermasalah','Urusan Keluarga Mendadak','Sakit Ringan','Hujan / Bencana Alam','Dinas Luar / Perjalanan Dinas','Lembur Hari Sebelumnya','Izin HRD'] as $alasan)
                    <button type="button" onclick="pilihAlasan('{{ $alasan }}')"
                            style="background:#f7fafc;color:#4a5568;border:1px solid #e2e8f0;border-radius:20px;padding:4px 10px;font-size:11px;cursor:pointer;transition:all .15s;"
                            onmouseover="this.style.background='#ebf8ff';this.style.borderColor='#90cdf4';this.style.color='#2b6cb0';"
                            onmouseout="this.style.background='#f7fafc';this.style.borderColor='#e2e8f0';this.style.color='#4a5568';">
                        {{ $alasan }}
                    </button>
                    @endforeach
                </div>
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Keterangan Detail <span style="color:#e53e3e;">*</span></label>
                <textarea name="keterangan_terlambat" id="input-alasan-terlambat" class="form-control" rows="3"
                          placeholder="Tuliskan alasan keterlambatan karyawan..." required
                          style="resize:vertical;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="tutupModalAlasan()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning" style="font-weight:700;">
                    <i class="fas fa-save"></i> Simpan Alasan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function bukaModalAlasan(absensiId, nama, tanggal) {
    var form = document.getElementById('form-alasan-terlambat');
    form.action = '{{ url("admin/absensi") }}/' + absensiId + '/alasan-terlambat';
    document.getElementById('modal-alasan-sub').textContent = nama + ' — ' + tanggal;
    document.getElementById('input-alasan-terlambat').value = '';
    document.getElementById('modal-alasan-terlambat').style.display = 'flex';
    setTimeout(() => document.getElementById('input-alasan-terlambat').focus(), 100);
}
function tutupModalAlasan() {
    document.getElementById('modal-alasan-terlambat').style.display = 'none';
}
function pilihAlasan(teks) {
    document.getElementById('input-alasan-terlambat').value = teks;
    document.getElementById('input-alasan-terlambat').focus();
}
document.getElementById('modal-alasan-terlambat').addEventListener('click', function(e) {
    if (e.target === this) tutupModalAlasan();
});
</script>
@endpush
@endif

@endsection
