<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pelanggaran - {{ $namaBulan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #1a1f36; color: white; margin-bottom: 14px; }
        table.header-table td { padding: 14px 20px; color: white; }
        .company { font-size: 13px; font-weight: 700; }
        .sub { font-size: 9px; opacity: 0.7; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: 700; text-align: right; }
        .period { font-size: 10px; opacity: 0.85; margin-top: 3px; text-align: right; }
        .generated { font-size: 9px; opacity: 0.6; margin-top: 2px; text-align: right; }

        .body { padding: 0 18px 18px; }

        table.summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 14px; }
        .sum-box { text-align: center; padding: 10px 6px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .sv { font-size: 20px; font-weight: 800; line-height: 1; }
        .sl { font-size: 8px; color: #718096; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .sum-red   { background: #fff5f5; border-color: #fed7d7; } .sum-red .sv   { color: #c53030; }
        .sum-org   { background: #fffaf0; border-color: #fbd38d; } .sum-org .sv   { color: #c05621; }
        .sum-yel   { background: #fffff0; border-color: #fefcbf; } .sum-yel .sv   { color: #744210; }
        .sum-grey  { background: #f7fafc; border-color: #e2e8f0; } .sum-grey .sv  { color: #4a5568; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #2d3748;
            margin: 14px 0 8px; padding-bottom: 4px;
            border-bottom: 2px solid #c53030;
        }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9px; }
        table.data th { background: #c53030; color: white; padding: 5px 7px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.data td { padding: 4px 7px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        table.data tr:nth-child(even) td { background: #fafafa; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-weight: 700; font-size: 8px; }
        .badge-alpha  { background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }
        .badge-tlb    { background: #fffaf0; color: #c05621; border: 1px solid #fbd38d; }
        .badge-tlbizn { background: #fffff0; color: #744210; border: 1px solid #fefcbf; }

        .rank-badge { display: inline-block; width: 18px; height: 18px; border-radius: 50%; text-align: center; line-height: 18px; font-weight: 800; font-size: 8px; }
        .rank-1 { background: #ffd700; color: #744210; }
        .rank-2 { background: #c0c0c0; color: #2d3748; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-n { background: #e2e8f0; color: #4a5568; }

        table.footer-table { width: 100%; margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        table.footer-table td { font-size: 8px; color: #a0aec0; padding: 8px 18px; }
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
            <div class="report-title">Laporan Pelanggaran &amp; Ketidakhadiran</div>
            <div class="period">
                Periode: {{ $namaBulan }}
                @if($dept) &mdash; Dept: {{ $dept->name }} @endif
                @if($jenis !== 'semua') &mdash; Filter: {{ match($jenis) { 'alpha'=>'Alpha saja','terlambat_tanpa'=>'Terlambat (tanpa izin)','terlambat_izin'=>'Terlambat (berizin)', default=>$jenis } }} @endif
            </div>
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    {{-- Summary --}}
    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sum-box sum-grey">
                <div class="sv">{{ $totalAlpha + $totalTerlambatTanpa + $totalTerlambatIzin }}</div>
                <div class="sl">Total Pelanggaran</div>
            </td>
            <td class="sum-box sum-red">
                <div class="sv">{{ $totalAlpha }}</div>
                <div class="sl">Alpha</div>
            </td>
            <td class="sum-box sum-org">
                <div class="sv">{{ $totalTerlambatTanpa }}</div>
                <div class="sl">Terlambat (tanpa izin)</div>
            </td>
            <td class="sum-box sum-yel">
                <div class="sv">{{ $totalTerlambatIzin }}</div>
                <div class="sl">Terlambat (berizin)</div>
            </td>
        </tr>
    </table>

    {{-- Ranking --}}
    <div class="section-title">Ranking Pelanggaran Terbanyak (Top {{ $ranking->count() }})</div>
    @if($ranking->isEmpty())
        <p style="color:#718096;font-style:italic;margin-bottom:12px;">Tidak ada data pelanggaran.</p>
    @else
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px;">No.</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th style="text-align:center;">Alpha</th>
                <th style="text-align:center;">Terlambat</th>
                <th style="text-align:center;">Tlb. Izin</th>
                <th style="text-align:center;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $i => $r)
            @php $rankClass = match($i+1) { 1=>'rank-1', 2=>'rank-2', 3=>'rank-3', default=>'rank-n' }; @endphp
            <tr>
                <td style="text-align:center;"><span class="rank-badge {{ $rankClass }}">{{ $i+1 }}</span></td>
                <td>{{ $r->karyawan?->nik ?? '-' }}</td>
                <td style="font-weight:600;">{{ $r->karyawan?->nama ?? '-' }}</td>
                <td>{{ $r->karyawan?->department?->name ?? $r->karyawan?->departemen ?? '-' }}</td>
                <td style="text-align:center;font-weight:700;color:#c53030;">{{ $r->alpha ?: '-' }}</td>
                <td style="text-align:center;font-weight:700;color:#c05621;">{{ $r->terlambat_tanpa ?: '-' }}</td>
                <td style="text-align:center;color:#744210;">{{ $r->terlambat_izin ?: '-' }}</td>
                <td style="text-align:center;font-weight:800;">{{ $r->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Detail --}}
    <div class="section-title">Detail Pelanggaran</div>
    @if($data->isEmpty())
        <p style="color:#718096;font-style:italic;">Tidak ada data.</p>
    @else
    <table class="data">
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Status</th>
                <th>Jam Masuk</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $a)
            @php
                $isAlpha   = $a->status === 'alpha';
                $berizin   = $a->keterangan_terlambat !== null;
                $badgeClass = $isAlpha ? 'badge-alpha' : ($berizin ? 'badge-tlbizn' : 'badge-tlb');
                $label      = $isAlpha ? 'Alpha' : ($berizin ? 'Terlambat (Izin)' : 'Terlambat');
            @endphp
            <tr>
                <td>{{ $a->karyawan?->nik ?? '-' }}</td>
                <td style="font-weight:600;">{{ $a->karyawan?->nama ?? '-' }}</td>
                <td>{{ $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-' }}</td>
                <td style="white-space:nowrap;">{{ $a->tanggal->format('d/m/Y') }}</td>
                <td>{{ $a->tanggal->translatedFormat('D') }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $label }}</span></td>
                <td>{{ $a->jam_masuk ? substr($a->jam_masuk,0,5) : '-' }}</td>
                <td style="max-width:120px;">{{ \Str::limit($a->keterangan_terlambat ?? '-', 40) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }} &mdash; Laporan Pelanggaran {{ $namaBulan }}</td>
        <td style="text-align:right;">Dicetak dari Sistem Absensi Digital</td>
    </tr>
</table>

</body>
</html>
