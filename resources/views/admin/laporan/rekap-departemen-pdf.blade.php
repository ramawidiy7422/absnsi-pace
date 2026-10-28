<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

    table.header-table { width: 100%; background: #276749; color: white; margin-bottom: 16px; }
    table.header-table td { padding: 16px 20px; color: white; }
    .header-title { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
    .header-sub { font-size: 11px; opacity: 0.9; }

    table.summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 14px; }
    .sum-box { border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; text-align: center; background: #f7fafc; }
    .num { font-size: 20px; font-weight: 800; }
    .lbl { font-size: 9px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

    table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.data thead tr { background: #276749; color: white; }
    table.data thead th { padding: 8px 10px; text-align: center; font-size: 9px; font-weight: 700; letter-spacing: 0.3px; }
    table.data thead th:first-child { text-align: left; }
    table.data tbody tr:nth-child(even) { background: #f7fafc; }
    table.data tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9.5px; text-align: center; }
    table.data tbody td:first-child { text-align: left; font-weight: 600; }

    .badge-green  { background: #c6f6d5; color: #276749; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .badge-yellow { background: #fefcbf; color: #744210; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .badge-red    { background: #fed7d7; color: #c53030; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .badge-blue   { background: #bee3f8; color: #2b6cb0; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }

    .bar-outer { display: block; background: #e2e8f0; border-radius: 3px; height: 8px; width: 60px; }
    .bar-inner { display: block; border-radius: 3px; height: 8px; }

    tfoot tr { background: #2d3748; color: white; font-weight: 700; }
    tfoot td { padding: 8px 10px; font-size: 9.5px; text-align: center; }
    tfoot td:first-child { text-align: left; }

    .footer { font-size: 9px; color: #a0aec0; text-align: right; margin-top: 10px; padding-right: 4px; }
</style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <div class="header-title">Rekap Absensi Per Departemen</div>
            <div class="header-sub">
                {{ $namaPerusahaan }} &nbsp;|&nbsp; Periode: {{ $namaBulan }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
                @if(!empty($alamatPerusahaan)) &nbsp;|&nbsp; {{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }} @endif
                @if(!empty($noTelpPerusahaan)) &nbsp;|&nbsp; Telp: {{ $noTelpPerusahaan }} @endif
            </div>
        </td>
    </tr>
</table>

@php
    $totalKary  = $rekapDept->sum('jumlah_karyawan');
    $totalHadir = $rekapDept->sum('hadir') + $rekapDept->sum('terlambat');
    $totalAlpha = $rekapDept->sum('alpha');
    $totalRec   = $rekapDept->sum('total');
    $avgPersen  = $totalRec > 0 ? round($totalHadir / $totalRec * 100) : 0;
@endphp

<table class="summary-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="sum-box">
            <div class="num" style="color:#276749;">{{ $rekapDept->count() }}</div>
            <div class="lbl">Departemen Aktif</div>
        </td>
        <td class="sum-box">
            <div class="num" style="color:#2b6cb0;">{{ $totalKary }}</div>
            <div class="lbl">Total Karyawan</div>
        </td>
        <td class="sum-box">
            <div class="num" style="color:#c53030;">{{ $totalAlpha }}</div>
            <div class="lbl">Total Alpha</div>
        </td>
        <td class="sum-box">
            <div class="num" style="color:#{{ $avgPersen >= 90 ? '276749' : ($avgPersen >= 70 ? 'c05621' : 'c53030') }};">{{ $avgPersen }}%</div>
            <div class="lbl">Rata-rata Kehadiran</div>
        </td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th style="text-align:left;width:22%;">Departemen</th>
            <th>Karyawan</th>
            <th>Hadir</th>
            <th>Terlambat</th>
            <th>Izin</th>
            <th>Sakit</th>
            <th>Alpha</th>
            <th>Total Record</th>
            <th>% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekapDept as $r)
        @php
            $persen = $r['persen_hadir'];
            $barPx  = max(2, min(60, intval($persen * 0.6)));
            $barColor = $persen >= 90 ? '#48bb78' : ($persen >= 70 ? '#ed8936' : '#fc8181');
            $txtColor = $persen >= 90 ? '#276749' : ($persen >= 70 ? '#c05621' : '#c53030');
        @endphp
        <tr>
            <td>{{ $r['departemen'] }}</td>
            <td>{{ $r['jumlah_karyawan'] }}</td>
            <td><span class="badge-green">{{ $r['hadir'] }}</span></td>
            <td><span class="badge-yellow">{{ $r['terlambat'] }}</span></td>
            <td><span class="badge-blue">{{ $r['izin'] }}</span></td>
            <td><span class="badge-blue">{{ $r['sakit'] }}</span></td>
            <td><span class="badge-red">{{ $r['alpha'] }}</span></td>
            <td>{{ $r['total'] }}</td>
            <td>
                <div class="bar-outer"><div class="bar-inner" style="background:{{ $barColor }};width:{{ $barPx }}px;"></div></div>
                <strong style="color:{{ $txtColor }};">{{ $persen }}%</strong>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td>{{ $totalKary }}</td>
            <td>{{ $rekapDept->sum('hadir') }}</td>
            <td>{{ $rekapDept->sum('terlambat') }}</td>
            <td>{{ $rekapDept->sum('izin') }}</td>
            <td>{{ $rekapDept->sum('sakit') }}</td>
            <td>{{ $totalAlpha }}</td>
            <td>{{ $totalRec }}</td>
            <td>{{ $avgPersen }}%</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    Dicetak dari Sistem Absensi PACE &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i:s') }}
</div>

</body>
</html>
