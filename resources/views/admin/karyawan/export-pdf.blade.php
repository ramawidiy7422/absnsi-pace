<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2d3748; }

.header-table { width: 100%; border-collapse: collapse; background: #1e4e8c; color: #fff; margin-bottom: 12px; }
.header-table td { padding: 12px 16px; color: #fff; }
.company-name { font-size: 13pt; font-weight: bold; }
.company-sub { font-size: 8pt; color: #c8daf5; margin-top: 3px; }
.doc-title { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; letter-spacing: 1px; }
.doc-sub { font-size: 8pt; color: #c8daf5; margin-top: 2px; }
.header-right { text-align: right; font-size: 8pt; color: #c8daf5; vertical-align: top; white-space: nowrap; }

.filter-bar { background: #dbeafe; border: 1px solid #93c5fd; padding: 5px 10px; font-size: 8pt; color: #1e40af; margin-bottom: 10px; }

.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.summary-table td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: center; background: #f8fafc; }
.sum-val { font-size: 16pt; font-weight: bold; color: #1e4e8c; line-height: 1; }
.sum-lbl { font-size: 7pt; color: #64748b; margin-top: 3px; text-transform: uppercase; }
.sum-green .sum-val  { color: #166534; } .sum-green { border-top: 3px solid #16a34a !important; }
.sum-blue .sum-val   { color: #1d4ed8; } .sum-blue  { border-top: 3px solid #3b82f6 !important; }
.sum-amber .sum-val  { color: #92400e; } .sum-amber { border-top: 3px solid #f59e0b !important; }
.sum-purple .sum-val { color: #6b21a8; } .sum-purple{ border-top: 3px solid #9333ea !important; }
.sum-red .sum-val    { color: #991b1b; } .sum-red   { border-top: 3px solid #ef4444 !important; }
.sum-sky .sum-val    { color: #075985; } .sum-sky   { border-top: 3px solid #0284c7 !important; }
.sum-pink .sum-val   { color: #9d174d; } .sum-pink  { border-top: 3px solid #ec4899 !important; }

table.data { width: 100%; border-collapse: collapse; }
table.data thead tr { background: #1e4e8c; color: #fff; }
table.data thead th { padding: 6px 7px; text-align: left; font-size: 8pt; font-weight: bold; white-space: nowrap; border: 1px solid #1a4278; }
table.data tbody tr.even { background: #f1f5f9; }
table.data tbody tr.odd  { background: #ffffff; }
table.data tbody td { padding: 4px 7px; font-size: 8pt; border: 1px solid #e2e8f0; vertical-align: middle; }
table.data tbody td.center { text-align: center; }
table.data tbody td.right  { text-align: right; }

.badge { display: inline; padding: 1px 5px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
.b-tetap      { background: #dcfce7; color: #166534; }
.b-kontrak    { background: #dbeafe; color: #1d4ed8; }
.b-magang     { background: #fef9c3; color: #854d0e; }
.b-outsource  { background: #f3e8ff; color: #6b21a8; }
.b-nonaktif   { background: #fee2e2; color: #991b1b; }
.b-resign     { background: #f1f5f9; color: #475569; }
.b-phk        { background: #ffedd5; color: #9a3412; }

.footer-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
.footer-info { font-size: 7.5pt; color: #64748b; vertical-align: top; }
.sign-area { text-align: center; vertical-align: bottom; width: 160px; font-size: 8.5pt; }
.sign-line { border-top: 1px solid #94a3b8; padding-top: 4px; margin-top: 40px; }
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
        <div class="doc-title">Daftar Karyawan</div>
        <div class="doc-sub">Per {{ now()->format('d F Y') }}</div>
    </td>
    <td class="header-right">
        Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        Jumlah: {{ $query->count() }} karyawan
    </td>
</tr>
</table>

{{-- Filter --}}
@if(!empty($filterLabel))
<div class="filter-bar">Filter: {{ $filterLabel }}</div>
@endif

{{-- Summary --}}
@php
    $total     = $query->count();
    $tetap     = $query->where('status','tetap')->count();
    $kontrak   = $query->where('status','kontrak')->count();
    $magang    = $query->where('status','magang')->count();
    $outsource = $query->where('status','outsourcing')->count();
    $nonaktif  = $query->whereIn('status',['nonaktif','resign','phk'])->count();
    $laki      = $query->where('jenis_kelamin','L')->count();
    $perempuan = $query->where('jenis_kelamin','P')->count();
@endphp
<table class="summary-table">
<tr>
    <td><div class="sum-val">{{ $total }}</div><div class="sum-lbl">Total</div></td>
    <td class="sum-green"><div class="sum-val">{{ $tetap }}</div><div class="sum-lbl">Tetap</div></td>
    <td class="sum-blue"><div class="sum-val">{{ $kontrak }}</div><div class="sum-lbl">Kontrak</div></td>
    <td class="sum-amber"><div class="sum-val">{{ $magang }}</div><div class="sum-lbl">Magang</div></td>
    <td class="sum-purple"><div class="sum-val">{{ $outsource }}</div><div class="sum-lbl">Outsource</div></td>
    <td class="sum-red"><div class="sum-val">{{ $nonaktif }}</div><div class="sum-lbl">Nonaktif</div></td>
    <td class="sum-sky"><div class="sum-val">{{ $laki }}</div><div class="sum-lbl">Laki-laki</div></td>
    <td class="sum-pink"><div class="sum-val">{{ $perempuan }}</div><div class="sum-lbl">Perempuan</div></td>
</tr>
</table>

{{-- Data Table --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">No</th>
            <th>NIK</th>
            <th>Nama Karyawan</th>
            <th>L/P</th>
            <th>Departemen</th>
            <th>Jabatan</th>
            <th>Shift</th>
            <th>Status</th>
            <th>Tgl Masuk</th>
            <th>Kontrak Berakhir</th>
            <th>No HP</th>
        </tr>
    </thead>
    <tbody>
        @forelse($query as $i => $k)
        @php
            $rowClass = ($i % 2 === 0) ? 'odd' : 'even';
            $badgeClass = match($k->status) {
                'tetap'       => 'b-tetap',
                'kontrak'     => 'b-kontrak',
                'magang'      => 'b-magang',
                'outsourcing' => 'b-outsource',
                'nonaktif'    => 'b-nonaktif',
                'resign'      => 'b-resign',
                'phk'         => 'b-phk',
                default       => '',
            };
            $kontrakStyle = '';
            if ($k->tanggal_kontrak_berakhir) {
                if ($k->tanggal_kontrak_berakhir->isPast()) {
                    $kontrakStyle = 'color:#b91c1c;font-weight:bold;';
                } elseif ($k->tanggal_kontrak_berakhir->diffInDays(now()) <= 30) {
                    $kontrakStyle = 'color:#d97706;font-weight:bold;';
                }
            }
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="font-size:7.5pt;color:#475569;">{{ $k->nik }}</td>
            <td style="font-weight:bold;">{{ $k->nama }}</td>
            <td class="center">{{ $k->jenis_kelamin === 'L' ? 'L' : 'P' }}</td>
            <td>{{ $k->department?->name ?? $k->departemen ?? '-' }}</td>
            <td>{{ $k->position?->name ?? $k->jabatan ?? '-' }}</td>
            <td>{{ $k->shift?->nama_shift ?? '-' }}</td>
            <td class="center"><span class="badge {{ $badgeClass }}">{{ ucfirst($k->status) }}</span></td>
            <td style="white-space:nowrap;">{{ $k->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
            <td style="white-space:nowrap;{{ $kontrakStyle }}">{{ $k->tanggal_kontrak_berakhir?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $k->no_hp ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="11" class="center" style="padding:20px;color:#94a3b8;">Tidak ada data karyawan</td></tr>
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
