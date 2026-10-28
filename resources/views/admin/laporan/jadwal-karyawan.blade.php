@extends('layouts.admin')
@section('title', 'Jadwal Shift Karyawan')
@section('page-title', 'Cek Jadwal Shift Karyawan')

@push('styles')
<style>
.jk-header {
    background: linear-gradient(135deg, #1a202c, #2d3748);
    border-radius: 16px; padding: 24px 28px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
}
.jk-header h4 { margin: 0; color: white; font-size: 20px; font-weight: 800; }
.jk-header p  { margin: 4px 0 0; color: #a0aec0; font-size: 13px; }

.filter-card {
    background: white; border-radius: 14px; padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 20px;
}
.filter-card .filter-row {
    display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;
}
.filter-card .fg { display: flex; flex-direction: column; gap: 5px; }
.filter-card label { font-size: 12px; font-weight: 700; color: #4a5568; letter-spacing: 0.3px; }
.filter-card select {
    padding: 9px 14px; border: 1.5px solid #e2e8f0; border-radius: 9px;
    font-size: 13px; color: #2d3748; min-width: 180px; transition: border-color .2s;
}
.filter-card select:focus { border-color: #4299e1; outline: none; }

/* Karyawan info bar */
.karyawan-bar {
    background: linear-gradient(135deg, #ebf8ff, #f0fff4);
    border: 1.5px solid #bee3f8; border-radius: 14px;
    padding: 18px 22px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
}
.karyawan-avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #4299e1, #667eea);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800; color: white; flex-shrink: 0;
}
.karyawan-info-name { font-size: 17px; font-weight: 800; color: #2d3748; }
.karyawan-info-sub  { font-size: 12px; color: #718096; margin-top: 2px; }
.karyawan-chips     { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.chip {
    padding: 3px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;
    border: 1px solid;
}
.chip-blue  { background: rgba(66,153,225,0.15); color: #2b6cb0; border-color: rgba(66,153,225,0.3); }
.chip-green { background: rgba(72,187,120,0.15); color: #276749; border-color: rgba(72,187,120,0.3); }
.chip-orange{ background: rgba(237,137,54,0.15);  color: #c05621; border-color: rgba(237,137,54,0.3); }

/* Stat row */
.stat-row {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;
}
.stat-box {
    background: white; border-radius: 12px; padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center;
}
.stat-box .stat-num  { font-size: 28px; font-weight: 800; }
.stat-box .stat-label{ font-size: 12px; color: #718096; margin-top: 4px; }

/* Calendar */
.cal-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 20px;
}
.cal-header {
    padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
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
    min-height: 80px; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 3px;
    border: 1px solid transparent; position: relative;
}
.cal-cell.is-today    { border-width: 2px; border-color: #4299e1; background: #ebf8ff !important; }
.cal-cell.is-past     { opacity: 0.65; }
.cal-cell.is-khusus   { outline: 2.5px solid #ed8936; outline-offset: 1px; }
.cal-cell.is-weekend  { background: #f7fafc; }
.cal-cell.shift-pagi  { background: #ebf8ff; border-color: #bee3f8; }
.cal-cell.shift-siang { background: #fffbeb; border-color: #fbd38d; }
.cal-cell.shift-malam { background: #f3e8ff; border-color: #e9d8fd; }
.cal-cell.shift-normal{ background: #f0fff4; border-color: #9ae6b4; }

.cal-date { font-size: 13px; font-weight: 600; color: #2d3748; }
.cal-date.today { font-weight: 800; color: #2b6cb0; }
.cal-date.weekend { color: #a0aec0; }
.cal-today-label {
    font-size: 8px; font-weight: 700; color: #4299e1;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.cal-shift-badge {
    font-size: 9px; font-weight: 700; border-radius: 6px; padding: 1px 7px;
    border: 1px solid;
}
.badge-pagi   { color: #3182ce; background: #ebf8ff; border-color: #bee3f8; }
.badge-siang  { color: #d69e2e; background: #fffbeb; border-color: #fbd38d; }
.badge-malam  { color: #6b46c1; background: #f3e8ff; border-color: #e9d8fd; }
.badge-normal { color: #38a169; background: #f0fff4; border-color: #9ae6b4; }
.badge-other  { color: #718096; background: #f7fafc; border-color: #e2e8f0; }

.cal-time { font-size: 9px; color: #718096; }
.cal-khusus-label { font-size: 8px; color: #c05621; font-weight: 700; }

/* List jadwal khusus */
.khusus-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden;
}
.khusus-header {
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    font-weight: 700; color: #2d3748; font-size: 14px;
    display: flex; align-items: center; gap: 8px;
}

/* Empty state */
.empty-state {
    text-align: center; padding: 60px 20px; color: #a0aec0;
}
.empty-state .empty-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: #f7fafc; border: 2px dashed #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; margin: 0 auto 20px; color: #cbd5e0;
}

/* Legenda */
.legend-bar {
    background: white; border-radius: 12px; padding: 14px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 16px;
    display: flex; gap: 14px; flex-wrap: wrap; align-items: center;
}

@media (max-width: 640px) {
    .stat-row { grid-template-columns: repeat(2, 1fr); }
    .cal-grid  { gap: 4px; }
    .cal-cell  { min-height: 60px; padding: 4px 2px; }
    .cal-shift-badge { display: none; }
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="jk-header">
    <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#4299e1,#805ad5);
                display:flex;align-items:center;justify-content:center;font-size:22px;color:white;flex-shrink:0;">
        <i class="fas fa-calendar-user" style="font-size:20px;"></i>
    </div>
    <div>
        <h4><i class="fas fa-calendar-week" style="color:#63b3ed;"></i> Jadwal Shift Karyawan</h4>
        <p>Pilih nama karyawan untuk melihat jadwal shift lengkap per bulan</p>
    </div>
</div>

{{-- Filter --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.laporan.jadwal-karyawan') }}">
        <div class="filter-row">

            <div class="fg" style="flex:1;min-width:200px;">
                <label><i class="fas fa-user"></i> Karyawan</label>
                <select name="karyawan_id" onchange="this.form.submit()" style="min-width:220px;">
                    <option value="">— Pilih Karyawan —</option>
                    @php $prevDept = null; @endphp
                    @foreach($karyawanList as $k)
                        @php $deptName = $k->department?->name ?? $k->departemen; @endphp
                        @if($deptName !== $prevDept)
                            @if($prevDept !== null) </optgroup> @endif
                            <optgroup label="{{ $deptName ?? 'Tanpa Departemen' }}">
                            @php $prevDept = $deptName; @endphp
                        @endif
                        <option value="{{ $k->id }}" {{ $k->id == $karyawanId ? 'selected' : '' }}>
                            {{ $k->nama }} — {{ $k->nik }}
                        </option>
                    @endforeach
                    @if($prevDept !== null) </optgroup> @endif
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

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Tampilkan
            </button>

            @if($karyawanId)
            <a href="{{ route('admin.laporan.jadwal-karyawan') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

@if(!$karyawanId)
{{-- Empty: belum pilih karyawan --}}
<div class="cal-card">
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <h5 style="color:#4a5568;margin-bottom:8px;">Pilih Karyawan</h5>
        <p style="font-size:14px;max-width:380px;margin:0 auto;line-height:1.7;">
            Gunakan dropdown di atas untuk memilih karyawan dan melihat jadwal shift mereka bulan ini.
            Cocok untuk karyawan rolling yang jadwalnya bisa berubah setiap minggu.
        </p>
    </div>
</div>

@elseif(!$karyawan)
<div class="cal-card">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-exclamation"></i></div>
        <p>Karyawan tidak ditemukan.</p>
    </div>
</div>

@else

{{-- Info Karyawan --}}
<div class="karyawan-bar">
    <div class="karyawan-avatar">{{ strtoupper(substr($karyawan->nama, 0, 1)) }}</div>
    <div style="flex:1;">
        <div class="karyawan-info-name">{{ $karyawan->nama }}</div>
        <div class="karyawan-info-sub">{{ $karyawan->nik }} &nbsp;·&nbsp; {{ $karyawan->jabatan ?? '-' }} &nbsp;·&nbsp; {{ $karyawan->departemen ?? '-' }}</div>
        <div class="karyawan-chips">
            <span class="chip chip-blue">
                <i class="fas fa-clock"></i> Default: {{ $karyawan->shift?->nama_shift ?? 'Tidak ada' }}
            </span>
            @if($karyawan->shift)
            <span class="chip chip-green">
                <i class="fas fa-sign-in-alt"></i> {{ substr($karyawan->shift->jam_masuk ?? '-', 0, 5) }} — {{ substr($karyawan->shift->jam_keluar ?? '-', 0, 5) }}
            </span>
            @endif
            <span class="chip chip-orange">
                <i class="fas fa-calendar-week"></i> {{ $namaBulan }}
            </span>
        </div>
    </div>
    @if(auth()->user()->hasPermission('shift.kelola'))
    <div style="flex-shrink:0;">
        <button class="btn btn-primary btn-sm" onclick="openAddModal()"
                style="padding:8px 16px;font-size:13px;border-radius:9px;">
            <i class="fas fa-plus"></i> Atur Jadwal
        </button>
        <a href="{{ route('admin.karyawan.show', $karyawan) }}#tab-employeshift"
           class="btn btn-secondary btn-sm" style="padding:8px 16px;font-size:13px;border-radius:9px;margin-left:6px;">
            <i class="fas fa-list"></i> Lihat Semua
        </a>
    </div>
    @endif
</div>

@php
    $totalKhusus   = $jadwal->count();
    $totalHariKerja = collect(range(1, $jumlahHari))->filter(fn($d) =>
        !\Carbon\Carbon::createFromDate($tahun, $bulan, $d)->isSunday()
    )->count();
    $totalDefault  = $karyawan->shift ? max(0, $totalHariKerja - $totalKhusus) : 0;
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
    </div>
    <div class="cal-body">
        <div class="cal-grid">
            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h)
                <div class="cal-day-label">{{ $h }}</div>
            @endforeach

            @php
                $firstDay = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->dayOfWeek;
            @endphp
            @for($pad = 0; $pad < $firstDay; $pad++)
                <div></div>
            @endfor

            @for($d = 1; $d <= $jumlahHari; $d++)
                @php
                    $tgl        = \Carbon\Carbon::createFromDate($tahun, $bulan, $d);
                    $tglKey     = $tgl->format('Y-m-d');
                    $isToday    = $tgl->isToday();
                    $isPast     = $tgl->isPast() && !$isToday;
                    $isWeekend  = $tgl->isWeekend();

                    $jadwalHari = $jadwal->get($tglKey);
                    $shiftObj   = $jadwalHari ? $jadwalHari->shift : ($isWeekend ? null : $karyawan->shift);
                    $isKhusus   = (bool)$jadwalHari;
                    $namaShift  = $shiftObj ? strtolower($shiftObj->nama_shift) : '';

                    if ($isWeekend && !$jadwalHari) {
                        $cellClass = 'is-weekend'; $badgeClass = ''; $badge = ''; $badgeLabel = '';
                    } elseif (!$shiftObj) {
                        $cellClass = ''; $badgeClass = ''; $badge = ''; $badgeLabel = '';
                    } elseif (str_contains($namaShift, 'pagi')) {
                        $cellClass = 'shift-pagi';   $badgeClass = 'badge-pagi';   $badge = 'Pagi';   $badgeLabel = 'P';
                    } elseif (str_contains($namaShift, 'siang')) {
                        $cellClass = 'shift-siang';  $badgeClass = 'badge-siang';  $badge = 'Siang';  $badgeLabel = 'S';
                    } elseif (str_contains($namaShift, 'malam')) {
                        $cellClass = 'shift-malam';  $badgeClass = 'badge-malam';  $badge = 'Malam';  $badgeLabel = 'M';
                    } else {
                        $cellClass = 'shift-normal'; $badgeClass = 'badge-normal'; $badge = $shiftObj->nama_shift; $badgeLabel = 'N';
                    }

                    $cellExtra = ($isToday ? ' is-today' : '') . ($isPast ? ' is-past' : '') . ($isKhusus ? ' is-khusus' : '');
                    $tooltipShift = $shiftObj
                        ? $shiftObj->nama_shift . ($shiftObj->jam_masuk ? ' (' . substr($shiftObj->jam_masuk,0,5) . '–' . substr($shiftObj->jam_keluar,0,5) . ')' : '')
                        : ($isWeekend ? 'Libur' : '-');
                @endphp
                @php
                    $clickable = auth()->user()->hasPermission('shift.kelola');
                    $esId = $jadwalHari ? $jadwalHari->id : null;
                @endphp
                <div class="cal-cell {{ $cellClass }}{{ $cellExtra }}"
                     title="{{ $tooltipShift }}{{ $isKhusus ? ' ★ Jadwal Khusus' : '' }}{{ $clickable ? ' — klik untuk atur' : '' }}"
                     @if($clickable)
                     onclick="openFromCal('{{ $tglKey }}', {{ $esId ?? 'null' }}, {{ $esId ? $jadwalHari->shift_id : 'null' }}, '{{ $esId && $jadwalHari->keterangan ? addslashes($jadwalHari->keterangan) : '' }}')"
                     style="cursor:{{ $clickable ? 'pointer' : 'default' }};"
                     @endif>
                    <div class="cal-date{{ $isToday ? ' today' : '' }}{{ $isWeekend ? ' weekend' : '' }}">{{ $d }}</div>
                    @if($isToday)
                        <div class="cal-today-label">Hari Ini</div>
                    @endif
                    @if($shiftObj && $badge)
                        <div class="cal-shift-badge {{ $badgeClass }}">{{ $badge }}</div>
                        <div class="cal-time">{{ $shiftObj->jam_masuk ? substr($shiftObj->jam_masuk,0,5) : '' }}</div>
                    @elseif($isWeekend && !$jadwalHari)
                        <div class="cal-time" style="color:#cbd5e0;">Libur</div>
                    @endif
                    @if($isKhusus)
                        <div class="cal-khusus-label">★ Khusus</div>
                    @endif
                </div>
            @endfor
        </div>

        {{-- Legenda --}}
        <div class="legend-bar" style="margin-top:20px;">
            <span style="font-size:11px;font-weight:700;color:#718096;">Keterangan:</span>
            <span class="cal-shift-badge badge-pagi" style="font-size:11px;padding:2px 10px;">Pagi</span>
            <span class="cal-shift-badge badge-siang" style="font-size:11px;padding:2px 10px;">Siang</span>
            <span class="cal-shift-badge badge-malam" style="font-size:11px;padding:2px 10px;">Malam</span>
            <span class="cal-shift-badge badge-normal" style="font-size:11px;padding:2px 10px;">Normal</span>
            <span style="font-size:11px;outline:2px solid #ed8936;outline-offset:1px;border-radius:6px;padding:2px 10px;color:#c05621;">★ Jadwal Khusus</span>
            <span style="font-size:11px;border:2px solid #4299e1;border-radius:6px;padding:2px 10px;color:#2b6cb0;">Hari Ini</span>
        </div>
    </div>
</div>

{{-- Tabel detail jadwal khusus --}}
@if($jadwal->count() > 0)
<div class="khusus-card">
    <div class="khusus-header">
        <i class="fas fa-star" style="color:#ed8936;"></i>
        Detail Jadwal Khusus Bulan {{ $namaBulan }}
        <span style="margin-left:auto;font-size:12px;font-weight:400;color:#718096;">{{ $jadwal->count() }} hari</span>
    </div>
    <div style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwal->sortKeys() as $j)
                <tr>
                    <td style="font-weight:600;">{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
                    <td style="color:#718096;">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('l') }}</td>
                    <td>
                        @php
                            $sn = strtolower($j->shift?->nama_shift ?? '');
                            $bc = str_contains($sn,'pagi') ? 'badge-pagi' : (str_contains($sn,'siang') ? 'badge-siang' : (str_contains($sn,'malam') ? 'badge-malam' : 'badge-normal'));
                        @endphp
                        <span class="cal-shift-badge {{ $bc }}" style="font-size:12px;padding:3px 12px;">
                            {{ $j->shift?->nama_shift ?? '-' }}
                        </span>
                    </td>
                    <td style="font-weight:600;">{{ $j->shift?->jam_masuk ? substr($j->shift->jam_masuk,0,5) : '-' }}</td>
                    <td style="font-weight:600;">{{ $j->shift?->jam_keluar ? substr($j->shift->jam_keluar,0,5) : '-' }}</td>
                    <td style="color:#718096;font-size:13px;">{{ $j->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="cal-card" style="padding:24px 28px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:50%;background:#f0fff4;border:2px solid #9ae6b4;
                    display:flex;align-items:center;justify-content:center;font-size:18px;color:#38a169;flex-shrink:0;">
            <i class="fas fa-check"></i>
        </div>
        <div>
            <div style="font-weight:700;color:#2d3748;">Tidak ada jadwal khusus bulan ini</div>
            <div style="font-size:13px;color:#718096;margin-top:2px;">
                Semua hari kerja menggunakan shift default:
                <strong>{{ $karyawan->shift?->nama_shift ?? '-' }}</strong>
                @if($karyawan->shift)
                ({{ substr($karyawan->shift->jam_masuk,0,5) }} — {{ substr($karyawan->shift->jam_keluar,0,5) }})
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@endif

@if($karyawan && auth()->user()->hasPermission('shift.kelola'))
{{-- Modal Tambah/Edit Jadwal --}}
<div id="modalJadwal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:460px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 id="modalJadwalTitle" style="font-weight:700;color:#2d3748;margin:0;font-size:15px;">
                <i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Atur Jadwal Shift
            </h6>
            <button onclick="closeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>

        {{-- Info karyawan di dalam modal --}}
        <div style="background:#f7fafc;border-radius:9px;padding:10px 14px;margin-bottom:18px;font-size:13px;color:#4a5568;">
            <i class="fas fa-user" style="color:#4299e1;"></i>
            <strong>{{ $karyawan->nama }}</strong> &nbsp;—&nbsp; {{ $karyawan->nik }}
        </div>

        <form id="formJadwal" method="POST">
            @csrf
            <span id="methodSpoof"></span>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">
                    Tanggal <span style="color:#e53e3e;">*</span>
                </label>
                <input type="date" name="date" id="inputDate" class="form-control" required
                       style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">
                    Shift <span style="color:#e53e3e;">*</span>
                </label>
                <select name="shift_id" id="inputShift" class="form-select" required
                        style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_shift }} &nbsp;({{ substr($s->jam_masuk,0,5) }} – {{ substr($s->jam_keluar,0,5) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Keterangan</label>
                <input type="text" name="keterangan" id="inputKet" class="form-control"
                       placeholder="Keterangan (opsional)" maxlength="255"
                       style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;">
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" id="btnSubmit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button type="button" id="btnHapus" onclick="hapusJadwal()" class="btn btn-danger" style="display:none;">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </form>

        {{-- Form hapus tersembunyi --}}
        <form id="formHapus" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@push('scripts')
<script>
const storeUrl  = "{{ route('admin.employeshift.store', $karyawan) }}";
const baseEditUrl = "/admin/employeshifts/";
const returnUrl = "{{ route('admin.laporan.jadwal-karyawan', ['karyawan_id' => $karyawan->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}";

function openAddModal(tgl) {
    document.getElementById('modalJadwalTitle').innerHTML = '<i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Tambah Jadwal Shift';
    document.getElementById('formJadwal').action = storeUrl;
    document.getElementById('methodSpoof').innerHTML = '';
    document.getElementById('inputDate').value = tgl || '';
    document.getElementById('inputShift').value = '';
    document.getElementById('inputKet').value = '';
    document.getElementById('btnHapus').style.display = 'none';
    document.getElementById('btnSubmit').textContent = 'Simpan';
    // tambah hidden return url
    setReturnInput();
    document.getElementById('modalJadwal').style.display = 'flex';
}

function openFromCal(tgl, esId, shiftId, ket) {
    if (esId) {
        // Mode edit
        document.getElementById('modalJadwalTitle').innerHTML = '<i class="fas fa-calendar-edit" style="color:#ed8936;"></i> Edit Jadwal Shift';
        document.getElementById('formJadwal').action = baseEditUrl + esId;
        document.getElementById('methodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('formHapus').action = baseEditUrl + esId;
        document.getElementById('btnHapus').style.display = 'inline-flex';
    } else {
        // Mode tambah dengan tanggal sudah terisi
        document.getElementById('modalJadwalTitle').innerHTML = '<i class="fas fa-calendar-plus" style="color:#4299e1;"></i> Tambah Jadwal Shift';
        document.getElementById('formJadwal').action = storeUrl;
        document.getElementById('methodSpoof').innerHTML = '';
        document.getElementById('btnHapus').style.display = 'none';
    }
    document.getElementById('inputDate').value  = tgl;
    document.getElementById('inputShift').value = shiftId || '';
    document.getElementById('inputKet').value   = ket || '';
    setReturnInput();
    document.getElementById('modalJadwal').style.display = 'flex';
}

function setReturnInput() {
    // Hapus input lama kalau ada
    const old = document.getElementById('hiddenReturn');
    if (old) old.remove();
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = '_redirect'; inp.id = 'hiddenReturn';
    inp.value = returnUrl;
    document.getElementById('formJadwal').appendChild(inp);
}

function hapusJadwal() {
    if (!confirm('Hapus jadwal khusus hari ini? Karyawan akan kembali ke shift default.')) return;
    document.getElementById('formHapus').submit();
}

function closeModal() {
    document.getElementById('modalJadwal').style.display = 'none';
}

document.getElementById('modalJadwal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endif

@endsection
