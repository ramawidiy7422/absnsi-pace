<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Lembur - {{ $namaBulan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #7b341e; color: white; margin-bottom: 14px; }
        table.header-table td { padding: 14px 20px; color: white; }
        .company { font-size: 13px; font-weight: 700; }
        .sub { font-size: 9px; opacity: 0.7; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: 700; text-align: right; }
        .period { font-size: 10px; opacity: 0.85; margin-top: 3px; text-align: right; }
        .generated { font-size: 9px; opacity: 0.6; margin-top: 2px; text-align: right; }

        .body { padding: 0 18px 18px; }

        table.summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 14px; }
        .sum-box { text-align: center; padding: 10px 6px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .sv { font-size: 22px; font-weight: 800; line-height: 1; }
        .sl { font-size: 8px; color: #718096; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .sum-org  { background: #fffaf0; border-color: #fbd38d; } .sum-org .sv  { color: #c05621; }
        .sum-blue { background: #ebf8ff; border-color: #90cdf4; } .sum-blue .sv { color: #2b6cb0; }
        .sum-grey { background: #f7fafc; border-color: #e2e8f0; } .sum-grey .sv { color: #4a5568; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #2d3748;
            margin: 14px 0 8px; padding-bottom: 4px;
            border-bottom: 2px solid #c05621;
        }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9px; }
        table.data th { background: #c05621; color: white; padding: 5px 7px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.data td { padding: 4px 7px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        table.data tr:nth-child(even) td { background: #fafafa; }

        .rank-badge { display: inline-block; width: 18px; height: 18px; border-radius: 50%; text-align: center; line-height: 18px; font-weight: 800; font-size: 8px; }
        .rank-1 { background: #ffd700; color: #744210; }
        .rank-2 { background: #c0c0c0; color: #2d3748; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-n { background: #e2e8f0; color: #4a5568; }

        .dept-badge { display: inline-block; padding: 1px 7px; border-radius: 10px; background: #ebf8ff; color: #2b6cb0; border: 1px solid #90cdf4; font-size: 8px; font-weight: 700; }

        table.footer-table { width: 100%; margin-top: 12px; border-top: 1px solid #e2e8f0; }
        table.footer-table td { font-size: 8px; color: #a0aec0; padding: 8px 18px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="company">{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</div>
            <div class="sub">
                Sistem Informasi Absensi &amp; HR
                @if(!empty($alamatPerusahaan)) &bull; {{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }} @endif
                @if(!empty($noTelpPerusahaan)) &bull; Telp: {{ $noTelpPerusahaan }} @endif
            </div>
        </td>
        <td style="width:40%;">
            <div class="report-title">Laporan Rekap Lembur</div>
            <div class="period">
                Periode: {{ $namaBulan }}
                @if($dept) &mdash; Dept: {{ $dept->name }} @endif
            </div>
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sum-box sum-org">
                <div class="sv">{{ number_format($totalJam, 1) }}</div>
                <div class="sl">Total Jam Lembur</div>
            </td>
            <td class="sum-box sum-blue">
                <div class="sv">{{ $totalKary }}</div>
                <div class="sl">Karyawan Lembur</div>
            </td>
            <td class="sum-box sum-grey">
                <div class="sv">{{ $rekapDept->count() }}</div>
                <div class="sl">Departemen</div>
            </td>
            <td class="sum-box sum-grey">
                <div class="sv">{{ $rekapKaryawan->count() > 0 ? number_format($rekapKaryawan->first()['total_jam'], 1) : '-' }}</div>
                <div class="sl">Jam Tertinggi</div>
            </td>
        </tr>
    </table>

    @if($rekapDept->isNotEmpty())
    <div class="section-title">Rekap per Departemen</div>
    <table class="data">
        <thead>
            <tr>
                <th>Departemen</th>
                <th style="text-align:center;">Karyawan</th>
                <th style="text-align:center;">Total Jam</th>
                <th style="text-align:center;">Rata-rata / Orang</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapDept as $deptName => $rd)
            <tr>
                <td><span class="dept-badge">{{ $deptName }}</span></td>
                <td style="text-align:center;">{{ $rd['total_kary'] }}</td>
                <td style="text-align:center;font-weight:700;color:#c05621;">{{ number_format($rd['total_jam'], 1) }} jam</td>
                <td style="text-align:center;color:#718096;">{{ $rd['total_kary'] > 0 ? number_format($rd['total_jam'] / $rd['total_kary'], 1) : '-' }} jam</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Ranking Lembur Terbanyak</div>
    @if($rekapKaryawan->isEmpty())
        <p style="color:#718096;font-style:italic;margin-bottom:12px;">Tidak ada data lembur approved.</p>
    @else
    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th style="text-align:center;">Hari</th>
                <th style="text-align:center;">Total Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapKaryawan as $i => $r)
            @php $rankClass = match($i+1) { 1=>'rank-1', 2=>'rank-2', 3=>'rank-3', default=>'rank-n' }; @endphp
            <tr>
                <td style="text-align:center;"><span class="rank-badge {{ $rankClass }}">{{ $i+1 }}</span></td>
                <td>{{ $r['karyawan']?->nik ?? '-' }}</td>
                <td style="font-weight:600;">{{ $r['karyawan']?->nama ?? '-' }}</td>
                <td>{{ $r['karyawan']?->department?->name ?? $r['karyawan']?->departemen ?? '-' }}</td>
                <td style="text-align:center;">{{ $r['total_hari'] }}</td>
                <td style="text-align:center;font-weight:800;color:#c05621;">{{ number_format($r['total_jam'], 1) }} jam</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }} &mdash; Rekap Lembur {{ $namaBulan }}</td>
        <td style="text-align:right;">Dicetak dari Sistem Absensi Digital</td>
    </tr>
</table>

</body>
</html>
