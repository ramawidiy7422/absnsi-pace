<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Cuti & Izin - {{ $namaBulan }}</title>
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
        .sum-cuti    { background: #ebf8ff; border-color: #90cdf4; } .sum-cuti .sv    { color: #2b6cb0; }
        .sum-sakit   { background: #fff5f7; border-color: #fbb6ce; } .sum-sakit .sv   { color: #97266d; }
        .sum-izin    { background: #fffff0; border-color: #f6e05e; } .sum-izin .sv    { color: #744210; }
        .sum-alfa    { background: #fff5f5; border-color: #fed7d7; } .sum-alfa .sv    { color: #c53030; }
        .sum-pending { background: #faf5ff; border-color: #d6bcfa; } .sum-pending .sv { color: #6b46c1; }

        table.data { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.data thead th { background: #1a1f36; color: white; padding: 7px 8px; text-align: left; font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.data tbody tr { border-bottom: 1px solid #f0f0f0; }
        table.data tbody tr:nth-child(even) { background: #f9fafb; }
        table.data tbody tr.dept-row { background: #edf2f7; }
        table.data tbody tr.dept-row td { padding: 5px 8px; font-weight: 700; font-size: 9px; color: #4a5568; text-transform: uppercase; letter-spacing: 0.5px; }
        table.data tbody td { padding: 6px 8px; }
        .center { text-align: center; }
        .val-zero { color: #cbd5e0; }
        .val-cuti   { color: #2b6cb0; font-weight: 700; }
        .val-sakit  { color: #97266d; font-weight: 700; }
        .val-izin   { color: #744210; font-weight: 700; }
        .val-alfa   { color: #c53030; font-weight: 700; }
        .val-pending{ color: #6b46c1; font-weight: 700; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: 700; }
        .badge-pending { background: #faf5ff; color: #6b46c1; }

        table.footer-table { width: 100%; margin-top: 20px; border-top: 1px solid #e2e8f0; }
        table.footer-table td { font-size: 8px; color: #a0aec0; padding: 10px 20px; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="company">{{ $namaPerusahaan }}</div>
            <div class="sub">
                Departemen HRD
                @if(!empty($alamatPerusahaan)) &bull; {{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }} @endif
                @if(!empty($noTelpPerusahaan)) &bull; Telp: {{ $noTelpPerusahaan }} @endif
            </div>
        </td>
        <td style="width:40%;">
            <div class="report-title">REKAP CUTI &amp; IZIN KARYAWAN</div>
            <div class="period">Periode: {{ $namaBulan }}{{ $departemen ? ' &mdash; '.$departemen : '' }}</div>
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    @php
        $sumCuti    = $rekap->sum('cuti_disetujui');
        $sumSakit   = $rekap->sum('izin_sakit');
        $sumIzin    = $rekap->sum('izin_izin');
        $sumAlfa    = $rekap->sum('izin_alfa');
        $sumPending = $rekap->sum('cuti_pending') + $rekap->sum('izin_pending');
        $total      = $rekap->count();
    @endphp

    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sum-box sum-cuti">
                <div class="sv">{{ $sumCuti }}</div>
                <div class="sl">Cuti Disetujui</div>
            </td>
            <td class="sum-box sum-sakit">
                <div class="sv">{{ $sumSakit }}</div>
                <div class="sl">Sakit</div>
            </td>
            <td class="sum-box sum-izin">
                <div class="sv">{{ $sumIzin }}</div>
                <div class="sl">Izin</div>
            </td>
            <td class="sum-box sum-alfa">
                <div class="sv">{{ $sumAlfa }}</div>
                <div class="sl">Alfa</div>
            </td>
            <td class="sum-box sum-pending">
                <div class="sv">{{ $sumPending }}</div>
                <div class="sl">Pending</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:24px;">#</th>
                <th>Nama Karyawan</th>
                <th>NIK</th>
                <th>Departemen</th>
                <th class="center">Cuti</th>
                <th class="center">Sakit</th>
                <th class="center">Izin</th>
                <th class="center">Alfa</th>
                <th class="center">Pending</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $prevDept = null; @endphp
            @foreach($rekap as $r)
                @php $deptName = $r['karyawan']->department?->name ?? $r['karyawan']->departemen; @endphp
                @if($deptName !== $prevDept)
                    <tr class="dept-row">
                        <td colspan="9"><i>{{ $deptName }}</i></td>
                    </tr>
                    @php $prevDept = $deptName; @endphp
                @endif
                @php
                    $hasPending = ($r['cuti_pending'] + $r['izin_pending']) > 0;
                @endphp
                <tr>
                    <td style="color:#a0aec0;font-size:9px;">{{ $no++ }}</td>
                    <td style="font-weight:600;color:#2d3748;">{{ $r['karyawan']->nama }}</td>
                    <td style="color:#718096;">{{ $r['karyawan']->nik }}</td>
                    <td style="color:#718096;font-size:9px;">{{ $deptName }}</td>
                    <td class="center {{ $r['cuti_disetujui'] > 0 ? 'val-cuti' : 'val-zero' }}">{{ $r['cuti_disetujui'] ?: '-' }}</td>
                    <td class="center {{ $r['izin_sakit'] > 0 ? 'val-sakit' : 'val-zero' }}">{{ $r['izin_sakit'] ?: '-' }}</td>
                    <td class="center {{ $r['izin_izin'] > 0 ? 'val-izin' : 'val-zero' }}">{{ $r['izin_izin'] ?: '-' }}</td>
                    <td class="center {{ $r['izin_alfa'] > 0 ? 'val-alfa' : 'val-zero' }}">{{ $r['izin_alfa'] ?: '-' }}</td>
                    <td class="center">
                        @if($hasPending)
                            <span class="badge badge-pending">{{ $r['cuti_pending'] + $r['izin_pending'] }} pending</span>
                        @else
                            <span class="val-zero">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr style="background:#1a1f36;color:white;font-weight:700;">
                <td colspan="4" style="padding:7px 8px;font-size:9px;color:white;">TOTAL</td>
                <td class="center" style="color:#90cdf4;">{{ $sumCuti }}</td>
                <td class="center" style="color:#fbb6ce;">{{ $sumSakit }}</td>
                <td class="center" style="color:#faf089;">{{ $sumIzin }}</td>
                <td class="center" style="color:#fc8181;">{{ $sumAlfa }}</td>
                <td class="center" style="color:#d6bcfa;">{{ $sumPending }}</td>
            </tr>
        </tbody>
    </table>

    {{-- TTD Section --}}
    <table style="width:100%;margin-top:30px;">
        <tr>
            <td style="width:70%;"></td>
            <td style="text-align:center;font-size:9px;">
                <div style="color:#4a5568;">Mengetahui,</div>
                <div style="font-weight:700;color:#2d3748;margin-top:2px;">HRD Manager</div>
                <div style="border-bottom:1px solid #2d3748;width:150px;margin:40px auto 4px;"></div>
                <div style="color:#718096;">(................................)</div>
            </td>
        </tr>
    </table>

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan }} &mdash; Sistem Informasi Absensi</td>
        <td style="text-align:right;">Halaman 1 / 1 &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>

</body>
</html>
