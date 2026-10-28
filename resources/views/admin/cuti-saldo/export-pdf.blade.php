<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2d3748; }

.header-table { width: 100%; border-collapse: collapse; background: #14532d; color: #fff; margin-bottom: 12px; }
.header-table td { padding: 12px 16px; color: #fff; }
.company-name { font-size: 13pt; font-weight: bold; }
.company-sub  { font-size: 8pt; color: #86efac; margin-top: 3px; }
.doc-title    { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; letter-spacing: 1px; }
.doc-sub      { font-size: 8pt; color: #86efac; margin-top: 2px; }
.header-right { text-align: right; font-size: 8pt; color: #86efac; vertical-align: top; white-space: nowrap; }

.filter-bar { background: #dcfce7; border: 1px solid #86efac; padding: 5px 10px; font-size: 8pt; color: #14532d; margin-bottom: 10px; }

.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.summary-table td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: center; background: #f8fafc; }
.sum-val { font-size: 16pt; font-weight: bold; color: #14532d; line-height: 1; }
.sum-lbl { font-size: 7pt; color: #64748b; margin-top: 3px; text-transform: uppercase; }
.s-green  { border-top: 3px solid #16a34a !important; } .s-green  .sum-val { color: #14532d; }
.s-red    { border-top: 3px solid #dc2626 !important; } .s-red    .sum-val { color: #991b1b; }
.s-blue   { border-top: 3px solid #2563eb !important; } .s-blue   .sum-val { color: #1d4ed8; }
.s-sky    { border-top: 3px solid #0284c7 !important; } .s-sky    .sum-val { color: #075985; }
.s-amber  { border-top: 3px solid #d97706 !important; } .s-amber  .sum-val { color: #92400e; }
.s-gray   { border-top: 3px solid #94a3b8 !important; } .s-gray   .sum-val { color: #475569; }

table.data { width: 100%; border-collapse: collapse; }
table.data thead tr { background: #14532d; color: #fff; }
table.data thead th { padding: 6px 8px; text-align: left; font-size: 8pt; font-weight: bold; border: 1px solid #166534; white-space: nowrap; }
table.data thead th.right { text-align: right; }
table.data thead th.center { text-align: center; }
table.data tbody tr.odd  { background: #ffffff; }
table.data tbody tr.even { background: #f0fdf4; }
table.data tbody td { padding: 4px 8px; font-size: 8.5pt; border: 1px solid #e2e8f0; vertical-align: middle; }
table.data tbody td.right  { text-align: right; font-weight: bold; }
table.data tbody td.center { text-align: center; }

.bar-outer { background: #e2e8f0; width: 70px; height: 8px; display: inline-block; vertical-align: middle; }
.bar-inner { height: 8px; background: #16a34a; display: inline-block; }
.bar-red   { background: #dc2626; }
.bar-amber { background: #d97706; }

.footer-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
.footer-info { font-size: 7.5pt; color: #64748b; vertical-align: top; }
.sign-area   { text-align: center; vertical-align: bottom; width: 160px; font-size: 8.5pt; }
.sign-line   { border-top: 1px solid #94a3b8; padding-top: 4px; margin-top: 40px; }
</style>
</head>
<body>

{{-- Header --}}
@php
    $subLine = '';
    if (!empty($alamatPerusahaan)) { $subLine .= $alamatPerusahaan; }
    if (!empty($kotaPerusahaan))   { $subLine .= ($subLine ? ', ' : '') . $kotaPerusahaan; }
    if (!empty($noTelpPerusahaan)) { $subLine .= ($subLine ? '  |  Telp: ' : 'Telp: ') . $noTelpPerusahaan; }
    if (empty($subLine))           { $subLine = 'Sistem Informasi Absensi & HR'; }
@endphp
<table class="header-table">
<tr>
    <td>
        <div class="company-name">{{ $namaPerusahaan }}</div>
        <div class="company-sub">{{ $subLine }}</div>
        <div class="doc-title">Rekap Saldo Cuti {{ $tahun }}</div>
        <div class="doc-sub">Jatah default: {{ $jatahDefault }} hari/tahun</div>
    </td>
    <td class="header-right">
        Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        Jumlah: {{ $karyawan->count() }} karyawan
    </td>
</tr>
</table>

{{-- Filter --}}
<div class="filter-bar">Filter: {{ $filterLabel }}</div>

{{-- Summary --}}
@php
    $totalJatah    = 0;
    $totalTerpakai = 0;
    $habis         = 0;
    $kritis        = 0;
    foreach ($karyawan as $k) {
        $s = $saldoMap[$k->id] ?? null;
        $j = $s?->jatah ?? $jatahDefault;
        $t = $s?->terpakai ?? 0;
        $r = max(0, $j - $t);
        $totalJatah    += $j;
        $totalTerpakai += $t;
        if ($r == 0) $habis++;
        elseif ($r <= 3) $kritis++;
    }
    $totalSisa = max(0, $totalJatah - $totalTerpakai);
@endphp
<table class="summary-table">
<tr>
    <td><div class="sum-val">{{ $karyawan->count() }}</div><div class="sum-lbl">Karyawan</div></td>
    <td class="s-green"><div class="sum-val">{{ $totalJatah }}</div><div class="sum-lbl">Total Jatah</div></td>
    <td class="s-red"><div class="sum-val">{{ $totalTerpakai }}</div><div class="sum-lbl">Terpakai</div></td>
    <td class="s-blue"><div class="sum-val">{{ $totalSisa }}</div><div class="sum-lbl">Total Sisa</div></td>
    <td class="s-amber"><div class="sum-val">{{ $kritis }}</div><div class="sum-lbl">Sisa &le; 3 Hari</div></td>
    <td class="s-gray"><div class="sum-val">{{ $habis }}</div><div class="sum-lbl">Cuti Habis</div></td>
</tr>
</table>

{{-- Data Table --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">No</th>
            <th>NIK</th>
            <th>Nama Karyawan</th>
            <th>Departemen</th>
            <th>Status</th>
            <th class="right">Jatah</th>
            <th class="right">Terpakai</th>
            <th class="right">Sisa</th>
            <th class="center">Penggunaan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($karyawan as $i => $k)
        @php
            $s        = $saldoMap[$k->id] ?? null;
            $jatah    = $s?->jatah    ?? $jatahDefault;
            $terpakai = $s?->terpakai ?? 0;
            $sisa     = max(0, $jatah - $terpakai);
            $pct      = $jatah > 0 ? min(100, round(($terpakai / $jatah) * 100)) : 0;
            $barClass = $pct >= 100 ? 'bar-red' : ($pct >= 70 ? 'bar-amber' : '');
            $sisaColor = $sisa == 0 ? 'color:#b91c1c;' : ($sisa <= 3 ? 'color:#d97706;' : 'color:#14532d;');
            $rowClass = ($i % 2 === 0) ? 'odd' : 'even';
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="font-size:7.5pt;color:#475569;">{{ $k->nik }}</td>
            <td style="font-weight:bold;">{{ $k->nama }}</td>
            <td>{{ $k->department?->name ?? $k->departemen ?? '-' }}</td>
            <td>{{ ucfirst($k->status) }}</td>
            <td class="right">{{ $jatah }}</td>
            <td class="right" style="color:#b91c1c;">{{ $terpakai }}</td>
            <td class="right" style="{{ $sisaColor }}font-weight:bold;">{{ $sisa }}</td>
            <td class="center">
                <div class="bar-outer"><div class="bar-inner {{ $barClass }}" style="width:{{ $pct }}%;"></div></div>
                <span style="font-size:7pt;color:#94a3b8;"> {{ $pct }}%</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="center" style="padding:20px;color:#94a3b8;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<table class="footer-table">
<tr>
    <td class="footer-info">
        {{ $namaPerusahaan }}<br>
        @if(!empty($alamatPerusahaan)){{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }}<br>@endif
        Dicetak pada: {{ now()->format('d F Y, H:i') }}
    </td>
    <td class="sign-area">
        Mengetahui,
        <div class="sign-line">HRD Manager</div>
    </td>
</tr>
</table>

</body>
</html>
