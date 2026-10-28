<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Absensi {{ $namaBulan }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',sans-serif; font-size:8px; color:#2d3748; }
.header { background:#2b6cb0; color:white; padding:12px 16px; border-radius:6px; margin-bottom:12px; }
.header h1 { font-size:13px; font-weight:800; }
.header p  { font-size:9px; opacity:.85; margin-top:2px; }
table { width:100%; border-collapse:collapse; font-size:7.5px; }
thead th { background:#2b6cb0; color:white; padding:5px 4px; text-align:center; font-weight:700; border:1px solid #2c5282; }
thead th.left { text-align:left; padding-left:6px; }
tbody td { padding:4px 4px; border:1px solid #e2e8f0; text-align:center; vertical-align:middle; }
tbody td.left { text-align:left; padding-left:6px; }
tbody tr:nth-child(even) { background:#f7fafc; }
.dept-row td { background:#ebf8ff; font-weight:800; color:#2b6cb0; font-size:7px; padding:4px 6px; border:1px solid #bee3f8; text-transform:uppercase; }
.badge { display:inline-block; padding:1px 5px; border-radius:3px; font-weight:800; }
.b-green  { background:#c6f6d5; color:#276749; }
.b-orange { background:#fef9c3; color:#854d0e; }
.b-red    { background:#fed7e2; color:#c53030; }
.b-blue   { background:#ebf8ff; color:#2b6cb0; }
.b-gray   { background:#f7fafc; color:#718096; }
.footer { margin-top:10px; font-size:7px; color:#a0aec0; text-align:right; }
</style>
</head>
<body>
<div class="header">
    <h1>&#128203; Rekap Absensi Karyawan &mdash; {{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</h1>
    <p>
        Periode: {{ $namaBulan }}{{ $departemen ? ' — Departemen: '.$departemen : '' }} &nbsp;·&nbsp; Total: {{ $rekap->count() }} karyawan
        @if(!empty($alamatPerusahaan)) &nbsp;·&nbsp; {{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }} @endif
        @if(!empty($noTelpPerusahaan)) &nbsp;·&nbsp; Telp: {{ $noTelpPerusahaan }} @endif
    </p>
</div>

<table>
    <thead>
        <tr>
            <th class="left" style="width:25px;">#</th>
            <th class="left" style="width:130px;">Nama Karyawan</th>
            <th class="left" style="width:90px;">Departemen</th>
            <th class="left" style="width:70px;">Shift</th>
            <th style="width:30px;">Hadir</th>
            <th style="width:35px;">Terlambat</th>
            <th style="width:25px;">Izin</th>
            <th style="width:25px;">Sakit</th>
            <th style="width:25px;">Alpha</th>
            <th style="width:30px;">Total</th>
            <th style="width:40px;">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $prevDept = null; @endphp
        @foreach($rekap as $r)
        @php $deptName = $r['karyawan']->department?->name ?? $r['karyawan']->departemen ?? 'Tanpa Departemen'; @endphp
        @if($deptName !== $prevDept)
        <tr class="dept-row">
            <td colspan="11">&#9632; {{ $deptName }}</td>
        </tr>
        @php $prevDept = $deptName; @endphp
        @endif
        <tr>
            <td>{{ $no++ }}</td>
            <td class="left">{{ $r['karyawan']->nama }}</td>
            <td class="left" style="font-size:7px;color:#718096;">{{ $r['karyawan']->jabatan ?? '-' }}</td>
            <td class="left" style="font-size:7px;">{{ $r['karyawan']->shift?->nama_shift ?? '-' }}</td>
            <td><span class="badge b-green">{{ $r['hadir'] }}</span></td>
            <td><span class="badge b-orange">{{ $r['terlambat'] }}</span></td>
            <td><span class="badge b-blue">{{ $r['izin'] }}</span></td>
            <td><span class="badge b-blue">{{ $r['sakit'] }}</span></td>
            <td><span class="badge b-red">{{ $r['alpha'] }}</span></td>
            <td><span class="badge b-gray">{{ $r['total'] }}</span></td>
            <td>
                <span class="badge {{ $r['persen'] >= 80 ? 'b-green' : ($r['persen'] >= 60 ? 'b-orange' : 'b-red') }}">
                    {{ $r['persen'] }}%
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} &nbsp;·&nbsp; {{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}
</div>
</body>
</html>
