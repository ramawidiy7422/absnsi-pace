<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Shift {{ $namaBulan }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; color: #2d3748; background: white; }

.header {
    background: #553c9a;
    color: white;
    padding: 12px 16px;
    margin-bottom: 12px;
    border-radius: 6px;
}
.header h1 { font-size: 14px; font-weight: 800; margin-bottom: 2px; }
.header p  { font-size: 9px; opacity: 0.85; }

.meta { margin-bottom: 10px; font-size: 9px; color: #718096; }
.meta span { background: #f3e8ff; color: #553c9a; padding: 2px 8px; border-radius: 4px; font-weight: 700; margin-right: 8px; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.5px;
    table-layout: fixed;
}
thead th {
    background: #553c9a;
    color: white;
    padding: 5px 2px;
    text-align: center;
    font-weight: 700;
    border: 1px solid #6b46c1;
    white-space: nowrap;
    overflow: hidden;
}
thead th.col-no   { width: 20px; }
thead th.col-nama { width: 110px; text-align: left; padding-left: 5px; }
thead th.col-dept { width: 80px; text-align: left; }
thead th.col-day  { width: 18px; font-size: 6.5px; }

tbody td {
    padding: 3px 2px;
    border: 1px solid #e2e8f0;
    text-align: center;
    vertical-align: middle;
}
tbody td.col-nama {
    text-align: left; padding-left: 5px;
    font-weight: 600; color: #2d3748;
    white-space: nowrap; overflow: hidden;
}
tbody td.col-dept {
    text-align: left; font-size: 6.5px; color: #718096;
    white-space: nowrap; overflow: hidden;
}
tbody tr:nth-child(even) { background: #fafafa; }

.dept-row td {
    background: #f3e8ff;
    font-weight: 800; color: #553c9a;
    font-size: 7px; padding: 4px 5px;
    text-transform: uppercase; letter-spacing: 0.3px;
    border: 1px solid #e9d8fd;
}

.badge {
    display: inline-block;
    padding: 1px 3px;
    border-radius: 3px;
    font-size: 6.5px;
    font-weight: 800;
    color: white;
}
.badge-P  { background: #3182ce; } /* Pagi */
.badge-Si { background: #dd6b20; } /* Siang */
.badge-M  { background: #6b46c1; } /* Malam */
.badge-N  { background: #38a169; } /* Normal */
.badge-X  { background: #718096; } /* Other */

.weekend { background: #fef9c3 !important; }

.legend { margin-top: 10px; font-size: 8px; }
.legend-item { display: inline-block; margin-right: 10px; }
.legend-dot  { display: inline-block; width: 10px; height: 10px; border-radius: 2px; vertical-align: middle; margin-right: 3px; }

.footer {
    margin-top: 12px;
    font-size: 7.5px;
    color: #a0aec0;
    text-align: right;
}
</style>
</head>
<body>

<div class="header">
    <h1>&#128197; Jadwal Shift Karyawan</h1>
    <p>
        {{ $namaBulan }}
        @if($departemen) &mdash; Departemen: {{ $departemen }} @endif
        &nbsp;&middot;&nbsp; Total: {{ $karyawanList->count() }} karyawan
    </p>
</div>

<table>
    <thead>
        <tr>
            <th class="col-no">#</th>
            <th class="col-nama">Nama Karyawan</th>
            <th class="col-dept">Departemen / Jabatan</th>
            @for($d = 1; $d <= $jumlahHari; $d++)
                @php $tgl = \Carbon\Carbon::createFromDate($tahun, $bulan, $d); @endphp
                <th class="col-day {{ $tgl->isWeekend() ? 'weekend' : '' }}">
                    {{ $d }}<br>
                    <span style="font-size:5.5px;font-weight:400;">{{ strtoupper(substr($tgl->translatedFormat('D'),0,2)) }}</span>
                </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $prevDept = null; @endphp
        @foreach($karyawanList as $k)
            @php $deptName = $k->department?->name ?? $k->departemen; @endphp
            @if($deptName !== $prevDept)
                <tr class="dept-row">
                    <td colspan="{{ 3 + $jumlahHari }}">&#9632; {{ $deptName ?? 'Tanpa Departemen' }}</td>
                </tr>
                @php $prevDept = $deptName; @endphp
            @endif
            <tr>
                <td>{{ $no++ }}</td>
                <td class="col-nama">{{ $k->nama }}</td>
                <td class="col-dept">{{ $k->jabatan ?? '-' }}</td>
                @for($d = 1; $d <= $jumlahHari; $d++)
                    @php
                        $tgl    = \Carbon\Carbon::createFromDate($tahun, $bulan, $d);
                        $key    = $tgl->format('Y-m-d');
                        $shift  = $jadwal[$k->id][$key] ?? null;
                        $defShift = $k->shift;
                        $weekend = $tgl->isWeekend();

                        if ($shift) {
                            $label = strtoupper(substr($shift->nama_shift, 0, 2));
                            $nm    = strtolower($shift->nama_shift);
                            $cls   = str_contains($nm,'pagi') ? 'P' : (str_contains($nm,'siang') ? 'Si' : (str_contains($nm,'malam') ? 'M' : (str_contains($nm,'normal') ? 'N' : 'X')));
                        } elseif ($defShift && !$weekend) {
                            $label = strtoupper(substr($defShift->nama_shift, 0, 2));
                            $nm    = strtolower($defShift->nama_shift);
                            $cls   = str_contains($nm,'pagi') ? 'P' : (str_contains($nm,'siang') ? 'Si' : (str_contains($nm,'malam') ? 'M' : (str_contains($nm,'normal') ? 'N' : 'X')));
                        } else {
                            $label = null;
                        }
                    @endphp
                    <td class="{{ $weekend ? 'weekend' : '' }}">
                        @if($label)
                            <span class="badge badge-{{ $cls }}">{{ $label }}</span>
                        @elseif($weekend)
                            <span style="color:#a0aec0;font-size:6px;">L</span>
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>

<div class="legend">
    <strong>Keterangan:</strong>
    <div class="legend-item"><div class="legend-dot" style="background:#3182ce;"></div> Pa = Pagi</div>
    <div class="legend-item"><div class="legend-dot" style="background:#dd6b20;"></div> Si = Siang</div>
    <div class="legend-item"><div class="legend-dot" style="background:#6b46c1;"></div> Ma = Malam</div>
    <div class="legend-item"><div class="legend-dot" style="background:#38a169;"></div> No = Normal</div>
    <div class="legend-item"><div class="legend-dot" style="background:#fef9c3;border:1px solid #d69e2e;"></div> L = Libur/Weekend</div>
    <div class="legend-item" style="font-size:7px;color:#718096;">* Badge redup = shift default; badge cerah = jadwal khusus</div>
</div>

<div class="footer">
    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} &nbsp;&middot;&nbsp; {{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}
</div>

</body>
</html>
