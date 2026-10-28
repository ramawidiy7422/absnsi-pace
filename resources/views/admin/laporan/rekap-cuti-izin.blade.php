@extends('layouts.admin')
@section('title','Rekap Cuti & Izin')
@section('page-title','Rekap Cuti & Izin')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-calendar-alt"></i> Rekap Cuti & Izin</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">{{ $namaBulan }}{{ $department_id ? ' — '.($departemenList->firstWhere('id', $department_id)?->name ?? '') : '' }}</p>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="padding:8px 12px;font-size:13px;">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m==$bulan?'selected':'' }}>{{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="padding:8px 12px;font-size:13px;">
                    @foreach(range(now()->year+1, now()->year-2, -1) as $y)
                    <option value="{{ $y }}" {{ $y==$tahun?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($departemenList as $d)
                    <option value="{{ $d->id }}" {{ $d->id==$department_id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.laporan.rekap-cuti-izin') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
            <a href="{{ route('admin.laporan.rekap-cuti-izin.export-pdf', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$department_id]) }}"
               class="btn" style="background:#c53030;color:white;" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            @if(auth()->user()->hasPermission('laporan.export'))
            <a href="{{ route('admin.laporan.rekap-cuti-izin.export-csv', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$department_id]) }}"
               class="btn" style="background:#276749;color:white;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            @endif
        </form>
    </div>
</div>

{{-- Ringkasan per Departemen --}}
@if($ringkasanDept->count() > 1)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px;">
    @foreach($ringkasanDept as $dept => $r)
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
        <div style="font-weight:800;color:#2d3748;font-size:13px;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #f0f0f0;">
            <i class="fas fa-building" style="color:#4299e1;"></i> {{ $dept }}
            <span style="float:right;font-size:11px;color:#718096;font-weight:400;">{{ $r['karyawan'] }} org</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:12px;">
            <div style="background:#e6f9f0;border-radius:6px;padding:6px;text-align:center;">
                <div style="font-weight:800;color:#276749;">{{ $r['cuti'] }}</div>
                <div style="font-size:10px;color:#718096;">Cuti</div>
            </div>
            <div style="background:#ebf8ff;border-radius:6px;padding:6px;text-align:center;">
                <div style="font-weight:800;color:#2b6cb0;">{{ $r['sakit'] }}</div>
                <div style="font-size:10px;color:#718096;">Sakit</div>
            </div>
            <div style="background:#fef9c3;border-radius:6px;padding:6px;text-align:center;">
                <div style="font-weight:800;color:#854d0e;">{{ $r['izin'] }}</div>
                <div style="font-size:10px;color:#718096;">Izin</div>
            </div>
            <div style="background:#fed7e2;border-radius:6px;padding:6px;text-align:center;">
                <div style="font-weight:800;color:#c53030;">{{ $r['alfa'] }}</div>
                <div style="font-size:10px;color:#718096;">Alfa</div>
            </div>
        </div>
        @if($r['pending'] > 0)
        <div style="margin-top:8px;font-size:11px;color:#c05621;font-weight:600;text-align:center;">
            <i class="fas fa-clock"></i> {{ $r['pending'] }} pengajuan pending
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- Tabel Detail --}}
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-table" style="color:#4299e1;"></i> Detail per Karyawan</h6>
        <span style="font-size:12px;color:#718096;">{{ $rekap->count() }} karyawan</span>
    </div>
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Karyawan</th>
                    <th style="padding:10px 8px;font-weight:700;color:#276749;text-align:center;">Cuti<br><small style="font-weight:400;color:#a0aec0;">Disetujui</small></th>
                    <th style="padding:10px 8px;font-weight:700;color:#854d0e;text-align:center;">Cuti<br><small style="font-weight:400;color:#a0aec0;">Pending</small></th>
                    <th style="padding:10px 8px;font-weight:700;color:#2b6cb0;text-align:center;">Sakit</th>
                    <th style="padding:10px 8px;font-weight:700;color:#c05621;text-align:center;">Izin</th>
                    <th style="padding:10px 8px;font-weight:700;color:#c53030;text-align:center;">Alfa</th>
                    <th style="padding:10px 8px;font-weight:700;color:#553c9a;text-align:center;">Izin<br><small style="font-weight:400;color:#a0aec0;">Pending</small></th>
                    <th style="padding:10px 8px;font-weight:700;color:#4a5568;text-align:center;">Total<br><small style="font-weight:400;color:#a0aec0;">Tidak Masuk</small></th>
                </tr>
            </thead>
            <tbody>
                @php $prevDept = null; @endphp
                @foreach($rekap as $r)
                @php $deptName = $r['karyawan']->department?->name ?? $r['karyawan']->departemen ?? 'Tanpa Departemen'; @endphp
                @if($deptName !== $prevDept)
                <tr style="background:#f3e8ff;">
                    <td colspan="8" style="padding:6px 12px;font-weight:800;color:#553c9a;font-size:11px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fas fa-building"></i> {{ $deptName }}
                    </td>
                </tr>
                @php $prevDept = $deptName; @endphp
                @endif
                @php
                    $total = $r['cuti_disetujui'] + $r['izin_sakit'] + $r['izin_izin'] + $r['izin_alfa'];
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f7fafc'" onmouseout="this.style.background=''">
                    <td style="padding:10px 12px;">
                        <a href="{{ route('admin.karyawan.show', $r['karyawan']->id) }}" style="font-weight:700;color:#2d3748;text-decoration:none;">{{ $r['karyawan']->nama }}</a>
                        <div style="font-size:11px;color:#a0aec0;">{{ $r['karyawan']->jabatan }}</div>
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['cuti_disetujui'] > 0)<span style="background:#e6f9f0;color:#276749;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['cuti_disetujui'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['cuti_pending'] > 0)<span style="background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['cuti_pending'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['izin_sakit'] > 0)<span style="background:#ebf8ff;color:#2b6cb0;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['izin_sakit'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['izin_izin'] > 0)<span style="background:#fffbeb;color:#c05621;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['izin_izin'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['izin_alfa'] > 0)<span style="background:#fed7e2;color:#c53030;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['izin_alfa'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($r['izin_pending'] > 0)<span style="background:#f3e8ff;color:#553c9a;padding:2px 8px;border-radius:12px;font-weight:700;">{{ $r['izin_pending'] }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                    <td style="text-align:center;padding:10px 8px;">
                        @if($total > 0)<span style="background:#2d3748;color:white;padding:2px 10px;border-radius:12px;font-weight:800;">{{ $total }}</span>@else<span style="color:#e2e8f0;">—</span>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
