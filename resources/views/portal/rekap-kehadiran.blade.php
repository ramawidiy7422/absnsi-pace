@extends('layouts.admin')
@section('title', 'Rekap Kehadiran Saya')
@section('page-title', 'Rekap Kehadiran')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#1a202c,#2d3748);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-chart-bar"></i> Rekap Kehadiran Saya</h4>
        <p style="margin:4px 0 0;opacity:.8;font-size:13px;">{{ $karyawan->nama }} &mdash; Tahun {{ $tahun }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <select name="tahun" class="form-select" style="padding:7px 12px;font-size:13px;background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;" onchange="this.form.submit()">
                @foreach($tahunList as $y)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }} style="color:#2d3748;">{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('portal.slip-pdf', ['bulan' => now()->month, 'tahun' => $tahun]) }}"
           style="background:rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-file-pdf"></i> Slip Absensi
        </a>
    </div>
</div>

{{-- Total Tahunan --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:linear-gradient(135deg,#276749,#38a169);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['hadir'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Hadir</div>
    </div>
    <div style="background:linear-gradient(135deg,#c05621,#ed8936);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['terlambat'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Terlambat</div>
    </div>
    <div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['izin'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Izin</div>
    </div>
    <div style="background:linear-gradient(135deg,#97266d,#d53f8c);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['sakit'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Sakit</div>
    </div>
    <div style="background:linear-gradient(135deg,#553c9a,#805ad5);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['cuti'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Cuti</div>
    </div>
    <div style="background:linear-gradient(135deg,#c53030,#e53e3e);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['alpha'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Alpha</div>
    </div>
</div>

{{-- Tabel Rekap Per Bulan --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-table" style="color:#2b6cb0;"></i> Rekap Per Bulan &mdash; Tahun {{ $tahun }}</h6>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">Bulan</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#4a5568;">Hari Kerja</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#276749;">Hadir</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#c05621;">Terlambat</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#2b6cb0;">Izin</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#97266d;">Sakit</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#553c9a;">Cuti</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#c53030;">Alpha</th>
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapBulanan as $r)
                @php
                    $isFuture = \Carbon\Carbon::createFromDate($tahun, $r['bulan'], 1)->startOfMonth()->gt(now());
                    $pct      = $r['persen'];
                    $barColor = $pct >= 90 ? '#38a169' : ($pct >= 70 ? '#ed8936' : '#e53e3e');
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;{{ $isFuture ? 'opacity:0.4;' : '' }}"
                    onmouseover="{{ !$isFuture ? 'this.style.background=\"#f7fafc\"' : '' }}" onmouseout="this.style.background=''">
                    <td style="padding:10px 16px;font-weight:700;color:#2d3748;">
                        {{ \Carbon\Carbon::createFromDate($tahun, $r['bulan'], 1)->translatedFormat('F') }}
                        @if($r['bulan'] == now()->month && $tahun == now()->year)
                        <span style="font-size:10px;background:#ebf8ff;color:#2b6cb0;padding:1px 7px;border-radius:10px;margin-left:6px;font-weight:700;">Ini</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;color:#718096;">{{ $r['hari_kerja'] }}</td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['hadir'] > 0)
                        <span style="font-weight:700;color:#276749;">{{ $r['hadir'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['terlambat'] > 0)
                        <span style="font-weight:700;color:#c05621;">{{ $r['terlambat'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['izin'] > 0)
                        <span style="font-weight:700;color:#2b6cb0;">{{ $r['izin'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['sakit'] > 0)
                        <span style="font-weight:700;color:#97266d;">{{ $r['sakit'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['cuti'] > 0)
                        <span style="font-weight:700;color:#553c9a;">{{ $r['cuti'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['alpha'] > 0)
                        <span style="font-weight:800;color:#c53030;">{{ $r['alpha'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 16px;min-width:120px;">
                        @if(!$isFuture && $r['hari_kerja'] > 0)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#e2e8f0;border-radius:99px;height:7px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:{{ $barColor }};height:100%;border-radius:99px;transition:width 0.4s;"></div>
                            </div>
                            <span style="font-size:11px;font-weight:700;color:{{ $barColor }};white-space:nowrap;">{{ $pct }}%</span>
                        </div>
                        @else
                        <span style="color:#a0aec0;font-size:12px;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f7fafc;border-top:2px solid #e2e8f0;">
                    <td style="padding:10px 16px;font-weight:800;color:#2d3748;">Total {{ $tahun }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:700;color:#718096;">{{ $rekapBulanan->sum('hari_kerja') }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#276749;">{{ $totalRekap['hadir'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#c05621;">{{ $totalRekap['terlambat'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#2b6cb0;">{{ $totalRekap['izin'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#97266d;">{{ $totalRekap['sakit'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#553c9a;">{{ $totalRekap['cuti'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#c53030;">{{ $totalRekap['alpha'] }}</td>
                    <td style="padding:10px 16px;">
                        @php
                            $totalHk = $rekapBulanan->sum('hari_kerja');
                            $totalPct = $totalHk > 0 ? round($totalRekap['hadir'] / $totalHk * 100) : 0;
                            $totalBarColor = $totalPct >= 90 ? '#38a169' : ($totalPct >= 70 ? '#ed8936' : '#e53e3e');
                        @endphp
                        <span style="font-weight:800;color:{{ $totalBarColor }};">{{ $totalPct }}%</span>
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

{{-- Chart Bar Kehadiran --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-chart-bar" style="color:#4299e1;"></i> Grafik Kehadiran {{ $tahun }}</h6>
    </div>
    <div class="card-body">
        <canvas id="chartKehadiran" height="90"></canvas>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(function () {
    const labels = @json($rekapBulanan->pluck('nama_bulan'));
    const hadir     = @json($rekapBulanan->pluck('hadir'));
    const terlambat = @json($rekapBulanan->pluck('terlambat'));
    const alpha     = @json($rekapBulanan->pluck('alpha'));
    const izin      = @json($rekapBulanan->pluck('izin'));
    const sakit     = @json($rekapBulanan->pluck('sakit'));

    const ctx = document.getElementById('chartKehadiran');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Hadir', data: hadir, backgroundColor: 'rgba(56,161,105,0.8)', borderRadius: 4 },
                { label: 'Terlambat', data: terlambat, backgroundColor: 'rgba(237,137,54,0.8)', borderRadius: 4 },
                { label: 'Izin', data: izin, backgroundColor: 'rgba(66,153,225,0.7)', borderRadius: 4 },
                { label: 'Sakit', data: sakit, backgroundColor: 'rgba(213,63,140,0.7)', borderRadius: 4 },
                { label: 'Alpha', data: alpha, backgroundColor: 'rgba(229,62,62,0.8)', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { stacked: false, grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
@endpush
@endsection
