<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2d3561; padding-bottom: 15px; }
        .header h2 { color: #2d3561; margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2d3561; color: white; padding: 7px 5px; text-align: left; font-size: 10px; }
        td { padding: 6px 5px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .hadir { background: #c6f6d5; color: #276749; }
        .terlambat { background: #feebc8; color: #c05621; }
        .izin { background: #bee3f8; color: #2c5282; }
        .sakit { background: #fed7e2; color: #97266d; }
        .alpha { background: #fed7e2; color: #c53030; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI KARYAWAN</h2>
        <p>PT Global Multi Pharmalab | Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Shift</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $i => $a)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $a->karyawan->nik ?? '-' }}</td>
                <td>{{ $a->karyawan->nama ?? '-' }}</td>
                <td>{{ $a->karyawan->departemen ?? '-' }}</td>
                <td>{{ $a->shift->nama_shift ?? '-' }}</td>
                <td>{{ $a->jam_masuk ?? '-' }}</td>
                <td>{{ $a->jam_keluar ?? '-' }}</td>
                <td><span class="badge {{ $a->status }}">{{ strtoupper($a->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total: {{ $absensi->count() }} record
    </div>
</body>
</html>
