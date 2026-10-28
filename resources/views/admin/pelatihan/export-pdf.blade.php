<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2d3748; }

.header-table { width: 100%; border-collapse: collapse; background: #3b0764; color: #fff; margin-bottom: 12px; }
.header-table td { padding: 12px 16px; color: #fff; }
.company-name { font-size: 13pt; font-weight: bold; }
.company-sub  { font-size: 8pt; color: #d8b4fe; margin-top: 3px; }
.doc-title    { font-size: 15pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; }
.doc-sub      { font-size: 8pt; color: #d8b4fe; margin-top: 2px; }
.header-right { text-align: right; font-size: 8pt; color: #d8b4fe; vertical-align: top; white-space: nowrap; }

.filter-bar { background: #faf5ff; border: 1px solid #d8b4fe; padding: 5px 10px; font-size: 8pt; color: #3b0764; margin-bottom: 10px; font-style: italic; }

.summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.summary-table td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: center; background: #f8fafc; }
.sum-val { font-size: 16pt; font-weight: bold; color: #3b0764; line-height: 1; }
.sum-lbl { font-size: 7pt; color: #64748b; margin-top: 3px; text-transform: uppercase; }
.s-purple { border-top: 3px solid #9333ea !important; } .s-purple .sum-val { color: #6b21a8; }
.s-green  { border-top: 3px solid #16a34a !important; } .s-green  .sum-val { color: #14532d; }
.s-red    { border-top: 3px solid #dc2626 !important; } .s-red    .sum-val { color: #991b1b; }
.s-blue   { border-top: 3px solid #2563eb !important; } .s-blue   .sum-val { color: #1d4ed8; }
.s-gray   { border-top: 3px solid #94a3b8 !important; } .s-gray   .sum-val { color: #475569; }
.s-amber  { border-top: 3px solid #d97706 !important; } .s-amber  .sum-val { color: #92400e; }

table.data { width: 100%; border-collapse: collapse; }
table.data thead tr { background: #6d28d9; color: #fff; }
table.data thead th { padding: 6px 7px; text-align: left; font-size: 8pt; font-weight: bold; border: 1px solid #5b21b6; white-space: nowrap; }
table.data tbody tr.odd  { background: #ffffff; }
table.data tbody tr.even { background: #faf5ff; }
table.data tbody td { padding: 4px 7px; font-size: 8pt; border: 1px solid #e2e8f0; vertical-align: middle; }
table.data tbody td.center { text-align: center; }

.badge { display: inline; padding: 1px 5px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
.b-lulus         { background: #dcfce7; color: #14532d; }
.b-tidak_lulus   { background: #fee2e2; color: #991b1b; }
.b-sedang_berjalan { background: #dbeafe; color: #1d4ed8; }
.b-belum_ikut    { background: #f1f5f9; color: #475569; }
.b-cpob          { background: #f3e8ff; color: #6b21a8; }
.b-k3            { background: #ffedd5; color: #9a3412; }
.b-hygiene       { background: #dcfce7; color: #14532d; }
.b-sop           { background: #dbeafe; color: #1d4ed8; }
.b-internal      { background: #f1f5f9; color: #475569; }
.b-external      { background: #fef9c3; color: #854d0e; }
.b-lainnya       { background: #fce7f3; color: #9d174d; }

.exp-warn  { color: #b91c1c; font-weight: bold; }
.exp-near  { color: #d97706; font-weight: bold; }

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

    $total    = $pelatihan->count();
    $lulus    = $pelatihan->where('status','lulus')->count();
    $tdk      = $pelatihan->where('status','tidak_lulus')->count();
    $sedang   = $pelatihan->where('status','sedang_berjalan')->count();
    $belum    = $pelatihan->where('status','belum_ikut')->count();
    $expired  = $pelatihan->filter(fn($p) => $p->tanggal_expired && $p->tanggal_expired->isPast())->count();
    $nearExp  = $pelatihan->filter(fn($p) => $p->tanggal_expired && !$p->tanggal_expired->isPast() && $p->tanggal_expired->diffInDays(now()) <= 30)->count();
@endphp
<table class="header-table">
<tr>
    <td>
        <div class="company-name">{{ $namaPerusahaan }}</div>
        <div class="company-sub">{{ $subLine }}</div>
        <div class="doc-title">Rekap Data Pelatihan & Sertifikasi</div>
    </td>
    <td class="header-right">
        Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        Total: {{ $total }} data
    </td>
</tr>
</table>

{{-- Filter --}}
<div class="filter-bar">Filter: {{ $filterLabel }}</div>

{{-- Summary --}}
<table class="summary-table">
<tr>
    <td><div class="sum-val">{{ $total }}</div><div class="sum-lbl">Total</div></td>
    <td class="s-green"><div class="sum-val">{{ $lulus }}</div><div class="sum-lbl">Lulus</div></td>
    <td class="s-red"><div class="sum-val">{{ $tdk }}</div><div class="sum-lbl">Tidak Lulus</div></td>
    <td class="s-blue"><div class="sum-val">{{ $sedang }}</div><div class="sum-lbl">Sedang Berjalan</div></td>
    <td class="s-gray"><div class="sum-val">{{ $belum }}</div><div class="sum-lbl">Belum Ikut</div></td>
    <td class="s-red"><div class="sum-val">{{ $expired }}</div><div class="sum-lbl">Expired</div></td>
    <td class="s-amber"><div class="sum-val">{{ $nearExp }}</div><div class="sum-lbl">Exp &le; 30 hari</div></td>
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
            <th>Nama Pelatihan</th>
            <th>Jenis</th>
            <th>Penyelenggara</th>
            <th>Tgl Mulai</th>
            <th>Tgl Selesai</th>
            <th>Status</th>
            <th>No. Sertifikat</th>
            <th>Tgl Expired</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pelatihan as $i => $p)
        @php
            $isExpired = $p->tanggal_expired && $p->tanggal_expired->isPast();
            $isNear    = $p->tanggal_expired && !$isExpired && $p->tanggal_expired->diffInDays(now()) <= 30;
            $rowClass  = ($i % 2 === 0) ? 'odd' : 'even';
            $expClass  = $isExpired ? 'exp-warn' : ($isNear ? 'exp-near' : '');
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="font-size:7.5pt;color:#475569;">{{ $p->karyawan?->nik ?? '-' }}</td>
            <td style="font-weight:bold;">{{ $p->karyawan?->nama ?? '-' }}</td>
            <td>{{ $p->karyawan?->department?->name ?? $p->karyawan?->departemen ?? '-' }}</td>
            <td style="font-weight:bold;">{{ $p->nama_pelatihan }}</td>
            <td><span class="badge b-{{ $p->jenis }}">{{ strtoupper($p->jenis) }}</span></td>
            <td>{{ $p->penyelenggara ?? '-' }}</td>
            <td style="white-space:nowrap;">{{ $p->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
            <td style="white-space:nowrap;">{{ $p->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
            <td><span class="badge b-{{ $p->status }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span></td>
            <td style="font-size:7.5pt;">{{ $p->nomor_sertifikat ?? '-' }}</td>
            <td style="white-space:nowrap;" class="{{ $expClass }}">
                {{ $p->tanggal_expired?->format('d/m/Y') ?? '-' }}
                @if($isExpired) (Exp)@elseif($isNear) (!)@endif
            </td>
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
