<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keterlambatan - {{ $namaBulan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #1a1f36; color: white; margin-bottom: 16px; }
        table.header-table td { padding: 16px 20px; color: white; }
        .company { font-size: 13px; font-weight: 700; }
        .sub { font-size: 9px; opacity: 0.7; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: 700; text-align: right; }
        .period { font-size: 11px; opacity: 0.85; margin-top: 3px; text-align: right; }
        .generated { font-size: 9px; opacity: 0.6; margin-top: 2px; text-align: right; }

        .body { padding: 0 20px 20px; }

        table.summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .sum-box { text-align: center; padding: 10px 6px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .sv { font-size: 20px; font-weight: 800; line-height: 1; }
        .sl { font-size: 8px; color: #718096; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .sum-orange { background: #fffaf0; border-color: #fbd38d; } .sum-orange .sv { color: #c05621; }
        .sum-red    { background: #fff5f5; border-color: #fed7d7; } .sum-red .sv    { color: #c53030; }
        .sum-blue   { background: #ebf8ff; border-color: #90cdf4; } .sum-blue .sv   { color: #2b6cb0; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #2d3748;
            margin: 16px 0 8px; padding-bottom: 4px;
            border-bottom: 2px solid #e53e3e;
        }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.data th { background: #e53e3e; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.data td { padding: 5px 8px; font-size: 9px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f9fafb; }

        .rank-badge { display: inline-block; width: 20px; height: 20px; border-radius: 50%; text-align: center; line-height: 20px; font-weight: 800; font-size: 9px; }
        .rank-1 { background: #ffd700; color: #744210; }
        .rank-2 { background: #c0c0c0; color: #2d3748; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-n { background: #e2e8f0; color: #4a5568; }

        .badge-terlambat { display: inline-block; padding: 1px 6px; border-radius: 10px; background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; font-weight: 700; font-size: 9px; }
        .dates-list { color: #718096; font-size: 8px; line-height: 1.6; }

        table.footer-table { width: 100%; margin-top: 20px; border-top: 1px solid #e2e8f0; }
        table.footer-table td { font-size: 8px; color: #a0aec0; padding: 10px 20px; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="company">{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</div>
            <div class="sub">@php
                $sl=[];
                if(!empty($alamatPerusahaan)){$sl[]=$alamatPerusahaan.(!empty($kotaPerusahaan)?', '.$kotaPerusahaan:'');}
                if(!empty($noTelpPerusahaan)){$sl[]='Telp: '.$noTelpPerusahaan;}
                echo empty($sl)?'Sistem Informasi Absensi &amp; HR':implode(' &bull; ',$sl);
            @endphp</div>
        </td>
        <td style="width:40%;">
            <div class="report-title">Laporan Keterlambatan</div>
            <div class="period">Periode: {{ $namaBulan }}@if($dept) &mdash; Dept: {{ $dept->name }}@endif</div>
            @if($minKali > 1)<div class="period">Min. keterlambatan: {{ $minKali }}x</div>@endif
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    {{-- Summary --}}
    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sum-box sum-red">
                <div class="sv">{{ $ranking->count() }}</div>
                <div class="sl">Karyawan Terlambat</div>
            </td>
            <td class="sum-box sum-orange">
                <div class="sv">{{ $ranking->sum('jumlah_terlambat') }}</div>
                <div class="sl">Total Kejadian</div>
            </td>
            <td class="sum-box sum-blue">
                <div class="sv">{{ $ranking->max('jumlah_terlambat') ?? 0 }}</div>
                <div class="sl">Terbanyak (kali)</div>
            </td>
        </tr>
    </table>

    {{-- Ranking Table --}}
    <div class="section-title">Ranking Keterlambatan</div>
    @if($ranking->isEmpty())
        <p style="color:#718096;font-style:italic;margin-bottom:16px;">Tidak ada data keterlambatan pada periode ini.</p>
    @else
    <table class="data">
        <thead>
            <tr>
                <th style="width:40px;">No.</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th style="width:70px;text-align:center;">Terlambat</th>
                <th>Tanggal-Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $i => $r)
            @php
                $rankClass = match($i+1) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-n' };
                $dates = $r->tanggal_list->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->join(', ');
            @endphp
            <tr>
                <td style="text-align:center;">
                    <span class="rank-badge {{ $rankClass }}">{{ $i+1 }}</span>
                </td>
                <td>{{ $r->karyawan?->nik ?? '-' }}</td>
                <td style="font-weight:600;">{{ $r->karyawan?->nama ?? '-' }}</td>
                <td>{{ $r->karyawan?->department?->name ?? $r->karyawan?->departemen ?? '-' }}</td>
                <td style="text-align:center;">
                    <span class="badge-terlambat">{{ $r->jumlah_terlambat }}x</span>
                </td>
                <td class="dates-list">{{ $dates }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }} &mdash; Sistem Absensi Digital</td>
        <td style="text-align:right;">Laporan Keterlambatan &mdash; {{ $namaBulan }}</td>
    </tr>
</table>

</body>
</html>
