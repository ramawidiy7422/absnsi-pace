<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Karyawan - {{ $namaBulan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #276749; color: white; margin-bottom: 12px; }
        table.header-table td { padding: 12px 18px; color: white; }
        .company { font-size: 12px; font-weight: 700; }
        .sub { font-size: 8px; opacity: 0.75; margin-top: 2px; }
        .report-title { font-size: 12px; font-weight: 700; text-align: right; }
        .period { font-size: 9px; opacity: 0.85; margin-top: 2px; text-align: right; }
        .generated { font-size: 8px; opacity: 0.6; margin-top: 2px; text-align: right; }

        .body { padding: 0 16px 16px; }

        table.summary-table { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 12px; }
        .sum-box { text-align: center; padding: 8px 4px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .sv { font-size: 18px; font-weight: 800; line-height: 1; }
        .sl { font-size: 7px; color: #718096; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.3px; }
        .sum-blue  { background: #ebf8ff; border-color: #90cdf4; } .sum-blue .sv  { color: #2b6cb0; }
        .sum-green { background: #f0fff4; border-color: #9ae6b4; } .sum-green .sv { color: #276749; }
        .sum-oran  { background: #fffaf0; border-color: #fbd38d; } .sum-oran .sv  { color: #c05621; }
        .sum-red   { background: #fff5f5; border-color: #feb2b2; } .sum-red .sv   { color: #c53030; }
        .sum-purp  { background: #faf5ff; border-color: #d6bcfa; } .sum-purp .sv  { color: #6b46c1; }

        table.data { width: 100%; border-collapse: collapse; font-size: 8px; }
        table.data thead th { background: #276749; color: white; padding: 5px 6px; text-align: left; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.data td { padding: 4px 6px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        table.data tr:nth-child(even) td { background: #fafafa; }

        .dept-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; background: #ebf8ff; color: #2b6cb0; border: 1px solid #90cdf4; font-size: 7.5px; font-weight: 700; }

        .bar-outer { display: block; background: #e2e8f0; border-radius: 3px; height: 6px; width: 60px; }
        .bar-inner { display: block; background: #48bb78; border-radius: 3px; height: 6px; }
        .bar-inner.low { background: #fc8181; }
        .bar-inner.mid { background: #ed8936; }

        tfoot td { background: #f7fafc; font-weight: 800; border-top: 2px solid #276749; }

        table.footer-table { width: 100%; margin-top: 10px; border-top: 1px solid #e2e8f0; }
        table.footer-table td { font-size: 7px; color: #a0aec0; padding: 6px 16px; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="company">{{ $namaPerusahaan }}</div>
            <div class="sub">
                Sistem Informasi Absensi &amp; HR
                @if(!empty($alamatPerusahaan)) &nbsp;&bull;&nbsp; {{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }} @endif
                @if(!empty($noTelpPerusahaan)) &nbsp;&bull;&nbsp; Telp: {{ $noTelpPerusahaan }} @endif
            </div>
        </td>
        <td style="width:40%;">
            <div class="report-title">Rekap Absensi Per Karyawan</div>
            <div class="period">Periode: {{ $namaBulan }}{{ $dept ? ' &mdash; ' . $dept->name : '' }}</div>
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    @php
        $totHadir    = $rekap->sum('hadir');
        $totTelat    = $rekap->sum('terlambat');
        $totIzin     = $rekap->sum('izin');
        $totSakit    = $rekap->sum('sakit');
        $totAlpha    = $rekap->sum('alpha');
        $totRecords  = $rekap->sum('total');
        $avgPct      = $rekap->count() > 0 ? round($rekap->avg('pct_hadir')) : 0;
    @endphp

    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sum-box sum-blue">
                <div class="sv">{{ $rekap->count() }}</div>
                <div class="sl">Karyawan</div>
            </td>
            <td class="sum-box sum-green">
                <div class="sv">{{ $totHadir }}</div>
                <div class="sl">Total Hadir</div>
            </td>
            <td class="sum-box sum-oran">
                <div class="sv">{{ $totTelat }}</div>
                <div class="sl">Terlambat</div>
            </td>
            <td class="sum-box sum-purp">
                <div class="sv">{{ $totIzin + $totSakit }}</div>
                <div class="sl">Izin + Sakit</div>
            </td>
            <td class="sum-box sum-red">
                <div class="sv">{{ $totAlpha }}</div>
                <div class="sl">Alpha</div>
            </td>
            <td class="sum-box sum-green">
                <div class="sv">{{ $avgPct }}%</div>
                <div class="sl">Rata Kehadiran</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:22px;">#</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th>Jabatan</th>
                <th style="text-align:center;">Hadir</th>
                <th style="text-align:center;">Terlambat</th>
                <th style="text-align:center;">Izin</th>
                <th style="text-align:center;">Sakit</th>
                <th style="text-align:center;">Alpha</th>
                <th style="text-align:center;">Total</th>
                <th style="text-align:center;width:80px;">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $i => $r)
            @php $pct = $r['pct_hadir']; $barClass = $pct >= 80 ? '' : ($pct >= 60 ? 'mid' : 'low'); @endphp
            <tr>
                <td style="text-align:center;color:#a0aec0;">{{ $i + 1 }}</td>
                <td style="font-size:7.5px;color:#718096;">{{ $r['karyawan']->nik ?? '-' }}</td>
                <td style="font-weight:600;">{{ $r['karyawan']->nama }}</td>
                <td><span class="dept-badge">{{ $r['karyawan']->department?->name ?? $r['karyawan']->departemen ?? '-' }}</span></td>
                <td style="color:#718096;">{{ $r['karyawan']->jabatan ?? '-' }}</td>
                <td style="text-align:center;font-weight:700;color:#276749;">{{ $r['hadir'] }}</td>
                <td style="text-align:center;color:#c05621;">{{ $r['terlambat'] }}</td>
                <td style="text-align:center;color:#2b6cb0;">{{ $r['izin'] }}</td>
                <td style="text-align:center;color:#6b46c1;">{{ $r['sakit'] }}</td>
                <td style="text-align:center;color:#c53030;font-weight:{{ $r['alpha'] > 0 ? '700' : '400' }};">{{ $r['alpha'] }}</td>
                <td style="text-align:center;color:#718096;">{{ $r['total'] }}</td>
                <td style="text-align:center;">
                    <span style="font-weight:700;color:{{ $pct >= 80 ? '#276749' : ($pct >= 60 ? '#c05621' : '#c53030') }};">{{ $pct }}%</span>
                    <div class="bar-outer"><div class="bar-inner {{ $barClass }}" style="width:{{ $pct }}%;"></div></div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;padding-right:10px;">TOTAL</td>
                <td style="text-align:center;color:#276749;">{{ $totHadir }}</td>
                <td style="text-align:center;color:#c05621;">{{ $totTelat }}</td>
                <td style="text-align:center;color:#2b6cb0;">{{ $totIzin }}</td>
                <td style="text-align:center;color:#6b46c1;">{{ $totSakit }}</td>
                <td style="text-align:center;color:#c53030;">{{ $totAlpha }}</td>
                <td style="text-align:center;">{{ $totRecords }}</td>
                <td style="text-align:center;color:#276749;">{{ $avgPct }}%</td>
            </tr>
        </tfoot>
    </table>

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan }} &mdash; Rekap Per Karyawan {{ $namaBulan }}</td>
        <td style="text-align:right;">Dicetak dari Sistem Absensi Digital &mdash; {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>

</body>
</html>
