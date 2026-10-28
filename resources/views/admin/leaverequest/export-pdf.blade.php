<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2d3748; }

.header-table { width: 100%; border-collapse: collapse; background: #1e3a5f; color: #fff; margin-bottom: 12px; }
.header-table td { padding: 12px 16px; color: #fff; }
.company-name { font-size: 13pt; font-weight: bold; }
.company-sub  { font-size: 8pt; color: #93c5fd; margin-top: 3px; }
.doc-title    { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; }
.doc-sub      { font-size: 8pt; color: #93c5fd; margin-top: 2px; }
.header-right { text-align: right; font-size: 8pt; color: #93c5fd; vertical-align: top; white-space: nowrap; }

.filter-bar { background: #dbeafe; border: 1px solid #93c5fd; padding: 5px 10px; font-size: 8pt; color: #1e3a5f; margin-bottom: 10px; font-style: italic; }

.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.summary-table td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: center; background: #f8fafc; }
.sum-val { font-size: 16pt; font-weight: bold; color: #1e3a5f; line-height: 1; }
.sum-lbl { font-size: 7pt; color: #64748b; margin-top: 3px; text-transform: uppercase; }
.s-blue   { border-top: 3px solid #2563eb !important; } .s-blue   .sum-val { color: #1d4ed8; }
.s-green  { border-top: 3px solid #16a34a !important; } .s-green  .sum-val { color: #14532d; }
.s-amber  { border-top: 3px solid #d97706 !important; } .s-amber  .sum-val { color: #92400e; }
.s-red    { border-top: 3px solid #dc2626 !important; } .s-red    .sum-val { color: #991b1b; }
.s-sky    { border-top: 3px solid #0284c7 !important; } .s-sky    .sum-val { color: #075985; }
.s-purple { border-top: 3px solid #9333ea !important; } .s-purple .sum-val { color: #6b21a8; }

table.data { width: 100%; border-collapse: collapse; }
table.data thead tr { background: #1e3a5f; color: #fff; }
table.data thead th { padding: 6px 7px; text-align: left; font-size: 8pt; font-weight: bold; border: 1px solid #1e40af; white-space: nowrap; }
table.data tbody tr.odd  { background: #ffffff; }
table.data tbody tr.even { background: #eff6ff; }
table.data tbody td { padding: 4px 7px; font-size: 8.5pt; border: 1px solid #e2e8f0; vertical-align: middle; }
table.data tbody td.center { text-align: center; }

.badge { display: inline; padding: 1px 5px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
.b-pending, .b-pending_spv, .b-pending_manajer, .b-pending_hrd { background: #fef9c3; color: #854d0e; }
.b-disetujui { background: #dcfce7; color: #14532d; }
.b-ditolak   { background: #fee2e2; color: #991b1b; }
.b-cuti      { background: #dbeafe; color: #1d4ed8; }
.b-izin      { background: #f3e8ff; color: #6b21a8; }
.b-sakit     { background: #fee2e2; color: #991b1b; }

.footer-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
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

    $total     = $leaverequests->count();
    $disetujui = $leaverequests->where('status', 'disetujui')->count();
    $pending   = $leaverequests->whereIn('status', ['pending_spv', 'pending_manajer', 'pending_hrd'])->count();
    $ditolak   = $leaverequests->where('status', 'ditolak')->count();
    $jmlCuti   = $leaverequests->where('type', 'cuti')->count();
    $jmlIzin   = $leaverequests->where('type', 'izin')->count();
    $jmlSakit  = $leaverequests->where('type', 'sakit')->count();
@endphp
<table class="header-table">
<tr>
    <td>
        <div class="company-name">{{ $namaPerusahaan }}</div>
        <div class="company-sub">{{ $subLine }}</div>
        <div class="doc-title">Laporan Pengajuan Cuti & Izin</div>
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
    <td class="s-blue"><div class="sum-val">{{ $jmlCuti }}</div><div class="sum-lbl">Cuti</div></td>
    <td class="s-purple"><div class="sum-val">{{ $jmlIzin }}</div><div class="sum-lbl">Izin</div></td>
    <td class="s-red"><div class="sum-val">{{ $jmlSakit }}</div><div class="sum-lbl">Sakit</div></td>
</tr>
</table>

{{-- Data Table --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">#</th>
            <th>NIK</th>
            <th>Nama Karyawan</th>
            <th>Departemen</th>
            <th>Tipe</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th style="width:35px;" class="center">Hari</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Disetujui Oleh</th>
            <th>Tgl Pengajuan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaverequests as $i => $lr)
        @php
            $durasi   = ($lr->start_date && $lr->end_date) ? ($lr->start_date->diffInDays($lr->end_date) + 1) : '-';
            $rowClass = ($i % 2 === 0) ? 'odd' : 'even';
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="font-size:7.5pt;color:#475569;">{{ $lr->karyawan?->nik ?? '-' }}</td>
            <td style="font-weight:bold;">{{ $lr->karyawan?->nama ?? '-' }}</td>
            <td>{{ $lr->karyawan?->department?->name ?? $lr->karyawan?->departemen ?? '-' }}</td>
            <td><span class="badge b-{{ $lr->type }}">{{ ucfirst($lr->type) }}</span></td>
            <td style="white-space:nowrap;">{{ $lr->start_date?->format('d/m/Y') ?? '-' }}</td>
            <td style="white-space:nowrap;">{{ $lr->end_date?->format('d/m/Y') ?? '-' }}</td>
            <td class="center" style="font-weight:bold;">{{ $durasi }}</td>
            @php
                $statusLabelPdf = match($lr->status) {
                    'pending_spv'     => 'Menunggu SPV',
                    'pending_manajer' => 'Menunggu Manajer',
                    'pending_hrd'     => 'Menunggu HRD',
                    default           => ucfirst($lr->status),
                };
            @endphp
            <td><span class="badge b-{{ $lr->status }}">{{ $statusLabelPdf }}</span></td>
            <td style="color:#4a5568;">{{ \Illuminate\Support\Str::limit($lr->keterangan ?? '-', 35) }}</td>
            <td style="color:#64748b;">{{ $lr->approver?->name ?? '-' }}</td>
            <td style="white-space:nowrap;color:#64748b;">{{ $lr->created_at?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="12" class="center" style="padding:20px;color:#94a3b8;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<table class="footer-table">
<tr>
    <td class="footer-info">
        {{ $namaPerusahaan }}<br>
        Total: {{ $total }} &bull; Disetujui: {{ $disetujui }} &bull; Pending: {{ $pending }} &bull; Ditolak: {{ $ditolak }}<br>
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
