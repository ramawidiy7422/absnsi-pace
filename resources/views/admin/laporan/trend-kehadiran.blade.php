@extends('layouts.admin')
@section('title', 'Trend Kehadiran')
@section('page-title', 'Laporan Trend Kehadiran')

@push('styles')
<style>
.trend-header {
    background: linear-gradient(135deg, #2b6cb0, #4299e1);
    color: white; border-radius: 14px; padding: 20px 24px;
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px;
}
.trend-header h4 { margin: 0; font-size: 18px; font-weight: 700; }
.trend-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 13px; }

.filter-bar {
    background: white; border-radius: 12px; padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
    display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
}
.filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
.filter-bar label { font-size: 12px; font-weight: 600; color: #4a5568; }
.filter-bar select {
    padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: #2d3748;
}

.card-section {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px; overflow: hidden;
}
.card-section .card-hd {
    padding: 14px 18px; border-bottom: 1px solid #e2e8f0;
    font-weight: 700; font-size: 14px; color: #2d3748;
    display: flex; justify-content: space-between; align-items: center;
}
.card-section .card-bd { padding: 16px 18px; }

.stat-boxes { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.stat-box {
    flex: 1; min-width: 140px; background: white; border-radius: 10px;
    padding: 14px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    border-left: 4px solid #4299e1;
}
.stat-box.green  { border-color: #48bb78; }
.stat-box.red    { border-color: #fc8181; }
.stat-box.orange { border-color: #f6ad55; }
.stat-box .val   { font-size: 26px; font-weight: 800; color: #2d3748; }
.stat-box .lbl   { font-size: 12px; color: #718096; margin-top: 2px; }
.stat-box .delta { font-size: 12px; font-weight: 600; }
.delta.up   { color: #38a169; }
.delta.down { color: #e53e3e; }
.delta.flat { color: #a0aec0; }

.chart-wrap { width: 100%; overflow-x: auto; }
.chart-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.chart-bar-row .lbl  { width: 80px; font-size: 12px; text-align: right; flex-shrink: 0; color: #4a5568; }
.chart-bar-row .bar  { flex: 1; height: 28px; background: #ebf8ff; border-radius: 6px; overflow: hidden; position: relative; }
.chart-bar-row .inner { height: 100%; border-radius: 6px; display: flex; align-items: center; padding-left: 8px; }
.chart-bar-row .pct  { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 700; color: #2d3748; }

.trend-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.trend-table th { background: #2b6cb0; color: white; padding: 10px 12px; text-align: center; }
.trend-table td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; text-align: center; }
.trend-table tr:last-child td { border-bottom: none; }
.trend-table tr:hover td { background: #f7fafc; }
.trend-table td:first-child { text-align: left; font-weight: 600; color: #2d3748; }

.dept-compare { display: flex; flex-direction: column; gap: 8px; }
.dept-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; background: #f7fafc; }
.dept-row .dept-name { width: 140px; font-size: 13px; font-weight: 600; color: #2d3748; flex-shrink: 0; }
.dept-row .bar-wrap  { flex: 1; height: 22px; background: #ebf8ff; border-radius: 4px; overflow: hidden; position: relative; }
.dept-row .bar-fill  { height: 100%; border-radius: 4px; background: #4299e1; }
.dept-row .pct-txt   { width: 60px; text-align: right; font-size: 13px; font-weight: 700; flex-shrink: 0; }
.dept-row .delta-tag { width: 60px; font-size: 11px; font-weight: 600; text-align: center; padding: 2px 6px; border-radius: 12px; flex-shrink: 0; }
.delta-tag.up   { background: #f0fff4; color: #276749; }
.delta-tag.down { background: #fff5f5; color: #c53030; }
.delta-tag.flat { background: #f7fafc; color: #a0aec0; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="trend-header">
    <div>
        <h4><i class="fas fa-chart-line"></i> Trend Kehadiran</h4>
        <p>Perbandingan tingkat kehadiran bulan ke bulan</p>
    </div>
    <div style="display:flex;gap:8px;">
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.laporan.trend-kehadiran.export-excel', ['bulan_range'=>$bulanRange,'department_id'=>$department_id]) }}"
           class="btn btn-sm" style="background:rgba(33,150,83,0.8);color:white;border:1px solid rgba(255,255,255,0.4);">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        @endif
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('admin.laporan.trend-kehadiran') }}">
<div class="filter-bar">
    <div class="fg">
        <label>Rentang Waktu</label>
        <select name="bulan_range">
            <option value="3"  {{ $bulanRange == 3  ? 'selected' : '' }}>3 Bulan Terakhir</option>
            <option value="6"  {{ $bulanRange == 6  ? 'selected' : '' }}>6 Bulan Terakhir</option>
            <option value="12" {{ $bulanRange == 12 ? 'selected' : '' }}>12 Bulan Terakhir</option>
        </select>
    </div>
    <div class="fg">
        <label>Departemen</label>
        <select name="department_id">
            <option value="">Semua Departemen</option>
            @foreach($departemenList as $d)
                <option value="{{ $d->id }}" {{ $department_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="fg">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary btn-sm" style="padding:8px 18px;">
            <i class="fas fa-search"></i> Tampilkan
        </button>
    </div>
</div>
</form>

@php
    $bulanTerakhir = $trendData[count($trendData)-1] ?? null;
    $bulanSebelum  = count($trendData) >= 2 ? $trendData[count($trendData)-2] : null;
    $avgPersen     = count($trendData) > 0 ? round(collect($trendData)->avg('persen')) : 0;
    $trendArah     = $bulanTerakhir && $bulanSebelum ? $bulanTerakhir['persen'] - $bulanSebelum['persen'] : 0;
@endphp

{{-- Stat boxes ringkasan --}}
@if($bulanTerakhir)
<div class="stat-boxes">
    <div class="stat-box green">
        <div class="val">{{ $bulanTerakhir['persen'] }}%</div>
        <div class="lbl">Kehadiran Bulan Ini</div>
        @if($bulanSebelum)
        <div class="delta {{ $trendArah > 0 ? 'up' : ($trendArah < 0 ? 'down' : 'flat') }}">
            {{ $trendArah > 0 ? '▲' : ($trendArah < 0 ? '▼' : '—') }} {{ abs($trendArah) }}% vs bulan lalu
        </div>
        @endif
    </div>
    <div class="stat-box">
        <div class="val">{{ $avgPersen }}%</div>
        <div class="lbl">Rata-rata {{ $bulanRange }} Bulan</div>
        <div class="delta flat">Rata-rata kehadiran</div>
    </div>
    <div class="stat-box orange">
        <div class="val">{{ $bulanTerakhir['terlambat'] }}</div>
        <div class="lbl">Terlambat Bulan Ini</div>
        @if($bulanSebelum)
        @php $deltaLambat = $bulanTerakhir['terlambat'] - $bulanSebelum['terlambat']; @endphp
        <div class="delta {{ $deltaLambat < 0 ? 'up' : ($deltaLambat > 0 ? 'down' : 'flat') }}">
            {{ $deltaLambat > 0 ? '▲' : ($deltaLambat < 0 ? '▼' : '—') }} {{ abs($deltaLambat) }} vs bulan lalu
        </div>
        @endif
    </div>
    <div class="stat-box red">
        <div class="val">{{ $bulanTerakhir['alpha'] }}</div>
        <div class="lbl">Alpha Bulan Ini</div>
        @if($bulanSebelum)
        @php $deltaAlpha = $bulanTerakhir['alpha'] - $bulanSebelum['alpha']; @endphp
        <div class="delta {{ $deltaAlpha < 0 ? 'up' : ($deltaAlpha > 0 ? 'down' : 'flat') }}">
            {{ $deltaAlpha > 0 ? '▲' : ($deltaAlpha < 0 ? '▼' : '—') }} {{ abs($deltaAlpha) }} vs bulan lalu
        </div>
        @endif
    </div>
</div>
@endif

{{-- Grafik batang horizontal --}}
<div class="card-section">
    <div class="card-hd">
        <span><i class="fas fa-chart-bar" style="color:#4299e1;"></i> Grafik Kehadiran per Bulan</span>
    </div>
    <div class="card-bd">
        <div class="chart-wrap">
            @foreach($trendData as $d)
            @php $pct = $d['persen']; $color = $pct >= 90 ? '#48bb78' : ($pct >= 75 ? '#f6ad55' : '#fc8181'); @endphp
            <div class="chart-bar-row">
                <span class="lbl">{{ $d['label'] }}</span>
                <div class="bar">
                    <div class="inner" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                    <span class="pct">{{ $pct }}%</span>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:10px;font-size:11px;color:#a0aec0;display:flex;gap:14px;">
            <span style="color:#48bb78;">&#9632; ≥ 90% Baik</span>
            <span style="color:#f6ad55;">&#9632; 75–89% Perlu Perhatian</span>
            <span style="color:#fc8181;">&#9632; &lt; 75% Kritis</span>
        </div>
    </div>
</div>

{{-- Tabel detail bulanan --}}
<div class="card-section">
    <div class="card-hd">
        <span><i class="fas fa-table" style="color:#4299e1;"></i> Detail per Bulan</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="trend-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Bulan</th>
                    <th>Hadir</th>
                    <th>Terlambat</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Alpha</th>
                    <th>Total</th>
                    <th>% Hadir</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trendData as $i => $d)
                @php
                    $prev = $i > 0 ? $trendData[$i-1] : null;
                    $delta = $prev ? $d['persen'] - $prev['persen'] : null;
                    $bgRow = $d['persen'] >= 90 ? '#f0fff4' : ($d['persen'] < 75 ? '#fff5f5' : '');
                @endphp
                <tr style="{{ $bgRow ? 'background:'.$bgRow : '' }}">
                    <td>{{ $d['label'] }}</td>
                    <td>{{ $d['hadir'] }}</td>
                    <td>{{ $d['terlambat'] }}</td>
                    <td>{{ $d['izin'] }}</td>
                    <td>{{ $d['sakit'] }}</td>
                    <td>{{ $d['alpha'] }}</td>
                    <td>{{ $d['total'] }}</td>
                    <td>
                        <strong style="color:{{ $d['persen'] >= 90 ? '#276749' : ($d['persen'] < 75 ? '#c53030' : '#c05621') }}">
                            {{ $d['persen'] }}%
                        </strong>
                    </td>
                    <td>
                        @if($delta !== null)
                            @if($delta > 0)
                                <span style="color:#38a169;font-weight:700;">▲ {{ $delta }}%</span>
                            @elseif($delta < 0)
                                <span style="color:#e53e3e;font-weight:700;">▼ {{ abs($delta) }}%</span>
                            @else
                                <span style="color:#a0aec0;">—</span>
                            @endif
                        @else
                            <span style="color:#a0aec0;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Perbandingan antar departemen --}}
@if($trendDept->isNotEmpty())
<div class="card-section">
    <div class="card-hd">
        <span><i class="fas fa-building" style="color:#4299e1;"></i> Perbandingan Kehadiran Antar Departemen (Bulan Ini vs Lalu)</span>
    </div>
    <div class="card-bd">
        <div class="dept-compare">
            @foreach($trendDept->sortByDesc('persen_now') as $dept)
            @php
                $delta = $dept['delta'];
                $cls   = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');
                $icon  = $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '—');
                $barColor = $dept['persen_now'] >= 90 ? '#48bb78' : ($dept['persen_now'] < 75 ? '#fc8181' : '#f6ad55');
            @endphp
            <div class="dept-row">
                <span class="dept-name">{{ $dept['nama'] }}</span>
                <div class="bar-wrap">
                    <div class="bar-fill" style="width:{{ $dept['persen_now'] }}%;background:{{ $barColor }};"></div>
                </div>
                <span class="pct-txt" style="color:{{ $barColor }};">{{ $dept['persen_now'] }}%</span>
                <span class="delta-tag {{ $cls }}">{{ $icon }} {{ abs($delta) }}%</span>
            </div>
            @endforeach
        </div>
        <p style="font-size:11px;color:#a0aec0;margin-top:10px;">
            <i class="fas fa-info-circle"></i> Kolom trend menunjukkan perubahan % kehadiran dibanding bulan sebelumnya.
            <span style="color:#276749;">▲</span> naik, <span style="color:#c53030;">▼</span> turun.
        </p>
    </div>
</div>
@endif

@endsection
