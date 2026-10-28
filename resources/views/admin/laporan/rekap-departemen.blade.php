@extends('layouts.admin')
@section('title', 'Rekap Per Departemen')
@section('page-title', 'Rekap Absensi Per Departemen')

@push('styles')
<style>
.rekap-header {
    background: linear-gradient(135deg, #276749, #48bb78);
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
.filter-bar select {
    padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: #2d3748; min-width: 120px;
}

.dept-card {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-bottom: 16px; overflow: hidden;
}
.dept-card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 20px;
    border-left: 4px solid #48bb78;
    background: #f0fff4;
}
.dept-card-header h6 { font-size: 15px; font-weight: 700; color: #276749; margin: 0; }
.dept-card-header small { font-size: 12px; color: #718096; }
.dept-card-body { padding: 16px 20px; }

.stat-row {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 12px;
}
@media (max-width: 900px) {
    .stat-row { grid-template-columns: repeat(3, 1fr); }
    .summary-cards { grid-template-columns: repeat(2, 1fr); }
    .dept-card-header { flex-wrap: wrap; gap: 6px; }
}
@media (max-width: 500px) {
    .stat-row { grid-template-columns: repeat(2, 1fr); }
    .summary-cards { grid-template-columns: 1fr 1fr; }
}
.stat-item { text-align: center; }
.stat-item .sv { font-size: 24px; font-weight: 800; line-height: 1; }
.stat-item .sl { font-size: 10px; color: #718096; margin-top: 3px; text-transform: uppercase; }

.progress-track {
    background: #e2e8f0; border-radius: 6px; height: 10px; overflow: hidden;
}
.progress-fill { height: 100%; border-radius: 6px; transition: width 0.5s ease; }

.summary-cards {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px;
}
.summary-card {
    background: white; border-radius: 10px; padding: 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center;
}
.summary-card .val { font-size: 28px; font-weight: 800; }
.summary-card .lbl { font-size: 11px; color: #718096; margin-top: 3px; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="rekap-header">
    <div>
        <h4><i class="fas fa-building"></i> Rekap Absensi Per Departemen</h4>
        <p>Periode: {{ $namaBulan }}</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('admin.laporan.rekap-bulanan', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-users"></i> Per Karyawan
        </a>
        <a href="{{ route('admin.laporan.rekap-karyawan', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-user-check"></i> Rekap Individu
        </a>
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.laporan.rekap-departemen.export-excel', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(33,150,83,0.8);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.laporan.rekap-departemen.export-csv', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="{{ route('admin.laporan.rekap-departemen.export-pdf', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:rgba(220,53,69,0.7);color:white;border:1px solid rgba(255,255,255,0.4);" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        @endif
    </div>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.laporan.rekap-departemen') }}" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
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
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('admin.laporan.rekap-departemen') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
    </form>
</div>

{{-- Summary Global --}}
@php
    $totalDept     = $rekapDept->count();
    $globalHadir   = $rekapDept->sum('hadir') + $rekapDept->sum('terlambat');
    $globalIzin    = $rekapDept->sum('izin') + $rekapDept->sum('sakit');
    $globalAlpha   = $rekapDept->sum('alpha');
    $globalTotal   = $rekapDept->sum('total');
    $globalPersen  = $globalTotal > 0 ? round($globalHadir / $globalTotal * 100) : 0;
@endphp
<div class="summary-cards">
    <div class="summary-card">
        <div class="val" style="color:#2b6cb0;">{{ $totalDept }}</div>
        <div class="lbl"><i class="fas fa-building" style="color:#4299e1;"></i> Departemen</div>
    </div>
    <div class="summary-card">
        <div class="val" style="color:#276749;">{{ $globalHadir }}</div>
        <div class="lbl"><i class="fas fa-check-circle" style="color:#48bb78;"></i> Total Hadir</div>
    </div>
    <div class="summary-card">
        <div class="val" style="color:#c05621;">{{ $globalAlpha }}</div>
        <div class="lbl"><i class="fas fa-times-circle" style="color:#fc8181;"></i> Total Alpha</div>
    </div>
    <div class="summary-card">
        <div class="val" style="color:{{ $globalPersen >= 90 ? '#276749' : ($globalPersen >= 70 ? '#c05621' : '#c53030') }};">{{ $globalPersen }}%</div>
        <div class="lbl"><i class="fas fa-chart-pie" style="color:#a0aec0;"></i> Rata-rata Kehadiran</div>
    </div>
</div>

{{-- Per Departemen --}}
@if($rekapDept->isEmpty())
    <div class="card">
        <div class="card-body" style="text-align:center; padding:50px; color:#718096;">
            <i class="fas fa-inbox" style="font-size:48px; display:block; margin-bottom:12px; opacity:0.3;"></i>
            <p>Tidak ada data departemen ditemukan</p>
        </div>
    </div>
@else
    @foreach($rekapDept as $rd)
    @php
        $barColor = $rd['persen_hadir'] >= 90 ? '#48bb78' : ($rd['persen_hadir'] >= 70 ? '#ed8936' : '#fc8181');
    @endphp
    <div class="dept-card">
        <div class="dept-card-header">
            <div>
                <h6><i class="fas fa-building"></i> {{ $rd['departemen'] }}</h6>
                <small>{{ $rd['jumlah_karyawan'] }} karyawan aktif &mdash; {{ $rd['total'] }} data absensi bulan ini</small>
            </div>
            <div style="text-align:right;">
                <div style="font-size:22px; font-weight:800; color:{{ $barColor }};">{{ $rd['persen_hadir'] }}%</div>
                <small style="font-size:10px; color:#718096;">Tingkat Kehadiran</small>
            </div>
        </div>
        <div class="dept-card-body">
            <div class="stat-row">
                <div class="stat-item">
                    <div class="sv" style="color:#276749;">{{ $rd['hadir'] }}</div>
                    <div class="sl">Hadir</div>
                </div>
                <div class="stat-item">
                    <div class="sv" style="color:#c05621;">{{ $rd['terlambat'] }}</div>
                    <div class="sl">Terlambat</div>
                </div>
                <div class="stat-item">
                    <div class="sv" style="color:#2b6cb0;">{{ $rd['izin'] }}</div>
                    <div class="sl">Izin</div>
                </div>
                <div class="stat-item">
                    <div class="sv" style="color:#97266d;">{{ $rd['sakit'] }}</div>
                    <div class="sl">Sakit</div>
                </div>
                <div class="stat-item">
                    <div class="sv" style="color:#c53030;">{{ $rd['alpha'] }}</div>
                    <div class="sl">Alpha</div>
                </div>
                <div class="stat-item">
                    <div class="sv" style="color:#4a5568;">{{ $rd['total'] }}</div>
                    <div class="sl">Total</div>
                </div>
            </div>
            <div style="margin-top:8px;">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:#718096; margin-bottom:4px;">
                    <span>Tingkat Kehadiran</span>
                    <span style="font-weight:700; color:{{ $barColor }};">{{ $rd['hadir'] + $rd['terlambat'] }} / {{ $rd['total'] }} hari</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width:{{ $rd['persen_hadir'] }}%; background:{{ $barColor }};"></div>
                </div>
            </div>
            <div style="margin-top:10px; text-align:right;">
                <a href="{{ route('admin.laporan.rekap-karyawan', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$rd['department_id']]) }}"
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-users"></i> Lihat Per Karyawan
                </a>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
