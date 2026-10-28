@extends('layouts.admin')
@section('title', 'Rekap Karyawan')
@section('page-title', 'Rekap Absensi Individual Karyawan')

@push('styles')
<style>
.rekap-header {
    background: linear-gradient(135deg, #553c9a, #805ad5);
    color: white; border-radius: 14px; padding: 20px 24px;
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px;
}
.rekap-header h4 { margin: 0; font-size: 18px; font-weight: 700; }
.rekap-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 13px; }

.filter-bar {
    background: white; border-radius: 12px; padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
    display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
}
.filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
.filter-bar label { font-size: 12px; font-weight: 600; color: #4a5568; }
.filter-bar select, .filter-bar input {
    padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: #2d3748;
}

.rekap-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rekap-table thead th {
    background: #f7fafc; padding: 11px 14px;
    text-align: center; font-weight: 700; color: #4a5568;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.rekap-table thead th:nth-child(1),
.rekap-table thead th:nth-child(2) { text-align: left; }
.rekap-table tbody td {
    padding: 11px 14px; border-bottom: 1px solid #f0f0f0;
    text-align: center; color: #4a5568;
}
.rekap-table tbody td:nth-child(1),
.rekap-table tbody td:nth-child(2) { text-align: left; }
.rekap-table tbody tr:hover { background: #f7fafc; }

.dept-row td {
    background: linear-gradient(90deg, #faf5ff, #f7fafc);
    font-weight: 700; color: #553c9a; font-size: 12px;
    padding: 7px 14px; border-bottom: 1px solid #e9d8fd;
    text-transform: uppercase; letter-spacing: 0.5px;
}

.val-hadir     { color: #276749; font-weight: 700; }
.val-terlambat { color: #c05621; font-weight: 700; }
.val-izin      { color: #2c5282; font-weight: 700; }
.val-sakit     { color: #97266d; font-weight: 700; }
.val-alpha     { color: #c53030; font-weight: 700; }
.val-zero      { color: #cbd5e0; }

.summary-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
.summary-item {
    background: white; border-radius: 10px; padding: 14px 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center;
}
.summary-item .sv { font-size: 26px; font-weight: 800; }
.summary-item .sl { font-size: 11px; color: #718096; margin-top: 2px; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="rekap-header">
    <div>
        <h4><i class="fas fa-user-check"></i> Rekap Individual Karyawan</h4>
        <p>Periode: {{ $namaBulan }}{{ $department_id ? ' &mdash; Dept: ' . ($departemenList->firstWhere('id', $department_id)?->name ?? '') : '' }}</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('admin.laporan.rekap-departemen', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-building"></i> Per Departemen
        </a>
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.laporan.rekap-karyawan.export-pdf', array_merge(['bulan'=>$bulan,'tahun'=>$tahun], $department_id ? ['department_id'=>$department_id] : [])) }}" class="btn btn-sm" style="background:rgba(220,53,69,0.85);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-file-pdf"></i> PDF Per Karyawan
        </a>
        @endif
        <a href="{{ route('admin.laporan.export-csv', array_merge(['bulan'=>$bulan,'tahun'=>$tahun], $department_id ? ['department_id'=>$department_id] : [])) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="{{ route('admin.laporan.rekap-bulanan', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-calendar-check"></i> Rekap Bulanan
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.laporan.rekap-karyawan') }}" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
        <div class="fg">
            <label>Bulan</label>
            <select name="bulan">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Tahun</label>
            <select name="tahun">
                @foreach(range(now()->year, now()->year - 3, -1) as $y)
                    <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Departemen</label>
            <select name="department_id">
                <option value="">Semua Departemen</option>
                @foreach($departemenList as $dept)
                    <option value="{{ $dept->id }}" {{ $dept->id == $department_id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Cari Karyawan</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIK..." style="min-width:180px;">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('admin.laporan.rekap-karyawan') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
    </form>
</div>

{{-- Summary --}}
@php
    $sumHadir     = $rekap->sum('hadir');
    $sumTerlambat = $rekap->sum('terlambat');
    $sumIzin      = $rekap->sum('izin');
    $sumSakit     = $rekap->sum('sakit');
    $sumAlpha     = $rekap->sum('alpha');
    $sumCuti      = $rekap->sum('cuti');
    $sumTotal     = $rekap->sum('total');
    $sumDurasi    = $rekap->sum('durasi_menit');
    $sumDurasiJam = intdiv((int)$sumDurasi, 60);
    $sumDurasiMnt = (int)$sumDurasi % 60;
@endphp
<div class="summary-grid">
    <div class="summary-item">
        <div class="sv" style="color:#276749;">{{ $sumHadir }}</div>
        <div class="sl"><i class="fas fa-check-circle" style="color:#48bb78;"></i> Total Hadir</div>
    </div>
    <div class="summary-item">
        <div class="sv" style="color:#c05621;">{{ $sumTerlambat }}</div>
        <div class="sl"><i class="fas fa-clock" style="color:#ed8936;"></i> Total Terlambat</div>
    </div>
    <div class="summary-item">
        <div class="sv" style="color:#2c5282;">{{ $sumIzin }}</div>
        <div class="sl"><i class="fas fa-calendar-minus" style="color:#63b3ed;"></i> Total Izin</div>
    </div>
    <div class="summary-item">
        <div class="sv" style="color:#c53030;">{{ $sumAlpha }}</div>
        <div class="sl"><i class="fas fa-times-circle" style="color:#fc8181;"></i> Total Alpha</div>
    </div>
    <div class="summary-item">
        <div class="sv" style="color:#553c9a;">{{ $sumCuti }}</div>
        <div class="sl"><i class="fas fa-calendar-check" style="color:#805ad5;"></i> Total Cuti</div>
    </div>
    <div class="summary-item">
        <div class="sv" style="color:#553c9a;">{{ $sumDurasiJam }}j</div>
        <div class="sl"><i class="fas fa-stopwatch" style="color:#805ad5;"></i> Total Durasi Kerja</div>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        @if($rekap->isEmpty())
            <div style="padding:40px; text-align:center; color:#718096;">
                <i class="fas fa-inbox" style="font-size:40px; margin-bottom:12px; display:block; opacity:0.3;"></i>
                <p>Tidak ada data untuk periode dan filter ini</p>
            </div>
        @else
        <div style="overflow-x:auto;">
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Karyawan</th>
                    <th>Shift</th>
                    <th style="color:#276749;"><i class="fas fa-check-circle"></i> Hadir</th>
                    <th style="color:#c05621;"><i class="fas fa-clock"></i> Terlambat</th>
                    <th style="color:#2c5282;"><i class="fas fa-calendar-minus"></i> Izin</th>
                    <th style="color:#97266d;"><i class="fas fa-heartbeat"></i> Sakit</th>
                    <th style="color:#c53030;"><i class="fas fa-times-circle"></i> Alpha</th>
                    <th style="color:#553c9a;"><i class="fas fa-calendar-check"></i> Cuti</th>
                    <th>Total</th>
                    <th><i class="fas fa-stopwatch"></i> Durasi</th>
                    <th>% Hadir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $prevDept = null; $no = 1; @endphp
                @foreach($rekap as $r)
                    @php $dept = $r['karyawan']->department?->name ?? $r['karyawan']->departemen; @endphp
                    @if($dept !== $prevDept)
                        <tr class="dept-row">
                            <td colspan="13"><i class="fas fa-building"></i> {{ $dept }}</td>
                        </tr>
                        @php $prevDept = $dept; @endphp
                    @endif
                    @php
                        $hadirTotal = $r['hadir'] + $r['terlambat'];
                        $pct        = $r['total'] > 0 ? round($hadirTotal / $r['total'] * 100) : 0;
                        $pctColor   = $pct >= 90 ? '#48bb78' : ($pct >= 75 ? '#ed8936' : '#fc8181');
                    @endphp
                    <tr>
                        <td style="color:#a0aec0; font-size:12px;">{{ $no++ }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                @if($r['karyawan']->foto_wajah)
                                    <img src="{{ asset('storage/' . $r['karyawan']->foto_wajah) }}"
                                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;"
                                         onerror="this.style.display='none'">
                                @else
                                    <div style="width:32px;height:32px;border-radius:50%;background:#faf5ff;display:flex;align-items:center;justify-content:center;font-weight:700;color:#553c9a;font-size:12px;">
                                        {{ strtoupper(substr($r['karyawan']->nama, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:600; color:#2d3748; font-size:13px;">{{ $r['karyawan']->nama }}</div>
                                    <div style="font-size:11px; color:#a0aec0;">{{ $r['karyawan']->nik }} &middot; {{ $r['karyawan']->jabatan ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:11px; color:#718096;">{{ $r['karyawan']->shift->nama_shift ?? '-' }}</td>
                        <td class="{{ $r['hadir'] > 0 ? 'val-hadir' : 'val-zero' }}">{{ $r['hadir'] ?: '-' }}</td>
                        <td class="{{ $r['terlambat'] > 0 ? 'val-terlambat' : 'val-zero' }}">{{ $r['terlambat'] ?: '-' }}</td>
                        <td class="{{ $r['izin'] > 0 ? 'val-izin' : 'val-zero' }}">{{ $r['izin'] ?: '-' }}</td>
                        <td class="{{ $r['sakit'] > 0 ? 'val-sakit' : 'val-zero' }}">{{ $r['sakit'] ?: '-' }}</td>
                        <td class="{{ $r['alpha'] > 0 ? 'val-alpha' : 'val-zero' }}">{{ $r['alpha'] ?: '-' }}</td>
                        <td class="{{ ($r['cuti'] ?? 0) > 0 ? '' : 'val-zero' }}" style="color:#553c9a;font-weight:700;">{{ ($r['cuti'] ?? 0) ?: '-' }}</td>
                        <td style="font-weight:600; color:#2d3748;">{{ $r['total'] ?: '-' }}</td>
                        <td style="font-size:12px; color:#553c9a; font-weight:600;">{{ $r['durasi_format'] }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="flex:1; background:#f0f2f5; border-radius:4px; height:6px; overflow:hidden; min-width:40px;">
                                    <div style="height:100%; width:{{ $pct }}%; background:{{ $pctColor }}; border-radius:4px;"></div>
                                </div>
                                <span style="font-size:11px; font-weight:600; color:{{ $pctColor }}; min-width:30px;">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.laporan.slip-pdf', $r['karyawan']) }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                               title="Download Slip PDF"
                               style="display:inline-flex;align-items:center;gap:4px;background:#fff5f5;color:#c53030;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:1px solid #fed7d7;">
                                <i class="fas fa-file-pdf"></i> Slip
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f7fafc; font-weight:700;">
                    <td colspan="3" style="padding:12px 14px; color:#4a5568;">TOTAL</td>
                    <td style="text-align:center; color:#276749;">{{ $sumHadir }}</td>
                    <td style="text-align:center; color:#c05621;">{{ $sumTerlambat }}</td>
                    <td style="text-align:center; color:#2c5282;">{{ $sumIzin }}</td>
                    <td style="text-align:center; color:#97266d;">{{ $sumSakit }}</td>
                    <td style="text-align:center; color:#c53030;">{{ $sumAlpha }}</td>
                    <td style="text-align:center; color:#2d3748;">{{ $sumTotal }}</td>
                    <td style="text-align:center; color:#553c9a;">{{ $sumDurasiJam }}j {{ $sumDurasiMnt }}m</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>
</div>

@endsection
