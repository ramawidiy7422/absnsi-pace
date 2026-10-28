@extends('layouts.admin')
@section('title', 'Rekap Lembur')
@section('page-title', 'Rekap Lembur')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#c05621,#ed8936);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-business-time"></i> Rekap Lembur</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">
            {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
            @if($department_id) &mdash; Dept. {{ $deptList->firstWhere('id', $department_id)?->name }} @endif
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.lemburan.rekap.excel', request()->query()) }}" style="background:rgba(39,103,73,0.85);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.lemburan.rekap.csv', request()->query()) }}" style="background:rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.lemburan.rekap.pdf', request()->query()) }}" style="background:rgba(220,53,69,0.7);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        @endif
        <a href="{{ route('admin.lemburan.index') }}" style="background:rgba(255,255,255,0.2);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            <i class="fas fa-list"></i> Semua Lembur
        </a>
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
                    <option value="{{ $m }}" {{ $bulan==$m?'selected':'' }}>
                        {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                    </option>
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
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.lemburan.rekap') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
    <div class="stat-card yellow" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ number_format($totalJam,1) }}</div>
                <div class="stat-label">Total Jam Lembur</div>
            </div>
            <div class="stat-icon" style="font-size:28px;"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="stat-card green" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $totalKary }}</div>
                <div class="stat-label">Karyawan Lembur</div>
            </div>
            <div class="stat-icon" style="font-size:28px;"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="stat-card blue" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $totalTranx }}</div>
                <div class="stat-label">Total Sesi Lembur</div>
            </div>
            <div class="stat-icon" style="font-size:28px;"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;">

    {{-- Rekap Per Karyawan --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-user-clock" style="color:#c05621;"></i> Rekap Per Karyawan</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Karyawan</th>
                            <th>Departemen</th>
                            <th style="text-align:center;">Sesi</th>
                            <th style="text-align:center;">Total Jam</th>
                            <th style="text-align:center;">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapKaryawan as $i => $r)
                        @php
                            $pct = $totalJam > 0 ? min(100, round($r['total_jam']/$totalJam*100)) : 0;
                        @endphp
                        <tr>
                            <td style="color:#a0aec0;font-size:12px;">{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight:700;color:#2d3748;">{{ $r['karyawan']?->nama ?? '-' }}</div>
                                <small style="color:#a0aec0;">{{ $r['karyawan']?->nik ?? '' }}</small>
                            </td>
                            <td style="font-size:12px;color:#718096;">{{ $r['karyawan']?->departemen ?? '-' }}</td>
                            <td style="text-align:center;font-weight:700;color:#2d3748;">{{ $r['total_hari'] }}</td>
                            <td style="text-align:center;">
                                <div style="font-weight:800;color:#c05621;font-size:15px;">{{ number_format($r['total_jam'],1) }}</div>
                                <div style="background:#e2e8f0;border-radius:99px;height:5px;margin-top:4px;overflow:hidden;">
                                    <div style="width:{{ $pct }}%;background:#ed8936;height:100%;border-radius:99px;"></div>
                                </div>
                            </td>
                            <td style="text-align:center;color:#718096;font-size:13px;">
                                {{ $r['total_hari'] > 0 ? number_format($r['total_jam']/$r['total_hari'],1) : 0 }} j/sesi
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#a0aec0;">
                                <i class="fas fa-business-time" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                Tidak ada data lembur approved bulan ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rekap Per Departemen --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-building" style="color:#276749;"></i> Per Departemen</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 14px;font-weight:700;color:#4a5568;">Departemen</th>
                        <th style="padding:10px 10px;text-align:center;font-weight:700;color:#4a5568;">Kary.</th>
                        <th style="padding:10px 10px;text-align:center;font-weight:700;color:#4a5568;">Total Jam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapDept as $dept => $r)
                    @php $pct = $totalJam > 0 ? min(100, round($r['total_jam']/$totalJam*100)) : 0; @endphp
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 14px;font-weight:600;color:#2d3748;">{{ $dept }}</td>
                        <td style="padding:10px;text-align:center;color:#718096;">{{ $r['total_kary'] }}</td>
                        <td style="padding:10px;">
                            <div style="font-weight:700;color:#276749;font-size:14px;text-align:center;">{{ number_format($r['total_jam'],1) }}j</div>
                            <div style="background:#e2e8f0;border-radius:99px;height:4px;margin-top:4px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:#38a169;height:100%;border-radius:99px;"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:24px;color:#a0aec0;">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
