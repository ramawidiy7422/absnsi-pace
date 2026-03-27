<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Absensi - {{ $karyawan->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2d3748; background: #fff; }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1a1f36, #2d3561);
            color: white;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-logo { font-size: 16px; font-weight: 700; }
        .header-logo small { font-size: 10px; opacity: 0.7; display: block; margin-top: 2px; }
        .header-title { text-align: right; }
        .header-title h2 { font-size: 14px; font-weight: 700; }
        .header-title span { font-size: 10px; opacity: 0.7; }

        .body { padding: 20px 24px; }

        /* Info karyawan */
        .karyawan-section {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
            display: flex;
            gap: 24px;
        }
        .kary-field { flex: 1; }
        .kary-field label { font-size: 9px; text-transform: uppercase; color: #a0aec0; letter-spacing: 0.5px; display: block; }
        .kary-field strong { font-size: 12px; color: #2d3748; }

        /* Rekap summary */
        .rekap-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }
        .rekap-box {
            flex: 1;
            text-align: center;
            padding: 12px 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .rekap-box .val { font-size: 22px; font-weight: 800; line-height: 1; }
        .rekap-box .lbl { font-size: 9px; color: #718096; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
        .rekap-box.hadir  { background: #f0fff4; border-color: #9ae6b4; } .rekap-box.hadir .val  { color: #276749; }
        .rekap-box.tlmbt  { background: #fffaf0; border-color: #fbd38d; } .rekap-box.tlmbt .val  { color: #c05621; }
        .rekap-box.izin   { background: #ebf8ff; border-color: #90cdf4; } .rekap-box.izin .val   { color: #2b6cb0; }
        .rekap-box.sakit  { background: #fff5f5; border-color: #fed7d7; } .rekap-box.sakit .val  { color: #c53030; }
        .rekap-box.alpha  { background: #fff5f5; border-color: #fed7d7; } .rekap-box.alpha .val  { color: #c53030; }
        .rekap-box.persen { background: #faf5ff; border-color: #d6bcfa; } .rekap-box.persen .val { color: #6b46c1; }

        /* Progress bar */
        .progress-wrap { background: #e2e8f0; border-radius: 6px; height: 8px; overflow: hidden; margin: 4px 0 8px; }
        .progress-fill { height: 100%; border-radius: 6px;
            background: {{ $rekap['persen'] >= 90 ? '#48bb78' : ($rekap['persen'] >= 70 ? '#ed8936' : '#fc8181') }}; }

        /* Tabel detail */
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead th {
            background: #1a1f36; color: #a0aec0;
            padding: 7px 10px; text-align: left;
            font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #f7fafc; }
        tbody tr:last-child td { border-bottom: none; }

        /* Status badge */
        .status { padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .s-hadir     { background: #c6f6d5; color: #276749; }
        .s-terlambat { background: #feebc8; color: #c05621; }
        .s-izin      { background: #bee3f8; color: #2c5282; }
        .s-sakit     { background: #fed7e2; color: #97266d; }
        .s-alpha     { background: #fed7e2; color: #c53030; }

        /* Footer */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-info { font-size: 9px; color: #a0aec0; }
        .ttd-box { text-align: center; }
        .ttd-box .garis { border-top: 1px solid #2d3748; width: 160px; margin: 40px auto 4px; }
        .ttd-box small { font-size: 9px; color: #718096; }

        .page-title {
            font-size: 13px; font-weight: 700; color: #2d3748;
            margin-bottom: 12px; padding-bottom: 8px;
            border-bottom: 2px solid #4299e1;
            display: flex; justify-content: space-between; align-items: center;
        }
        .page-title span { font-size: 10px; color: #718096; font-weight: 400; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-logo">
        PT Global Multi Pharmalab
        <small>Sistem Absensi Digital</small>
    </div>
    <div class="header-title">
        <h2>SLIP ABSENSI KARYAWAN</h2>
        <span>Periode: {{ $namaBulan }}</span>
    </div>
</div>

<div class="body">

    {{-- Judul --}}
    <div class="page-title">
        Data Absensi Detail
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
    </div>

    {{-- Info Karyawan --}}
    <div class="karyawan-section">
        <div class="kary-field">
            <label>Nama Karyawan</label>
            <strong>{{ $karyawan->nama }}</strong>
        </div>
        <div class="kary-field">
            <label>NIK</label>
            <strong>{{ $karyawan->nik }}</strong>
        </div>
        <div class="kary-field">
            <label>Departemen</label>
            <strong>{{ $karyawan->departemen }}</strong>
        </div>
        <div class="kary-field">
            <label>Jabatan</label>
            <strong>{{ $karyawan->jabatan }}</strong>
        </div>
        <div class="kary-field">
            <label>Shift</label>
            <strong>{{ $karyawan->shift->nama_shift ?? '-' }}</strong>
        </div>
    </div>

    {{-- Rekap Ringkas --}}
    <div class="rekap-grid">
        <div class="rekap-box hadir">
            <div class="val">{{ $rekap['hadir'] }}</div>
            <div class="lbl">Hadir</div>
        </div>
        <div class="rekap-box tlmbt">
            <div class="val">{{ $rekap['terlambat'] }}</div>
            <div class="lbl">Terlambat</div>
        </div>
        <div class="rekap-box izin">
            <div class="val">{{ $rekap['izin'] }}</div>
            <div class="lbl">Izin</div>
        </div>
        <div class="rekap-box sakit">
            <div class="val">{{ $rekap['sakit'] }}</div>
            <div class="lbl">Sakit</div>
        </div>
        <div class="rekap-box alpha">
            <div class="val">{{ $rekap['alpha'] }}</div>
            <div class="lbl">Alpha</div>
        </div>
        <div class="rekap-box persen">
            <div class="val">{{ $rekap['persen'] }}%</div>
            <div class="lbl">Kehadiran</div>
        </div>
    </div>

    <div style="font-size:10px; color:#718096; margin-bottom:4px;">
        Kehadiran: {{ $rekap['hadir'] + $rekap['terlambat'] }} / {{ $rekap['hari_kerja'] }} hari kerja
    </div>
    <div class="progress-wrap">
        <div class="progress-fill" style="width: {{ $rekap['persen'] }}%;"></div>
    </div>

    {{-- Tabel Detail --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Shift</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Metode</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $i => $a)
            <tr>
                <td style="color:#a0aec0;">{{ $i+1 }}</td>
                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('D') }}</td>
                <td>{{ $a->shift->nama_shift ?? '-' }}</td>
                <td>{{ $a->jam_masuk ?? '-' }}</td>
                <td>{{ $a->jam_keluar ?? '-' }}</td>
                <td>
                    @if($a->durasi_menit)
                        {{ intdiv($a->durasi_menit, 60) }}j {{ $a->durasi_menit % 60 }}m
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="status s-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
                    @if($a->pulang_cepat)
                        <span style="font-size:8px;color:#c53030;"> ⚠</span>
                    @endif
                </td>
                <td style="color:#718096;">
                    @if(($a->metode ?? '') === 'qrcode') QR
                    @elseif(($a->metode ?? '') === 'manual') Manual
                    @else Wajah
                    @endif
                </td>
                <td style="color:#718096;">{{ $a->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:20px; color:#a0aec0;">
                    Tidak ada data absensi bulan ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-info">
            <div>PT Global Multi Pharmalab &mdash; Sistem Absensi Digital</div>
            <div style="margin-top:2px;">Dokumen ini digenerate secara otomatis oleh sistem</div>
        </div>
        <div class="ttd-box">
            <div class="garis"></div>
            <div>HRD / Manager</div>
            <small>Tanda Tangan & Stempel</small>
        </div>
    </div>

</div>
</body>
</html>
