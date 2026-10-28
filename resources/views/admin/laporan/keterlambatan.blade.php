@extends('layouts.admin')
@section('title', 'Laporan Keterlambatan')
@section('page-title', 'Laporan Keterlambatan')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#c53030,#e53e3e);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-user-clock"></i> Laporan Keterlambatan</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">
            {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
            @if($department_id) &mdash; Dept. {{ $deptList->firstWhere('id', $department_id)?->name }} @endif
        </p>
    </div>
    <div style="display:flex;gap:8px;">
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.laporan.keterlambatan.pdf', request()->query()) }}" style="background:rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        @endif
        <a href="{{ route('admin.laporan.keterlambatan.csv', request()->query()) }}" style="background:rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.laporan.keterlambatan.export-excel', request()->query()) }}" style="background:rgba(39,103,73,0.8);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        @endif
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="min-width:130px;">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ $bulan==$m?'selected':'' }}>{{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="min-width:100px;">
                    @for($y=now()->year;$y>=now()->year-3;$y--)
                    <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="min-width:160px;">
                    <option value="">Semua Departemen</option>
                    @foreach($deptList as $d)
                    <option value="{{ $d->id }}" {{ $department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Min. Kejadian</label>
                <input type="number" name="min_kali" class="form-control" value="{{ $minKali }}" min="1" max="31" style="width:80px;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.laporan.keterlambatan') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    <div class="stat-card red" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $totalTerlambat }}</div>
                <div class="stat-label">Total Kejadian</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="stat-card yellow" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $karyawanTerlambat }}</div>
                <div class="stat-label">Karyawan Terlambat</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="stat-card blue" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $totalHadir }}</div>
                <div class="stat-label">Total Kehadiran</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="stat-card red" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $pctTerlambat }}%</div>
                <div class="stat-label">Tingkat Keterlambatan</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:16px;align-items:start;">

    {{-- Ranking --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-trophy" style="color:#c05621;"></i> Ranking Keterlambatan</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 12px;font-weight:700;color:#4a5568;">#</th>
                        <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Karyawan</th>
                        <th style="padding:10px 8px;text-align:center;font-weight:700;color:#4a5568;">Kali</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ranking as $r)
                    @php
                        $medals = ['🥇','🥈','🥉'];
                        $medal  = $medals[$loop->index] ?? '';
                        $maxKali= $ranking->first()?->jumlah_terlambat ?? 1;
                        $pct    = $maxKali > 0 ? round($r->jumlah_terlambat / $maxKali * 100) : 0;
                        $color  = $loop->index === 0 ? '#c53030' : ($loop->index === 1 ? '#c05621' : ($loop->index === 2 ? '#d69e2e' : '#718096'));
                    @endphp
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 12px;font-size:16px;">{{ $medal ?: $loop->iteration }}</td>
                        <td style="padding:10px 12px;">
                            <div style="font-weight:700;color:#2d3748;font-size:13px;">{{ $r->karyawan?->nama ?? '-' }}</div>
                            <small style="color:#a0aec0;">{{ $r->karyawan?->department?->name ?? $r->karyawan?->departemen ?? '' }}</small>
                            <div style="background:#e2e8f0;border-radius:99px;height:4px;margin-top:4px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:{{ $color }};height:100%;border-radius:99px;"></div>
                            </div>
                        </td>
                        <td style="padding:10px 8px;text-align:center;">
                            <span style="background:{{ $loop->index < 3 ? '#fff5f5' : '#f7fafc' }};color:{{ $color }};font-weight:800;font-size:16px;padding:4px 10px;border-radius:8px;">
                                {{ $r->jumlah_terlambat }}x
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:30px;color:#a0aec0;">
                        <i class="fas fa-check-circle" style="font-size:28px;display:block;margin-bottom:8px;color:#48bb78;"></i>
                        Tidak ada keterlambatan bulan ini!
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Keterlambatan --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-list-alt" style="color:#3182ce;"></i> Detail Keterlambatan</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Dept.</th>
                            <th style="text-align:center;">Jam Masuk</th>
                            <th style="text-align:center;">Shift</th>
                            <th style="text-align:center;">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detail as $a)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#2d3748;">{{ $a->tanggal->format('d/m/Y') }}</div>
                                <small style="color:#a0aec0;">{{ $a->tanggal->translatedFormat('l') }}</small>
                            </td>
                            <td>
                                <div style="font-weight:600;color:#2d3748;">{{ $a->karyawan?->nama ?? '-' }}</div>
                                <small style="color:#a0aec0;">{{ $a->karyawan?->nik ?? '' }}</small>
                            </td>
                            <td style="font-size:12px;color:#718096;">{{ $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-' }}</td>
                            <td style="text-align:center;">
                                <span style="background:#fff5f5;color:#c53030;font-weight:700;padding:3px 10px;border-radius:20px;font-size:12px;">
                                    {{ substr($a->jam_masuk,0,5) }}
                                </span>
                            </td>
                            <td style="text-align:center;font-size:12px;color:#718096;">
                                @if($a->shift)
                                {{ $a->shift->nama_shift }}<br>
                                <small>{{ substr($a->shift->jam_masuk,0,5) }}</small>
                                @else -
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $a->badge_class }}">
                                    {{ $a->has_alasan_terlambat ? 'Berizin' : 'Tanpa Ket.' }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:#718096;max-width:200px;">
                                @if($a->keterangan_terlambat)
                                    <span title="{{ $a->keterangan_terlambat }}">{{ \Str::limit($a->keterangan_terlambat, 60) }}</span>
                                @elseif(auth()->user()->hasPermission('absensi.edit'))
                                    <button onclick="bukaModalAlasan({{ $a->id }}, '{{ addslashes($a->karyawan?->nama) }}', '{{ $a->tanggal->format('d/m/Y') }}')"
                                            style="background:#fef3c7;color:#92400e;border:1px solid #f6d860;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;cursor:pointer;">
                                        <i class="fas fa-pencil-alt"></i> Isi Alasan
                                    </button>
                                @else
                                    <span style="color:#a0aec0;">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;padding:30px;color:#a0aec0;">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:12px 16px;border-top:1px solid #f0f0f0;">
                {{ $detail->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@if(auth()->user()->hasPermission('absensi.edit'))
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
                            style="background:#f7fafc;color:#4a5568;border:1px solid #e2e8f0;border-radius:20px;padding:4px 10px;font-size:11px;cursor:pointer;">
                        {{ $alasan }}
                    </button>
                    @endforeach
                </div>
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Keterangan Detail <span style="color:#e53e3e;">*</span></label>
                <textarea name="keterangan_terlambat" id="input-alasan-terlambat" class="form-control" rows="3"
                          placeholder="Tuliskan alasan keterlambatan karyawan..." required style="resize:vertical;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="tutupModalAlasan()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning" style="font-weight:700;"><i class="fas fa-save"></i> Simpan</button>
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
}
document.getElementById('modal-alasan-terlambat').addEventListener('click', function(e) {
    if (e.target === this) tutupModalAlasan();
});
</script>
@endpush
@endif

@endsection
