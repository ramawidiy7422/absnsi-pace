<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Absensi - {{ $karyawan->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #1a1f36; color: white; }
        table.header-table td { padding: 20px 24px; color: white; }
        .header-logo { font-size: 16px; font-weight: 700; }
        .header-logo small { font-size: 10px; opacity: 0.7; display: block; margin-top: 2px; }
        .header-title { text-align: right; }
        .header-title h2 { font-size: 14px; font-weight: 700; }
        .header-title span { font-size: 10px; opacity: 0.7; }

        .body { padding: 20px 24px; }

        table.karyawan-table { width: 100%; background: #f7fafc; border: 1px solid #e2e8f0; margin-bottom: 16px; }
        table.karyawan-table td { padding: 12px 16px; }
        .kary-label { font-size: 9px; text-transform: uppercase; color: #a0aec0; letter-spacing: 0.5px; display: block; }
        .kary-value { font-size: 12px; color: #2d3748; font-weight: 700; }

        table.rekap-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .rekap-box { text-align: center; padding: 12px 8px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .r-val { font-size: 22px; font-weight: 800; line-height: 1; }
        .r-lbl { font-size: 9px; color: #718096; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
        .r-hadir  { background: #f0fff4; border-color: #9ae6b4; } .r-hadir .r-val  { color: #276749; }
        .r-tlmbt  { background: #fffaf0; border-color: #fbd38d; } .r-tlmbt .r-val  { color: #c05621; }
        .r-izin   { background: #ebf8ff; border-color: #90cdf4; } .r-izin .r-val   { color: #2b6cb0; }
        .r-sakit  { background: #fff5f5; border-color: #fed7d7; } .r-sakit .r-val  { color: #c53030; }
        .r-alpha  { background: #fff5f5; border-color: #fed7d7; } .r-alpha .r-val  { color: #c53030; }
        .r-persen { background: #faf5ff; border-color: #d6bcfa; } .r-persen .r-val { color: #6b46c1; }

        .bar-outer { display: block; background: #e2e8f0; border-radius: 6px; height: 8px; margin: 4px 0 8px; }
        .bar-inner { display: block; height: 8px; border-radius: 6px; }

        table.data { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.data thead th { background: #1a1f36; color: #a0aec0; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.data tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; }
        table.data tbody tr:nth-child(even) { background: #f7fafc; }
        table.data tbody tr:last-child td { border-bottom: none; }

        .status { padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .s-hadir     { background: #c6f6d5; color: #276749; }
        .s-terlambat { background: #feebc8; color: #c05621; }
        .s-izin      { background: #bee3f8; color: #2c5282; }
        .s-sakit     { background: #fed7e2; color: #97266d; }
        .s-alpha     { background: #fed7e2; color: #c53030; }

        .page-title-table { width: 100%; margin-bottom: 12px; border-bottom: 2px solid #4299e1; padding-bottom: 8px; }
        .page-title-table td { font-size: 13px; font-weight: 700; color: #2d3748; }
        .footer-info { font-size: 9px; color: #a0aec0; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="header-logo">
                {{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}
                <small>{{ $alamatPerusahaan ?? '' }}@if(!empty($kotaPerusahaan)) &bull; {{ $kotaPerusahaan }}@endif</small>
                @if(!empty($noTelpPerusahaan))<small style="opacity:0.6;">Telp: {{ $noTelpPerusahaan }}</small>@endif
            </div>
        </td>
        <td style="width:40%;">
            <div class="header-title">
                <h2>SLIP ABSENSI KARYAWAN</h2>
                <span>Periode: {{ $namaBulan }}</span>
            </div>
        </td>
    </tr>
</table>

<div class="body">

    <table class="page-title-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>Data Absensi Detail</td>
            <td style="text-align:right;font-size:10px;color:#718096;font-weight:400;">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</td>
        </tr>
    </table>

    {{-- Info Karyawan --}}
    <table class="karyawan-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:20%;">
                <span class="kary-label">Nama Karyawan</span>
                <span class="kary-value">{{ $karyawan->nama }}</span>
            </td>
            <td style="width:20%;">
                <span class="kary-label">ID Karyawan</span>
                <span class="kary-value">{{ $karyawan->nik }}</span>
            </td>
            <td style="width:20%;">
                <span class="kary-label">Departemen</span>
                <span class="kary-value">{{ $karyawan->department?->name ?? $karyawan->departemen }}</span>
            </td>
            <td style="width:20%;">
                <span class="kary-label">Jabatan</span>
                <span class="kary-value">{{ $karyawan->jabatan }}</span>
            </td>
            <td style="width:20%;">
                <span class="kary-label">Shift</span>
                <span class="kary-value">{{ $karyawan->shift?->nama_shift ?? '-' }}</span>
            </td>
        </tr>
    </table>

    {{-- Rekap Ringkas --}}
    <table class="rekap-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="rekap-box r-hadir">
                <div class="r-val">{{ $rekap['hadir'] }}</div>
                <div class="r-lbl">Hadir</div>
            </td>
            <td class="rekap-box r-tlmbt">
                <div class="r-val">{{ $rekap['terlambat'] }}</div>
                <div class="r-lbl">Terlambat</div>
            </td>
            <td class="rekap-box r-izin">
                <div class="r-val">{{ $rekap['izin'] }}</div>
                <div class="r-lbl">Izin</div>
            </td>
            <td class="rekap-box r-sakit">
                <div class="r-val">{{ $rekap['sakit'] }}</div>
                <div class="r-lbl">Sakit</div>
            </td>
            <td class="rekap-box r-alpha">
                <div class="r-val">{{ $rekap['alpha'] }}</div>
                <div class="r-lbl">Alpha</div>
            </td>
            <td class="rekap-box r-persen">
                <div class="r-val">{{ $rekap['persen'] }}%</div>
                <div class="r-lbl">Kehadiran</div>
            </td>
        </tr>
    </table>

    <div style="font-size:10px; color:#718096; margin-bottom:4px;">
        Kehadiran: {{ $rekap['hadir'] + $rekap['terlambat'] }} / {{ $rekap['hari_kerja'] }} hari kerja
    </div>
    @php
        $barColor = $rekap['persen'] >= 90 ? '#48bb78' : ($rekap['persen'] >= 70 ? '#ed8936' : '#fc8181');
        $barW = max(1, $rekap['persen']);
    @endphp
    <div class="bar-outer"><div class="bar-inner" style="width:{{ $barW }}%;background:{{ $barColor }};"></div></div>

    {{-- Tabel Detail --}}
    <table class="data">
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
                <td>{{ $a->shift?->nama_shift ?? '-' }}</td>
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
                        <span style="font-size:8px;color:#c53030;"> !</span>
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

    {{-- Footer / Tanda Tangan --}}
    <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
        <table style="width:100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:60%;vertical-align:top;">
                    <div class="footer-info">
                        <div style="font-weight:700; color:#2d3748; font-size:10px;">{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</div>
                        @if(!empty($alamatPerusahaan))<div style="margin-top:1px;color:#4a5568;">{{ $alamatPerusahaan }}{{ !empty($kotaPerusahaan) ? ', '.$kotaPerusahaan : '' }}</div>@endif
                        @if(!empty($noTelpPerusahaan))<div style="color:#718096;">Telp: {{ $noTelpPerusahaan }}</div>@endif
                        @if(!empty($slipFooter))<div style="margin-top:4px;font-style:italic;color:#718096;">{{ $slipFooter }}</div>@endif
                        <div style="margin-top:4px; color:#a0aec0;">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
                    </div>
                </td>
                <td style="text-align:center;vertical-align:top;">
                    <div style="font-size:10px; color:#4a5568; margin-bottom:4px; font-weight:600;">Mengetahui,</div>
                    <div style="font-size:9px; color:#718096;">Pimpinan / Direktur</div>
                    <div style="border-top: 1px solid #2d3748; width: 140px; margin: 48px auto 4px;"></div>
                    <div style="font-size:9px; color:#4a5568; font-weight:600;">(...................................)</div>
                </td>
            </tr>
        </table>

        <table style="width:100%;margin-top:20px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align:center;width:33%;">
                    <div style="font-size:9px; color:#718096; margin-bottom:4px;">Karyawan Ybs,</div>
                    <div style="font-size:9px; color:#718096; font-style:italic;">{{ $karyawan->nama }}</div>
                    <div style="border-top: 1px solid #2d3748; width: 140px; margin: 48px auto 4px;"></div>
                    <div style="font-size:9px; color:#4a5568; font-weight:600;">{{ $karyawan->nik }}</div>
                </td>
                <td style="text-align:center;width:33%;">
                    <div style="font-size:9px; color:#718096; margin-bottom:4px;">Diperiksa oleh,</div>
                    <div style="font-size:9px; color:#718096; font-style:italic;">Supervisor / Kepala Bagian</div>
                    <div style="border-top: 1px solid #2d3748; width: 140px; margin: 48px auto 4px;"></div>
                    <div style="font-size:9px; color:#4a5568; font-weight:600;">(...................................)</div>
                </td>
                <td style="text-align:center;width:33%;">
                    <div style="font-size:9px; color:#718096; margin-bottom:4px;">Disetujui oleh,</div>
                    <div style="font-size:9px; color:#718096; font-style:italic;">HRD Manager</div>
                    <div style="border-top: 1px solid #2d3748; width: 140px; margin: 48px auto 4px;"></div>
                    <div style="font-size:9px; color:#4a5568; font-weight:600;">(...................................)</div>
                </td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
