<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2d3748; }

.header-table { width: 100%; border-collapse: collapse; background: #7b341e; color: #fff; margin-bottom: 12px; }
.header-table td { padding: 12px 16px; color: #fff; }
.company-name { font-size: 13pt; font-weight: bold; }
.company-sub  { font-size: 8pt; color: #fed7aa; margin-top: 3px; }
.doc-title    { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; }
.doc-sub      { font-size: 8pt; color: #fed7aa; margin-top: 2px; }
.header-right { text-align: right; font-size: 8pt; color: #fed7aa; vertical-align: top; white-space: nowrap; }

.filter-bar { background: #fff7ed; border: 1px solid #fed7aa; padding: 5px 10px; font-size: 8pt; color: #7b341e; margin-bottom: 10px; font-style: italic; }

.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.summary-table td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: center; background: #f8fafc; }
.sum-val { font-size: 16pt; font-weight: bold; color: #7b341e; line-height: 1; }
.sum-lbl { font-size: 7pt; color: #64748b; margin-top: 3px; text-transform: uppercase; }
.s-orange { border-top: 3px solid #ea580c !important; } .s-orange .sum-val { color: #9a3412; }
.s-green  { border-top: 3px solid #16a34a !important; } .s-green  .sum-val { color: #14532d; }
.s-amber  { border-top: 3px solid #d97706 !important; } .s-amber  .sum-val { color: #92400e; }
.s-red    { border-top: 3px solid #dc2626 !important; } .s-red    .sum-val { color: #991b1b; }
.s-purple { border-top: 3px solid #9333ea !important; } .s-purple .sum-val { color: #6b21a8; }

table.data { width: 100%; border-collapse: collapse; }
table.data thead tr { background: #c05621; color: #fff; }
table.data thead th { padding: 6px 7px; text-align: left; font-size: 8pt; font-weight: bold; border: 1px solid #9a3412; white-space: nowrap; }
table.data tbody tr.odd  { background: #ffffff; }
table.data tbody tr.even { background: #fff7ed; }
table.data tbody td { padding: 4px 7px; font-size: 8.5pt; border: 1px solid #e2e8f0; vertical-align: middle; }
table.data tbody td.center { text-align: center; }

.badge { display: inline; padding: 1px 5px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
.b-pending   { background: #fef9c3; color: #854d0e; }
.b-disetujui { background: #dcfce7; color: #14532d; }
.b-ditolak   { background: #fee2e2; color: #991b1b; }
.b-sakit     { background: #fee2e2; color: #991b1b; }
.b-izin      { background: #fef9c3; color: #854d0e; }
.b-alfa      { background: #f3e8ff; color: #6b21a8; }

.footer-table { width: 100%; border-collapse: collapse; margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
.footer-info  { font-size: 7.5pt; color: #64748b; vertical-align: top; }
.sign-area    { text-align: center; vertical-align: bottom; width: 160px; font-size: 8.5pt; }
.sign-line    { border-top: 1px solid #94a3b8; padding-top: 4px; margin-top: 40px; }
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

    $total     = $izinList->count();
    $disetujui = $izinList->where('status', 'disetujui')->count();
    $pending   = $izinList->where('status', 'pending')->count();
    $ditolak   = $izinList->where('status', 'ditolak')->count();
    $jmlSakit  = $izinList->where('jenis', 'sakit')->count();
    $jmlIzin   = $izinList->where('jenis', 'izin')->count();
    $jmlAlfa   = $izinList->where('jenis', 'alfa')->count();
@endphp
<table class="header-table">
<tr>
    <td>
        <div class="company-name">{{ $namaPerusahaan }}</div>
        <div class="company-sub">{{ $subLine }}</div>
        <div class="doc-title">Laporan Izin Tidak Masuk</div>
        <div class="doc-sub">{{ $bulan ? \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') : 'Semua Bulan' }} {{ $tahun }}</div>
    </td>
    <td class="header-right">
        Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        Total: {{ $total }} data
    </td>
</tr>
</table>

{{-- Filter --}}
@if($filterLabel)
<div class="filter-bar">Filter: {{ $filterLabel }}</div>
@endif

{{-- Summary --}}
<table class="summary-table">
<tr>
    <td><div class="sum-val">{{ $total }}</div><div class="sum-lbl">Total</div></td>
    <td class="s-green"><div class="sum-val">{{ $disetujui }}</div><div class="sum-lbl">Disetujui</div></td>
    <td class="s-amber"><div class="sum-val">{{ $pending }}</div><div class="sum-lbl">Pending</div></td>
    <td class="s-red"><div class="sum-val">{{ $ditolak }}</div><div class="sum-lbl">Ditolak</div></td>
    <td class="s-red"><div class="sum-val">{{ $jmlSakit }}</div><div class="sum-lbl">Sakit</div></td>
    <td class="s-orange"><div class="sum-val">{{ $jmlIzin }}</div><div class="sum-lbl">Izin</div></td>
    <td class="s-purple"><div class="sum-val">{{ $jmlAlfa }}</div><div class="sum-lbl">Alfa</div></td>
</tr>
</table>

{{-- Data Table --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">#</th>
            <th>Tanggal</th>
            <th>NIK</th>
            <th>Nama Karyawan</th>
            <th>Departemen</th>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Lampiran</th>
            <th>Disetujui Oleh</th>
        </tr>
    </thead>
    <tbody>
        @forelse($izinList as $i => $izin)
        @php $rowClass = ($i % 2 === 0) ? 'odd' : 'even'; @endphp
        <tr class="{{ $rowClass }}">
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="white-space:nowrap;">{{ $izin->tanggal?->format('d/m/Y') ?? '-' }}</td>
            <td style="font-size:7.5pt;color:#475569;">{{ $izin->karyawan?->nik ?? '-' }}</td>
            <td style="font-weight:bold;">{{ $izin->karyawan?->nama ?? '-' }}</td>
            <td>{{ $izin->karyawan?->department?->name ?? $izin->karyawan?->departemen ?? '-' }}</td>
            <td><span class="badge b-{{ $izin->jenis }}">{{ ucfirst($izin->jenis) }}</span></td>
            <td style="color:#4a5568;">{{ \Illuminate\Support\Str::limit($izin->keterangan ?? '-', 40) }}</td>
            <td><span class="badge b-{{ $izin->status }}">{{ ucfirst($izin->status) }}</span></td>
            <td class="center">
                @if($izin->lampiran)<span style="color:#1d4ed8;">Ada</span>@else<span style="color:#94a3b8;">-</span>@endif
            </td>
            <td style="color:#64748b;">{{ $izin->approver?->name ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="10" class="center" style="padding:20px;color:#94a3b8;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<table class="footer-table" style="margin-top:16px;">
<tr>
    <td class="footer-info">
        {{ $namaPerusahaan }}<br>
        Total: {{ $total }} &bull; Sakit: {{ $jmlSakit }} &bull; Izin: {{ $jmlIzin }} &bull; Alfa: {{ $jmlAlfa }}<br>
        Dicetak pada: {{ now()->format('d F Y, H:i') }}
    </td>
    <td class="sign-area">
        Mengetahui, HRD Manager
        <div class="sign-line">(....................................)</div>
    </td>
</tr>
</table>

</body>
</html>
