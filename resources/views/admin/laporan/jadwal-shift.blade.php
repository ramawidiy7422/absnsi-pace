@extends('layouts.admin')
@section('title', 'Jadwal Shift')
@section('page-title', 'Jadwal Shift')

@push('styles')
<style>
/* ── Header ─────────────────────────────────────────────────── */
.js-header {
    background: linear-gradient(135deg, #553c9a, #805ad5);
    color: white; border-radius: 14px; padding: 20px 24px;
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
}
.js-header h4 { margin: 0; font-size: 18px; font-weight: 700; }
.js-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 13px; }

/* ── Tab switch ─────────────────────────────────────────────── */
.view-tabs {
    display: flex; gap: 4px; background: rgba(255,255,255,0.15);
    border-radius: 10px; padding: 4px;
}
.view-tab {
    padding: 7px 18px; border-radius: 7px; font-size: 12px; font-weight: 700;
    border: none; cursor: pointer; color: rgba(255,255,255,0.7);
    background: transparent; transition: all .2s;
}
.view-tab.active { background: white; color: #553c9a; }

/* ── Filter bar ─────────────────────────────────────────────── */
.filter-bar {
    background: white; border-radius: 12px; padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
    display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
}
.filter-bar .fg { display: flex; flex-direction: column; gap: 4px; }
.filter-bar label { font-size: 12px; font-weight: 600; color: #4a5568; }
.filter-bar select, .filter-bar input {
    padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: 13px; color: #2d3748; min-width: 140px;
}

/* ══ VIEW 1: GRID SEMUA KARYAWAN ════════════════════════════ */
.jadwal-table {
    width: 100%; border-collapse: collapse; font-size: 12px;
    table-layout: fixed;
}
.jadwal-table thead th {
    background: #f7fafc; padding: 8px 4px;
    text-align: center; font-weight: 700; color: #4a5568;
    border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    position: sticky; top: 0; z-index: 2;
}
.jadwal-table thead th.col-no   { width: 34px; }
.jadwal-table thead th.col-nama { width: 150px; text-align: left; padding-left: 10px; }
.jadwal-table thead th.col-dept { width: 110px; text-align: left; }
.jadwal-table thead th.col-day  { width: 36px; }
.jadwal-table tbody td {
    padding: 6px 3px; border-bottom: 1px solid #f0f0f0;
    text-align: center; color: #4a5568; vertical-align: middle;
}
.jadwal-table tbody td.col-nama {
    text-align: left; padding-left: 10px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    font-weight: 600; color: #2d3748;
}
.jadwal-table tbody td.col-dept {
    text-align: left; font-size: 11px; color: #718096;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.jadwal-table tbody tr:hover { background: #f7fafc; cursor: pointer; }
.dept-row td {
    background: linear-gradient(90deg, #f3e8ff, #f7fafc);
    font-weight: 700; color: #553c9a; font-size: 11px;
    padding: 6px 10px; border-bottom: 1px solid #e9d8fd;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.shift-badge {
    display: inline-block; width: 22px; height: 22px; line-height: 22px;
    border-radius: 5px; font-size: 10px; font-weight: 700;
    text-align: center; color: white; cursor: default;
}
.shift-badge.clickable { cursor: pointer; }
.shift-pagi   { background: #3182ce; }
.shift-siang  { background: #dd6b20; }
.shift-malam  { background: #6b46c1; }
.shift-normal { background: #38a169; }
.shift-other  { background: #718096; }
.day-weekend  {
    background: #fee2e2 !important;
    color: #dc2626 !important;
    font-weight: 700 !important;
    border-left: 2px solid #dc2626 !important;
    border-right: 2px solid #dc2626 !important;
}
.table-card {
    background: white; border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); overflow: hidden;
}
.table-responsive { overflow-x: auto; }

/* ══ VIEW 2: KALENDER PER KARYAWAN ═════════════════════════ */
.karyawan-bar {
    background: linear-gradient(135deg, #ebf8ff, #f0fff4);
    border: 1.5px solid #bee3f8; border-radius: 14px;
    padding: 16px 22px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
}
.karyawan-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #4299e1, #667eea);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 800; color: white; flex-shrink: 0;
}
.chip {
    padding: 3px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; border: 1px solid;
}
.chip-blue  { background: rgba(66,153,225,0.15); color: #2b6cb0; border-color: rgba(66,153,225,0.3); }
.chip-green { background: rgba(72,187,120,0.15); color: #276749; border-color: rgba(72,187,120,0.3); }
.chip-orange{ background: rgba(237,137,54,0.15);  color: #c05621; border-color: rgba(237,137,54,0.3); }

.stat-row {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;
}
.stat-box {
    background: white; border-radius: 12px; padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center;
}
.stat-box .stat-num   { font-size: 26px; font-weight: 800; }
.stat-box .stat-label { font-size: 12px; color: #718096; margin-top: 4px; }

.cal-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 20px;
}
.cal-header {
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 10px;
    font-weight: 700; color: #2d3748; font-size: 15px;
}
.cal-body { padding: 20px; }
.cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;
}
.cal-day-label {
    text-align: center; font-size: 11px; font-weight: 700;
    color: #718096; padding: 6px 0; text-transform: uppercase; letter-spacing: 0.5px;
}
.cal-cell {
    border-radius: 10px; padding: 8px 6px; text-align: center;
    min-height: 78px; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 3px;
    border: 1px solid transparent; position: relative;
}
.cal-cell.is-today    { border-width: 2px; border-color: #4299e1; background: #ebf8ff !important; }
.cal-cell.is-past     { opacity: 0.65; }
.cal-cell.is-khusus   { outline: 2.5px solid #ed8936; outline-offset: 1px; }
.cal-cell.is-weekend  { background: #fee2e2 !important; border-color: #dc2626; border-width: 1.5px; }
.cal-cell.shift-pagi  { background: #ebf8ff; border-color: #bee3f8; }
.cal-cell.shift-siang { background: #fffbeb; border-color: #fbd38d; }
.cal-cell.shift-malam { background: #f3e8ff; border-color: #e9d8fd; }
.cal-cell.shift-normal{ background: #f0fff4; border-color: #9ae6b4; }
.cal-cell.clickable   { cursor: pointer; }
.cal-cell.clickable:hover { filter: brightness(0.95); }

.cal-date { font-size: 13px; font-weight: 600; color: #2d3748; }
.cal-date.today   { font-weight: 800; color: #2b6cb0; }
.cal-date.weekend { color: #e53e3e; font-weight: 700; }
.cal-today-label  { font-size: 8px; font-weight: 700; color: #4299e1; text-transform: uppercase; }
.cal-shift-badge  { font-size: 9px; font-weight: 700; border-radius: 6px; padding: 1px 7px; border: 1px solid; }
.badge-pagi   { color: #3182ce; background: #ebf8ff; border-color: #bee3f8; }
.badge-siang  { color: #d69e2e; background: #fffbeb; border-color: #fbd38d; }
.badge-malam  { color: #6b46c1; background: #f3e8ff; border-color: #e9d8fd; }
.badge-normal { color: #38a169; background: #f0fff4; border-color: #9ae6b4; }
.badge-other  { color: #718096; background: #f7fafc; border-color: #e2e8f0; }
.cal-time         { font-size: 9px; color: #718096; }
.cal-khusus-label { font-size: 8px; color: #c05621; font-weight: 700; }

.legend-bar {
    display: flex; gap: 14px; flex-wrap: wrap; align-items: center;
    margin-top: 16px;
}

/* Khusus table */
.khusus-card { background: white; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
.khusus-header {
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    font-weight: 700; color: #2d3748; font-size: 14px;
    display: flex; align-items: center; gap: 8px;
}

@media (max-width: 640px) {
    .stat-row { grid-template-columns: repeat(2, 1fr); }
    .cal-grid { gap: 4px; }
    .cal-cell { min-height: 58px; padding: 4px 2px; }
    .cal-shift-badge { display: none; }
    .view-tabs { flex-wrap: wrap; }
}
/* Select2 override agar sesuai desain */
.select2-container--default .select2-selection--single {
    height: 36px; border: 1px solid #e2e8f0; border-radius: 8px;
    display: flex; align-items: center; padding: 0 10px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    font-size: 13px; color: #2d3748; line-height: 34px; padding: 0;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px; right: 8px;
}
.select2-container--default .select2-results__option--highlighted {
    background: #553c9a !important;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 13px;
}
.select2-dropdown { border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
.select2-results__option { font-size: 13px; padding: 7px 12px; }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')

{{-- ── Header ───────────────────────────────────────────────────────── --}}
<div class="js-header">
    <div>
        <h4><i class="fas fa-calendar-week"></i> Jadwal Shift</h4>
        <p id="headerSub">
            {{ $namaBulan }}
            @if($department_id) &mdash; Departemen: {{ $departemenList->firstWhere('id', $department_id)?->name }} @endif
        </p>
    </div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <a href="{{ route('admin.laporan.buat-jadwal-shift') }}"
           style="background:rgba(255,255,255,0.2);color:white;border:1.5px solid rgba(255,255,255,0.4);font-size:12px;font-weight:700;border-radius:8px;padding:8px 16px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-calendar-plus"></i> Buat Jadwal Shift
        </a>
        <div class="view-tabs">
            <button class="view-tab {{ $view === 'grid' ? 'active' : '' }}" onclick="switchView('grid')">
                <i class="fas fa-table"></i> Semua Karyawan
            </button>
            <button class="view-tab {{ $view === 'kalender' ? 'active' : '' }}" onclick="switchView('kalender')">
                <i class="fas fa-calendar-alt"></i> Per Karyawan
            </button>
        </div>
    </div>
</div>

{{-- ── VIEW: GRID ───────────────────────────────────────────────────── --}}
<div id="view-grid" style="{{ $view === 'grid' ? '' : 'display:none;' }}">

    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.laporan.jadwal-shift') }}"
              style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
            <input type="hidden" name="view" value="grid">
            <div class="fg">
                <label>Bulan</label>
                <select name="bulan">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="fg">
                <label>Tahun</label>
                <select name="tahun">
                    @foreach(range(now()->year + 1, now()->year - 2, -1) as $y)
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
                <label>Filter Shift</label>
                <select name="shift_id">
                    <option value="">Semua Shift</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" {{ $s->id == $shiftId ? 'selected' : '' }}>{{ $s->nama_shift }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.laporan.jadwal-shift') }}?view=grid" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
            <a href="{{ route('admin.laporan.jadwal-shift.export-pdf', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$department_id]) }}"
               class="btn" style="background:#e53e3e;color:white;border-color:#e53e3e;" target="_blank">
               <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="jadwal-table">
                <thead>
                    <tr>
                        <th class="col-no">#</th>
                        <th class="col-nama" style="text-align:left;">Nama Karyawan</th>
                        <th class="col-dept" style="text-align:left;">Jabatan</th>
                        @for($d = 1; $d <= $jumlahHari; $d++)
                            @php $tgl = \Carbon\Carbon::createFromDate($tahun, $bulan, $d); @endphp
                            <th class="col-day{{ $tgl->isWeekend() ? ' day-weekend' : '' }}"
                                title="{{ $tgl->translatedFormat('l, d M Y') }}">
                                {{ $d }}<br>
                                <span style="font-size:9px;font-weight:400;">{{ $tgl->translatedFormat('D') }}</span>
                            </th>
                        @endfor
                        @if(auth()->user()->hasPermission('shift.kelola'))
                        <th style="width:60px;">Atur</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; $prevDept = null; @endphp
                    @forelse($karyawanList as $k)
                        @php $deptName = $k->department?->name ?? $k->departemen ?? 'Tanpa Departemen'; @endphp
                        @if($deptName !== $prevDept)
                            @php $prevDept = $deptName; @endphp
                            <tr class="dept-row">
                                <td colspan="{{ $jumlahHari + (auth()->user()->hasPermission('shift.kelola') ? 4 : 3) }}">
                                    <i class="fas fa-sitemap"></i> {{ $deptName }}
                                </td>
                            </tr>
                        @endif
                        <tr onclick="switchToKaryawan({{ $k->id }})" title="Klik untuk lihat kalender {{ $k->nama }}">
                            <td>{{ $no++ }}</td>
                            <td class="col-nama" title="{{ $k->nama }}">{{ $k->nama }}</td>
                            <td class="col-dept">{{ $k->jabatan ?? '-' }}</td>
                            @for($d = 1; $d <= $jumlahHari; $d++)
                                @php
                                    $tgl      = \Carbon\Carbon::createFromDate($tahun, $bulan, $d);
                                    $tglKey   = $tgl->format('Y-m-d');
                                    $isWeekend = $tgl->isWeekend();
                                    $shiftObj  = $jadwal[$k->id][$tglKey] ?? $k->shift;
                                    $namaShift = $shiftObj ? strtolower($shiftObj->nama_shift) : null;
                                    if (!$shiftObj)                              { $cls=''; $lbl=''; }
                                    elseif (str_contains($namaShift,'pagi'))     { $cls='shift-pagi';   $lbl='P'; }
                                    elseif (str_contains($namaShift,'siang'))    { $cls='shift-siang';  $lbl='S'; }
                                    elseif (str_contains($namaShift,'malam'))    { $cls='shift-malam';  $lbl='M'; }
                                    elseif (str_contains($namaShift,'normal') || str_contains($namaShift,'reguler') || str_contains($namaShift,'regular')) { $cls='shift-normal'; $lbl='N'; }
                                    else                                          { $cls='shift-other';  $lbl=strtoupper(substr($shiftObj->nama_shift,0,1)); }
                                    $isOverride = isset($jadwal[$k->id][$tglKey]);
                                @endphp
                                <td class="{{ $isWeekend ? 'day-weekend' : '' }}"
                                    title="{{ $shiftObj ? $shiftObj->nama_shift : '-' }}{{ $isOverride ? ' (khusus)' : '' }}">
                                    @if($cls)
                                        <span class="shift-badge {{ $cls }}"
                                              style="{{ $isOverride ? 'box-shadow:0 0 0 2px #f6ad55;' : '' }}">
                                            {{ $lbl }}
                                        </span>
                                    @else
                                        <span style="color:#e2e8f0;font-size:10px;">—</span>
                                    @endif
                                </td>
                            @endfor
                            @if(auth()->user()->hasPermission('karyawan.edit'))
                            <td onclick="event.stopPropagation()" style="white-space:nowrap;">
                                <button class="btn btn-secondary btn-sm"
                                        style="padding:3px 8px;font-size:11px;margin-right:2px;"
                                        title="Ubah shift default {{ $k->nama }}"
                                        onclick="openShiftDefaultModal({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ $k->shift_id ?? 'null' }})">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="btn btn-primary btn-sm"
                                        style="padding:3px 8px;font-size:11px;"
                                        title="Tambah jadwal khusus"
                                        onclick="openGridModal({{ $k->id }}, '{{ addslashes($k->nama) }}')">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $jumlahHari + 3 }}" style="text-align:center;padding:40px;color:#a0aec0;">
                                Tidak ada data karyawan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div style="background:white;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,0.07);margin-top:16px;display:flex;gap:14px;flex-wrap:wrap;align-items:center;">
        <span style="font-size:12px;font-weight:700;color:#4a5568;"><i class="fas fa-info-circle"></i> Keterangan:</span>
        @foreach([['shift-pagi','P','Pagi'],['shift-siang','S','Siang'],['shift-malam','M','Malam'],['shift-normal','N','Normal']] as [$c,$l,$n])
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#4a5568;">
            <span class="shift-badge {{ $c }}">{{ $l }}</span> {{ $n }}
        </div>
        @endforeach
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#4a5568;border-left:1px solid #e2e8f0;padding-left:12px;margin-left:4px;">
            <span class="shift-badge shift-pagi" style="box-shadow:0 0 0 2px #f6ad55;">P</span> Jadwal Khusus
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#718096;">
            <span style="display:inline-block;width:22px;height:22px;background:#fff1f2;border:1px solid #fecdd3;border-radius:4px;line-height:22px;text-align:center;font-size:10px;color:#e53e3e;font-weight:700;">S</span> Weekend
        </div>
        <span style="margin-left:auto;font-size:11px;color:#a0aec0;"><i class="fas fa-hand-pointer"></i> Klik baris untuk lihat kalender karyawan</span>
    </div>
</div>

{{-- ── VIEW: KALENDER ───────────────────────────────────────────────── --}}
<div id="view-kalender" style="{{ $view === 'kalender' ? '' : 'display:none;' }}">

    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.laporan.jadwal-shift') }}"
              id="formKalender"
              style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
            <input type="hidden" name="view" value="kalender">

            {{-- Filter Departemen (client-side, tidak submit) --}}
            <div class="fg" style="min-width:170px;">
                <label><i class="fas fa-building"></i> Departemen</label>
                <select id="filterDeptKalender" style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    <option value="">Semua Departemen</option>
                    @foreach($karyawanList->groupBy(fn($k) => $k->department?->name ?? $k->departemen ?? 'Tanpa Departemen')->keys()->sort() as $deptNm)
                        <option value="{{ $deptNm }}" {{ request('filter_dept') == $deptNm ? 'selected' : '' }}>{{ $deptNm }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Select2 searchable karyawan --}}
            <div class="fg" style="flex:1;min-width:260px;">
                <label><i class="fas fa-user"></i> Karyawan <small style="color:#a0aec0;font-weight:400;">(ketik nama/NIK untuk cari)</small></label>
                <select name="karyawan_id" id="selectKaryawan" style="width:100%;">
                    <option value="">— Pilih Karyawan —</option>
                    @foreach($karyawanList as $k)
                    @php $deptNm = $k->department?->name ?? $k->departemen ?? 'Tanpa Departemen'; @endphp
                    <option value="{{ $k->id }}"
                            data-dept="{{ $deptNm }}"
                            {{ $k->id == $karyawanId ? 'selected' : '' }}>
                        {{ $k->nama }} — {{ $k->nik }} ({{ $deptNm }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="fg">
                <label><i class="fas fa-calendar"></i> Bulan</label>
                <select name="bulan" onchange="this.form.submit()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="fg">
                <label><i class="fas fa-calendar-alt"></i> Tahun</label>
                <select name="tahun" onchange="this.form.submit()">
                    @foreach(range(now()->year+1, now()->year-2, -1) as $y)
                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
        </form>
    </div>

    @if(!$karyawanId)
    <div class="cal-card">
        <div style="text-align:center;padding:60px 20px;color:#a0aec0;">
            <div style="width:80px;height:80px;border-radius:50%;background:#f7fafc;border:2px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;color:#cbd5e0;">
                <i class="fas fa-user-clock"></i>
            </div>
            <h5 style="color:#4a5568;margin-bottom:8px;">Pilih Karyawan</h5>
            <p style="font-size:14px;max-width:380px;margin:0 auto;line-height:1.7;">
                Gunakan dropdown di atas untuk memilih karyawan dan melihat jadwal shift mereka per bulan.
            </p>
        </div>
    </div>

    @elseif($karyawan)

    {{-- Info Karyawan --}}
    <div class="karyawan-bar">
        <div class="karyawan-avatar">{{ strtoupper(substr($karyawan->nama, 0, 1)) }}</div>
        <div style="flex:1;">
            <div style="font-size:17px;font-weight:800;color:#2d3748;">{{ $karyawan->nama }}</div>
            <div style="font-size:12px;color:#718096;margin-top:2px;">
                {{ $karyawan->nik }} &nbsp;·&nbsp; {{ $karyawan->jabatan ?? '-' }} &nbsp;·&nbsp; {{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                <span class="chip chip-blue">
                    <i class="fas fa-clock"></i> Default: {{ $karyawan->shift->nama_shift ?? 'Tidak ada' }}
                </span>
                @if($karyawan->shift)
                <span class="chip chip-green">
                    {{ substr($karyawan->shift->jam_masuk ?? '-', 0, 5) }} — {{ substr($karyawan->shift->jam_keluar ?? '-', 0, 5) }}
                </span>
                @endif
                <span class="chip chip-orange"><i class="fas fa-calendar-week"></i> {{ $namaBulan }}</span>
            </div>
        </div>
        <div style="flex-shrink:0;display:flex;gap:8px;flex-wrap:wrap;">
            @if(auth()->user()->hasPermission('karyawan.edit'))
            <div style="position:relative;display:inline-block;">
                <button class="btn btn-secondary btn-sm" id="btnToggleShiftDefault"
                        onclick="document.getElementById('inlineShiftDefault').style.display = document.getElementById('inlineShiftDefault').style.display==='none' ? 'block' : 'none'"
                        style="padding:8px 16px;font-size:13px;border-radius:9px;">
                    <i class="fas fa-sync-alt"></i> Ubah Shift Default
                </button>
                <div id="inlineShiftDefault" style="display:none;position:absolute;right:0;top:110%;background:white;border:1px solid #e2e8f0;border-radius:10px;padding:16px;width:280px;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999;">
                    <div style="font-size:12px;font-weight:600;color:#4a5568;margin-bottom:10px;">Shift saat ini: <strong>{{ $karyawan->shift?->nama_shift ?? '—' }}</strong></div>
                    <form action="{{ route('admin.employeshift.shift-default-post', $karyawan) }}" method="POST">
                        @csrf
                        <select name="shift_id" class="form-select" style="margin-bottom:10px;font-size:13px;" required>
                            @foreach($shifts as $s)
                                <option value="{{ $s->id }}" {{ $karyawan->shift_id == $s->id ? 'selected' : '' }}>{{ $s->nama_shift }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary" style="width:100%;font-size:13px;">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @if(auth()->user()->hasPermission('shift.kelola'))
            <button class="btn btn-primary btn-sm" onclick="openAddModal()"
                    style="padding:8px 16px;font-size:13px;border-radius:9px;">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </button>
            <button class="btn btn-warning btn-sm" onclick="openRangeModal()"
                    style="padding:8px 16px;font-size:13px;border-radius:9px;">
                <i class="fas fa-calendar-week"></i> Atur Rentang
            </button>
            @endif
        </div>
    </div>

    @php
        $totalKhusus     = $jadwalKalend->count();
        $totalHariKerja  = collect(range(1, $jumlahHari))->filter(fn($d) =>
            !\Carbon\Carbon::createFromDate($tahun, $bulan, $d)->isSunday()
        )->count();
        $totalDefault    = $karyawan->shift ? max(0, $totalHariKerja - $totalKhusus) : 0;
    @endphp

    {{-- Stat cards --}}
    <div class="stat-row">
        <div class="stat-box">
            <div class="stat-num" style="color:#4299e1;">{{ $jumlahHari }}</div>
            <div class="stat-label">Hari dalam Bulan</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color:#48bb78;">{{ $totalHariKerja }}</div>
            <div class="stat-label">Hari Kerja</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color:#ed8936;">{{ $totalKhusus }}</div>
            <div class="stat-label">Jadwal Khusus</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color:#805ad5;">{{ $totalDefault }}</div>
            <div class="stat-label">Shift Default</div>
        </div>
    </div>

    {{-- Kalender --}}
    <div class="cal-card">
        <div class="cal-header">
            <i class="fas fa-calendar-week" style="color:#4299e1;"></i>
            Jadwal Bulan {{ $namaBulan }}
            @if(auth()->user()->hasPermission('shift.kelola'))
            <span style="margin-left:auto;font-size:11px;font-weight:400;color:#a0aec0;"><i class="fas fa-hand-pointer"></i> Klik tanggal untuk atur jadwal</span>
            @endif
        </div>
        <div class="cal-body">
            <div class="cal-grid">
                @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h)
                    <div class="cal-day-label" @if(in_array($h,['Min','Sab'])) style="color:#dc2626;" @endif>{{ $h }}</div>
                @endforeach

                @php $firstDay = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->dayOfWeek; @endphp
                @for($pad = 0; $pad < $firstDay; $pad++)<div></div>@endfor

                @for($d = 1; $d <= $jumlahHari; $d++)
                    @php
                        $tgl        = \Carbon\Carbon::createFromDate($tahun, $bulan, $d);
                        $tglKey     = $tgl->format('Y-m-d');
                        $isToday    = $tgl->isToday();
                        $isPast     = $tgl->isPast() && !$isToday;
                        $isWeekend  = $tgl->isWeekend();
                        $jadwalHari = $jadwalKalend->get($tglKey);
                        $shiftObj   = $jadwalHari ? $jadwalHari->shift : ($isWeekend ? null : $karyawan->shift);
                        $isKhusus   = (bool)$jadwalHari;
                        $namaShift  = $shiftObj ? strtolower($shiftObj->nama_shift) : '';
                        if ($isWeekend && !$jadwalHari)        { $cellClass='is-weekend'; $badgeClass=''; $badge=''; }
                        elseif (!$shiftObj)                    { $cellClass=''; $badgeClass=''; $badge=''; }
                        elseif (str_contains($namaShift,'pagi'))  { $cellClass='shift-pagi';   $badgeClass='badge-pagi';   $badge='Pagi'; }
                        elseif (str_contains($namaShift,'siang')) { $cellClass='shift-siang';  $badgeClass='badge-siang';  $badge='Siang'; }
                        elseif (str_contains($namaShift,'malam')) { $cellClass='shift-malam';  $badgeClass='badge-malam';  $badge='Malam'; }
                        else                                   { $cellClass='shift-normal'; $badgeClass='badge-normal'; $badge=$shiftObj->nama_shift; }
                        $cellExtra = ($isToday?' is-today':'').($isPast?' is-past':'').($isKhusus?' is-khusus':'');
                        $canEdit   = auth()->user()->hasPermission('shift.kelola');
                        $esId      = $jadwalHari?->id;
                    @endphp
                    <div class="cal-cell {{ $cellClass }}{{ $cellExtra }}{{ $canEdit ? ' clickable' : '' }}"
                         title="{{ $shiftObj ? $shiftObj->nama_shift : ($isWeekend?'Libur':'-') }}{{ $isKhusus?' ★ Khusus':'' }}{{ $canEdit?' — klik untuk atur':'' }}"
                         @if($canEdit)
                         onclick="openFromCal('{{ $tglKey }}', {{ $esId ?? 'null' }}, {{ $esId ? $jadwalHari->shift_id : 'null' }}, '{{ $esId && $jadwalHari->keterangan ? addslashes($jadwalHari->keterangan) : '' }}')"
                         @endif>
                        <div class="cal-date{{ $isToday?' today':'' }}{{ $isWeekend?' weekend':'' }}">{{ $d }}</div>
                        @if($isToday)<div class="cal-today-label">Hari Ini</div>@endif
                        @if($shiftObj && $badge)
                            <div class="cal-shift-badge {{ $badgeClass }}">{{ $badge }}</div>
                            <div class="cal-time">{{ $shiftObj->jam_masuk ? substr($shiftObj->jam_masuk,0,5) : '' }}</div>
                        @elseif($isWeekend && !$jadwalHari)
                            <div class="cal-time" style="color:#cbd5e0;">Libur</div>
                        @endif
                        @if($isKhusus)<div class="cal-khusus-label">★ Khusus</div>@endif
                    </div>
                @endfor
            </div>

            <div class="legend-bar">
                <span style="font-size:11px;font-weight:700;color:#718096;">Keterangan:</span>
                <span class="cal-shift-badge badge-pagi"   style="font-size:11px;padding:2px 10px;">Pagi</span>
                <span class="cal-shift-badge badge-siang"  style="font-size:11px;padding:2px 10px;">Siang</span>
                <span class="cal-shift-badge badge-malam"  style="font-size:11px;padding:2px 10px;">Malam</span>
                <span class="cal-shift-badge badge-normal" style="font-size:11px;padding:2px 10px;">Normal</span>
                <span style="font-size:11px;outline:2px solid #ed8936;outline-offset:1px;border-radius:6px;padding:2px 10px;color:#c05621;">★ Jadwal Khusus</span>
                <span style="font-size:11px;border:2px solid #4299e1;border-radius:6px;padding:2px 10px;color:#2b6cb0;">Hari Ini</span>
            </div>
        </div>
    </div>

    {{-- Tabel detail jadwal khusus --}}
    @if($jadwalKalend->count() > 0)
    <div class="khusus-card">
        <div class="khusus-header">
            <i class="fas fa-star" style="color:#ed8936;"></i>
            Detail Jadwal Khusus {{ $namaBulan }}
            <span style="margin-left:auto;font-size:12px;font-weight:400;color:#718096;">{{ $jadwalKalend->count() }} hari</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th><th>Hari</th><th>Shift</th>
                    <th>Masuk</th><th>Keluar</th><th>Keterangan</th>
                    @if(auth()->user()->hasPermission('shift.kelola'))<th style="width:60px;">Aksi</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($jadwalKalend->sortKeys() as $j)
                <tr>
                    <td style="font-weight:600;">{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
                    <td style="color:#718096;">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('l') }}</td>
                    <td>
                        @php
                            $sn = strtolower($j->shift->nama_shift ?? '');
                            $bc = str_contains($sn,'pagi')?'badge-pagi':(str_contains($sn,'siang')?'badge-siang':(str_contains($sn,'malam')?'badge-malam':'badge-normal'));
                        @endphp
                        <span class="cal-shift-badge {{ $bc }}" style="font-size:12px;padding:3px 12px;">{{ $j->shift->nama_shift ?? '-' }}</span>
                    </td>
                    <td style="font-weight:600;">{{ $j->shift->jam_masuk ? substr($j->shift->jam_masuk,0,5) : '-' }}</td>
                    <td style="font-weight:600;">{{ $j->shift->jam_keluar ? substr($j->shift->jam_keluar,0,5) : '-' }}</td>
                    <td style="color:#718096;font-size:13px;">{{ $j->keterangan ?? '-' }}</td>
                    @if(auth()->user()->hasPermission('shift.kelola'))
                    <td>
                        <button class="btn btn-warning btn-sm" style="padding:3px 7px;"
                                onclick="openFromCal('{{ $j->date->format('Y-m-d') }}', {{ $j->id }}, {{ $j->shift_id }}, '{{ addslashes($j->keterangan ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.employeshift.destroy', $j->id) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus jadwal ini?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="_redirect" value="{{ route('admin.laporan.jadwal-shift', ['view'=>'kalender','karyawan_id'=>$karyawan->id,'bulan'=>$bulan,'tahun'=>$tahun]) }}">
                            <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 7px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="cal-card" style="padding:20px 24px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:50%;background:#f0fff4;border:2px solid #9ae6b4;display:flex;align-items:center;justify-content:center;font-size:16px;color:#38a169;flex-shrink:0;">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <div style="font-weight:700;color:#2d3748;">Tidak ada jadwal khusus bulan ini</div>
                <div style="font-size:13px;color:#718096;margin-top:2px;">
                    Semua hari kerja pakai shift default: <strong>{{ $karyawan->shift->nama_shift ?? '-' }}</strong>
                    @if($karyawan->shift)({{ substr($karyawan->shift->jam_masuk,0,5) }} — {{ substr($karyawan->shift->jam_keluar,0,5) }})@endif
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

{{-- ══ MODALS ═══════════════════════════════════════════════════════════ --}}
@if(auth()->user()->hasPermission('shift.kelola'))

{{-- Modal Tambah/Edit 1 hari --}}
<div id="modalJadwal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:460px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h6 id="modalJadwalTitle" style="font-weight:700;color:#2d3748;margin:0;font-size:15px;">
                <i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Atur Jadwal Shift
            </h6>
            <button onclick="closeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <div id="modalKaryawanInfo" style="background:#f7fafc;border-radius:9px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#4a5568;"></div>

        <form id="formJadwal" method="POST">
            @csrf
            <span id="methodSpoof"></span>
            <input type="hidden" id="hiddenReturn" name="_redirect" value="">

            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Tanggal <span style="color:#e53e3e;">*</span></label>
                <input type="date" name="date" id="inputDate" class="form-control" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Shift <span style="color:#e53e3e;">*</span></label>
                <select name="shift_id" id="inputShift" class="form-select" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ substr($s->jam_masuk,0,5) }}–{{ substr($s->jam_keluar,0,5) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Keterangan</label>
                <input type="text" name="keterangan" id="inputKet" class="form-control" placeholder="Opsional" maxlength="255" style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <button type="button" id="btnHapus" onclick="hapusJadwal()" class="btn btn-danger" style="display:none;">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </form>
        <form id="formHapus" method="POST" style="display:none;">
            @csrf @method('DELETE')
            <input type="hidden" name="_redirect" id="hapusReturn" value="">
        </form>
    </div>
</div>

{{-- Modal Rentang Tanggal (atur banyak hari sekaligus) --}}
<div id="modalRange" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;font-size:15px;">
                <i class="fas fa-calendar-week" style="color:#ed8936;"></i> Atur Jadwal Rentang Tanggal
            </h6>
            <button onclick="closeRangeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <div id="rangeKaryawanInfo" style="background:#f7fafc;border-radius:9px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#4a5568;"></div>

        <form id="formRange" method="POST">
            @csrf
            <input type="hidden" name="_redirect" id="rangeReturn" value="">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Dari Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="date_from" id="inputDateFrom" class="form-control" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Sampai Tanggal <span style="color:#e53e3e;">*</span></label>
                    <input type="date" name="date_to" id="inputDateTo" class="form-control" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Shift <span style="color:#e53e3e;">*</span></label>
                <select name="shift_id" id="rangeShift" class="form-select" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ substr($s->jam_masuk,0,5) }}–{{ substr($s->jam_keluar,0,5) }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:8px;">Hari yang Disertakan</label>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $idx => $hr)
                    <label style="display:flex;align-items:center;gap:4px;padding:5px 10px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;color:#4a5568;">
                        <input type="checkbox" name="hari[]" value="{{ $idx }}" {{ $idx > 0 && $idx < 6 ? 'checked' : '' }}>
                        {{ $hr }}
                    </label>
                    @endforeach
                </div>
                <small style="color:#718096;font-size:11px;margin-top:4px;display:block;">Default: Senin–Jumat. Centang hari yang ingin diatur jadwalnya.</small>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Keterangan</label>
                <input type="text" name="keterangan" class="form-control" placeholder="Opsional" maxlength="255" style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
            </div>

            <div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:8px;padding:10px 14px;font-size:12px;color:#744210;margin-bottom:16px;">
                <i class="fas fa-info-circle"></i>
                Jika sudah ada jadwal khusus di tanggal tersebut, akan ditimpa dengan shift baru.
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeRangeModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-calendar-check"></i> Terapkan</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     MODAL: ATUR SHIFT MASSAL
═══════════════════════════════════════════════════════════════ --}}
<div id="modalBulk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:16px;width:100%;max-width:620px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#553c9a,#805ad5);padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:16px;font-weight:800;color:white;"><i class="fas fa-layer-group"></i> Atur Shift Massal</div>
                <div style="font-size:12px;color:rgba(255,255,255,0.8);margin-top:2px;">Terapkan shift ke banyak karyawan sekaligus</div>
            </div>
            <button onclick="document.getElementById('modalBulk').style.display='none'"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:16px;">&times;</button>
        </div>

        <div style="padding:24px;">
            @if(session('success'))
            <div style="background:#c6f6d5;border:1px solid #9ae6b4;border-radius:8px;padding:10px 14px;color:#276749;font-size:13px;margin-bottom:16px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.employeshift.storeBulk') }}" id="formBulk">
                @csrf
                <input type="hidden" name="target" value="pilih">
                <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">

                {{-- Departemen filter + daftar karyawan --}}
                <div style="margin-bottom:16px;">
                    {{-- Baris 1: label + tombol --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:8px;">
                        <label style="font-size:12px;font-weight:700;color:#4a5568;">
                            <i class="fas fa-users" style="color:#805ad5;"></i> Pilih Karyawan
                            <span style="color:#e53e3e;">*</span>
                        </label>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button type="button" onclick="checkVisible(true)"
                                    style="padding:5px 11px;background:#e9d8fd;color:#553c9a;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;">
                                <i class="fas fa-check-double"></i> Centang Semua
                            </button>
                            <button type="button" onclick="checkVisible(false)"
                                    style="padding:5px 11px;background:#fed7e2;color:#c53030;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;">
                                <i class="fas fa-times"></i> Reset
                            </button>
                        </div>
                    </div>

                    {{-- Baris 2: filter departemen + search --}}
                    <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                        <select id="filterDeptBulk" onchange="loadBulkKaryawan(this.value)"
                                style="padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#2d3748;flex:1;min-width:150px;">
                            <option value="">— Semua Departemen —</option>
                            @foreach($departemenList as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <div style="position:relative;flex:1;min-width:150px;">
                            <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a0aec0;font-size:12px;pointer-events:none;"></i>
                            <input type="text" id="searchBulk" placeholder="Cari nama / jabatan…"
                                   oninput="filterBulkSearch(this.value)"
                                   style="width:100%;padding:8px 10px 8px 30px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#2d3748;box-sizing:border-box;">
                        </div>
                    </div>

                    {{-- Daftar karyawan --}}
                    <div id="karyawanCheckboxList"
                         style="max-height:220px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:10px;padding:8px;background:#fafafa;">
                        <div style="text-align:center;padding:30px;color:#a0aec0;font-size:13px;">
                            <i class="fas fa-building" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                            Pilih departemen atau ketik nama untuk mencari karyawan.
                        </div>
                    </div>
                    <div id="selectedCount" style="margin-top:6px;font-size:12px;color:#553c9a;font-weight:600;min-height:18px;"></div>
                </div>

                {{-- Rentang tanggal --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Dari Tanggal <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="date_from" id="bulkDateFrom" class="form-control" required
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                               style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Sampai Tanggal <span style="color:#e53e3e;">*</span></label>
                        <input type="date" name="date_to" id="bulkDateTo" class="form-control" required
                               value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                               style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    </div>
                </div>

                {{-- Shift --}}
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Shift <span style="color:#e53e3e;">*</span></label>
                    <select name="shift_id" class="form-select" required style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ substr($s->jam_masuk,0,5) }}–{{ substr($s->jam_keluar,0,5) }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Hari --}}
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:8px;">Hari yang Disertakan</label>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $idx => $hr)
                        <label style="display:flex;align-items:center;gap:4px;padding:5px 10px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;color:#4a5568;background:#f7fafc;">
                            <input type="checkbox" name="hari[]" value="{{ $idx }}" {{ $idx > 0 && $idx < 6 ? 'checked' : '' }}>
                            {{ $hr }}
                        </label>
                        @endforeach
                    </div>
                    <small style="color:#718096;font-size:11px;margin-top:4px;display:block;">Default: Senin–Jumat.</small>
                </div>

                {{-- Keterangan --}}
                <div style="margin-bottom:16px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Opsional" maxlength="255"
                           style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                </div>

                <div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:8px;padding:10px 14px;font-size:12px;color:#744210;margin-bottom:16px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Jadwal khusus yang sudah ada di tanggal tersebut akan <strong>ditimpa</strong> dengan shift baru.
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('modalBulk').style.display='none'" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background:#553c9a;border-color:#553c9a;">
                        <i class="fas fa-layer-group"></i> Terapkan Massal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Modal Ubah Shift Default --}}
<div id="modalShiftDefault" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:1100;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;width:400px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#4299e1,#667eea);border-radius:14px 14px 0 0;">
            <h6 style="margin:0;color:white;font-weight:700;"><i class="fas fa-sync-alt"></i> Ubah Shift Default</h6>
            <button type="button" onclick="closeShiftDefaultModal()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:16px;">&times;</button>
        </div>
        <div style="padding:20px;">
            <p style="color:#718096;font-size:13px;margin-bottom:4px;">Karyawan: <strong id="sdNama"></strong></p>
            <form id="formShiftDefault" action="" method="POST" style="margin-top:12px;">
                @csrf
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;">Shift Baru</label>
                    <select name="shift_id" id="sdShiftId" class="form-select" required>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ substr($s->jam_masuk,0,5) }}–{{ substr($s->jam_keluar,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="closeShiftDefaultModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const currentView   = '{{ $view }}';
const baseUrl       = '{{ route('admin.laporan.jadwal-shift') }}';
const bulan         = {{ $bulan }};
const tahun         = {{ $tahun }};
@if($karyawan)
const karyawanId    = {{ $karyawan->id }};
const karyawanNama  = '{{ addslashes($karyawan->nama) }}';
const karyawanNik   = '{{ $karyawan->nik }}';
const storeUrl      = '{{ route('admin.employeshift.store', $karyawan) }}';
const baseEditUrl   = '{{ route('admin.employeshift.update', ':id') }}'.replace(':id', '');
const storeRangeUrl = '{{ route('admin.employeshift.storeRange', $karyawan) }}';
const returnKalUrl  = '{!! route('admin.laporan.jadwal-shift', ['view'=>'kalender','karyawan_id'=>$karyawan->id,'bulan'=>$bulan,'tahun'=>$tahun]) !!}';
@else
const karyawanId = null, karyawanNama = '', karyawanNik = '', storeUrl = '', baseEditUrl = '{{ route('admin.employeshift.update', ':esid') }}'.replace('/:esid', '/'), storeRangeUrl = '', returnKalUrl = '';
@endif

/* ── View switch ─────────────────────────────────────── */
function switchView(v) {
    document.getElementById('view-grid').style.display     = v === 'grid'     ? '' : 'none';
    document.getElementById('view-kalender').style.display = v === 'kalender' ? '' : 'none';
    document.querySelectorAll('.view-tab').forEach((t,i) => {
        t.classList.toggle('active', (i===0 && v==='grid') || (i===1 && v==='kalender'));
    });
    // Update URL tanpa reload
    const url = new URL(window.location);
    url.searchParams.set('view', v);
    window.history.replaceState({}, '', url);
}

/* Klik baris grid → pindah ke kalender karyawan tersebut */
function switchToKaryawan(kid) {
    const url = new URL(window.location);
    url.searchParams.set('view', 'kalender');
    url.searchParams.set('karyawan_id', kid);
    window.location = url;
}

/* Buka modal dari tombol di grid (karyawan bisa berbeda) */
function openGridModal(kid, nama) {
    document.getElementById('modalKaryawanInfo').innerHTML =
        '<i class="fas fa-user" style="color:#4299e1;"></i> <strong>' + nama + '</strong>';
    document.getElementById('modalJadwalTitle').innerHTML =
        '<i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Tambah Jadwal Shift';
    document.getElementById('formJadwal').action = '{{ route('admin.employeshift.store', ':kid') }}'.replace(':kid', kid);
    document.getElementById('methodSpoof').innerHTML = '';
    document.getElementById('inputDate').value  = '';
    document.getElementById('inputShift').value = '';
    document.getElementById('inputKet').value   = '';
    document.getElementById('btnHapus').style.display = 'none';
    document.getElementById('hiddenReturn').value = baseUrl + '?view=grid&bulan=' + bulan + '&tahun=' + tahun;
    document.getElementById('modalJadwal').style.display = 'flex';
}

/* ── Kalender modal (1 hari) ─────────────────────────── */
function openAddModal(tgl) {
    document.getElementById('modalKaryawanInfo').innerHTML =
        '<i class="fas fa-user" style="color:#4299e1;"></i> <strong>' + karyawanNama + '</strong> — ' + karyawanNik;
    document.getElementById('modalJadwalTitle').innerHTML =
        '<i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Tambah Jadwal Shift';
    document.getElementById('formJadwal').action = storeUrl;
    document.getElementById('methodSpoof').innerHTML = '';
    document.getElementById('inputDate').value  = tgl || '';
    document.getElementById('inputShift').value = '';
    document.getElementById('inputKet').value   = '';
    document.getElementById('btnHapus').style.display = 'none';
    document.getElementById('hiddenReturn').value = returnKalUrl;
    document.getElementById('modalJadwal').style.display = 'flex';
}

function openFromCal(tgl, esId, shiftId, ket) {
    document.getElementById('modalKaryawanInfo').innerHTML =
        '<i class="fas fa-user" style="color:#4299e1;"></i> <strong>' + karyawanNama + '</strong> — ' + karyawanNik;
    if (esId) {
        document.getElementById('modalJadwalTitle').innerHTML =
            '<i class="fas fa-calendar-edit" style="color:#ed8936;"></i> Edit Jadwal Shift';
        document.getElementById('formJadwal').action = baseEditUrl + esId;
        document.getElementById('methodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('formHapus').action = baseEditUrl + esId;
        document.getElementById('hapusReturn').value = returnKalUrl;
        document.getElementById('btnHapus').style.display = 'inline-flex';
    } else {
        document.getElementById('modalJadwalTitle').innerHTML =
            '<i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Tambah Jadwal Shift';
        document.getElementById('formJadwal').action = storeUrl;
        document.getElementById('methodSpoof').innerHTML = '';
        document.getElementById('btnHapus').style.display = 'none';
    }
    document.getElementById('inputDate').value  = tgl;
    document.getElementById('inputShift').value = shiftId || '';
    document.getElementById('inputKet').value   = ket || '';
    document.getElementById('hiddenReturn').value = returnKalUrl;
    document.getElementById('modalJadwal').style.display = 'flex';
}

function hapusJadwal() {
    if (!confirm('Hapus jadwal khusus ini? Karyawan akan kembali ke shift default.')) return;
    document.getElementById('formHapus').submit();
}
function closeModal() { document.getElementById('modalJadwal').style.display = 'none'; }

/* ── Modal Rentang ───────────────────────────────────── */
function openRangeModal() {
    document.getElementById('rangeKaryawanInfo').innerHTML =
        '<i class="fas fa-user" style="color:#ed8936;"></i> <strong>' + karyawanNama + '</strong> — ' + karyawanNik;
    document.getElementById('formRange').action = storeRangeUrl;
    document.getElementById('rangeReturn').value = returnKalUrl;
    // Default: bulan ini
    const y = tahun, m = String(bulan).padStart(2,'0');
    document.getElementById('inputDateFrom').value = y + '-' + m + '-01';
    const lastDay = new Date(y, bulan, 0).getDate();
    document.getElementById('inputDateTo').value   = y + '-' + m + '-' + String(lastDay).padStart(2,'0');
    document.getElementById('modalRange').style.display = 'flex';
}
function closeRangeModal() { document.getElementById('modalRange').style.display = 'none'; }

/* Backdrop click tutup modal */
['modalJadwal','modalRange','modalBulk'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', e => { if (e.target === el) el.style.display = 'none'; });
});

/* ── Bulk Shift Assignment ────────────────────────────── */
const bulkKaryawanUrl = '{{ route('admin.karyawan-by-dept') }}';
let bulkData = []; // cache hasil fetch

function loadBulkKaryawan(dept) {
    document.getElementById('searchBulk').value = '';
    const url = dept ? bulkKaryawanUrl + '?department_id=' + encodeURIComponent(dept) : bulkKaryawanUrl;
    const el  = document.getElementById('karyawanCheckboxList');
    el.innerHTML = '<div style="text-align:center;padding:20px;color:#a0aec0;"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            bulkData = data;
            renderBulkList(data);
        })
        .catch(() => {
            el.innerHTML = '<div style="text-align:center;padding:20px;color:#e53e3e;font-size:13px;"><i class="fas fa-exclamation-circle"></i> Gagal memuat data.</div>';
        });
}

function renderBulkList(data) {
    const el   = document.getElementById('karyawanCheckboxList');
    const dept = document.getElementById('filterDeptBulk').value;

    if (data.length === 0) {
        el.innerHTML = '<div style="text-align:center;padding:20px;color:#a0aec0;font-size:13px;"><i class="fas fa-search"></i> Tidak ada karyawan ditemukan.</div>';
        updateSelectedCount();
        return;
    }

    // Simpan status checked yang sudah ada (agar search tidak reset pilihan)
    const prevChecked = {};
    document.querySelectorAll('#karyawanCheckboxList input[type=checkbox]').forEach(cb => {
        prevChecked[cb.value] = cb.checked;
    });

    let html = '';
    let prevDeptLabel = null;

    data.forEach(k => {
        // Header departemen hanya jika load lintas-dept
        if (!dept && k.dept_name !== prevDeptLabel) {
            html += `<div style="padding:4px 8px;font-size:10px;font-weight:800;color:#805ad5;
                         text-transform:uppercase;letter-spacing:0.5px;background:#f3e8ff;
                         border-radius:6px;margin:6px 0 2px;sticky:top 0;">
                         <i class="fas fa-building" style="margin-right:4px;"></i>${k.dept_name ?? 'Tanpa Departemen'}
                     </div>`;
            prevDeptLabel = k.dept_name;
        }

        // Pakai status lama jika ada, default checked
        const isChecked = (k.id in prevChecked) ? prevChecked[k.id] : true;

        html += `<label data-nama="${k.nama.toLowerCase()}" data-jabatan="${(k.jabatan ?? '').toLowerCase()}"
                        style="display:flex;align-items:center;gap:10px;padding:7px 8px;border-radius:8px;
                               cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" name="karyawan_ids[]" value="${k.id}"
                           ${isChecked ? 'checked' : ''}
                           onchange="updateSelectedCount()"
                           style="width:16px;height:16px;accent-color:#805ad5;cursor:pointer;flex-shrink:0;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:#2d3748;
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${k.nama}</div>
                        <div style="font-size:11px;color:#718096;">
                            ${k.dept_name ?? ''}${k.jabatan ? ' &middot; ' + k.jabatan : ''}
                        </div>
                    </div>
                </label>`;
    });

    el.innerHTML = html;
    updateSelectedCount();
}

function filterBulkSearch(q) {
    q = q.toLowerCase().trim();
    if (!q) {
        // Tampilkan semua (dari cache), jaga status checkbox
        renderBulkList(bulkData);
        return;
    }
    const filtered = bulkData.filter(k =>
        k.nama.toLowerCase().includes(q) ||
        (k.jabatan ?? '').toLowerCase().includes(q) ||
        (k.dept_name ?? '').toLowerCase().includes(q)
    );
    renderBulkList(filtered);
}

// Centang/uncentang hanya yang sedang tampil (tidak tersembunyi oleh search)
function checkVisible(state) {
    document.querySelectorAll('#karyawanCheckboxList input[type=checkbox]').forEach(cb => {
        cb.checked = state;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('#karyawanCheckboxList input[type=checkbox]:checked').length;
    const total   = document.querySelectorAll('#karyawanCheckboxList input[type=checkbox]').length;
    const el = document.getElementById('selectedCount');
    if (total === 0) { el.innerHTML = ''; return; }
    const color = checked > 0 ? '#38a169' : '#e53e3e';
    const warn  = checked === 0 ? ' &nbsp;<span style="color:#e53e3e;font-weight:400;">(minimal 1 karyawan harus dipilih)</span>' : '';
    el.innerHTML = `<i class="fas fa-user-check" style="color:${color};"></i> `
                 + `<strong style="color:${color};">${checked}</strong> dari ${total} karyawan dipilih${warn}`;
}
</script>

{{-- Select2 untuk karyawan picker di view kalender --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    // Init Select2 pada select karyawan kalender
    $('#selectKaryawan').select2({
        placeholder: '— Ketik nama atau NIK untuk mencari —',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() { return 'Karyawan tidak ditemukan'; },
            searching: function() { return 'Mencari...'; },
        },
    });

    // Auto-submit saat karyawan dipilih
    $('#selectKaryawan').on('select2:select select2:clear', function () {
        document.getElementById('formKalender').submit();
    });

    // Filter departemen — sembunyikan option yang tidak sesuai lalu refresh Select2
    $('#filterDeptKalender').on('change', function () {
        const dept = $(this).val();
        const select = $('#selectKaryawan');

        // Reset ke pilih karyawan kosong
        select.val('').trigger('change.select2');

        // Tampilkan/sembunyikan option sesuai dept
        select.find('option[data-dept]').each(function () {
            const match = !dept || $(this).data('dept') === dept;
            $(this).prop('disabled', !match);
            // Select2 tidak support show/hide option langsung, gunakan disabled
        });

        // Kalau ada dept dipilih, coba set placeholder info
        if (dept) {
            const count = select.find('option[data-dept="' + dept + '"]').length;
            select.data('select2').options.options.placeholder = '— ' + dept + ' (' + count + ' karyawan) —';
            select.trigger('change.select2');
        } else {
            select.data('select2').options.options.placeholder = '— Ketik nama atau NIK untuk mencari —';
            select.trigger('change.select2');
        }

        // Refresh Select2 agar mengikuti perubahan disabled
        select.trigger('change.select2');
    });

    // Kalau sudah ada karyawan dipilih saat load, trigger filter dept-nya
    const activeDept = '{{ request('filter_dept', '') }}';
    if (activeDept) {
        $('#filterDeptKalender').val(activeDept).trigger('change');
    }
});

/* ── Modal Ubah Shift Default ─────────────────────────── */
const shiftDefaultRouteTemplate = '{{ route('admin.employeshift.shift-default-post', ':kid') }}';

function openShiftDefaultModal(kid, nama, currentShiftId) {
    document.getElementById('sdNama').textContent = nama;
    document.getElementById('formShiftDefault').action = shiftDefaultRouteTemplate.replace(':kid', kid);
    if (currentShiftId) {
        document.getElementById('sdShiftId').value = currentShiftId;
    }
    document.getElementById('modalShiftDefault').style.display = 'flex';
}

function closeShiftDefaultModal() {
    document.getElementById('modalShiftDefault').style.display = 'none';
}

document.getElementById('modalShiftDefault').addEventListener('click', function(e) {
    if (e.target === this) closeShiftDefaultModal();
});
</script>
@endpush
@endsection

