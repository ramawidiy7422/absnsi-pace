<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peringatan - {{ $karyawan->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

        table.header-table { width: 100%; background: #7b341e; color: white; margin-bottom: 16px; }
        table.header-table td { padding: 16px 20px; color: white; }
        .company { font-size: 13px; font-weight: 700; }
        .sub { font-size: 9px; opacity: 0.7; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: 700; text-align: right; }
        .karyawan-sub { font-size: 10px; opacity: 0.85; margin-top: 3px; text-align: right; }
        .generated { font-size: 9px; opacity: 0.6; margin-top: 2px; text-align: right; }

        .body { padding: 0 20px 20px; }

        table.info-table { width: 100%; background: #f7fafc; border: 1px solid #e2e8f0; margin-bottom: 16px; }
        table.info-table td { padding: 10px 16px; }
        .label { font-size: 8px; color: #718096; text-transform: uppercase; letter-spacing: 0.4px; }
        .value { font-size: 11px; font-weight: 700; color: #2d3748; margin-top: 1px; }

        table.summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .stat-box { text-align: center; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .sv { font-size: 18px; font-weight: 800; }
        .sl { font-size: 8px; color: #718096; margin-top: 2px; }
        .stat-sp1   { background: #fff5f5; } .stat-sp1 .sv   { color: #c53030; }
        .stat-sp2   { background: #fffaf0; } .stat-sp2 .sv   { color: #c05621; }
        .stat-sp3   { background: #fff5f5; } .stat-sp3 .sv   { color: #9b2c2c; }
        .stat-total { background: #f7fafc; } .stat-total .sv { color: #4a5568; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #2d3748;
            margin: 16px 0 8px; padding-bottom: 4px;
            border-bottom: 2px solid #c05621;
        }

        .sp-block { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 14px; overflow: hidden; page-break-inside: avoid; }

        table.sp-header-table { width: 100%; border-bottom: 1px solid #e2e8f0; }
        table.sp-header-table td { padding: 10px 14px; }
        .sp-header-sp1 { background: #fff5f5; }
        .sp-header-sp2 { background: #fffaf0; }
        .sp-header-sp3 { background: #fff5f5; }

        .sp-type { font-size: 16px; font-weight: 800; }
        .sp-type-sp1 { color: #c53030; }
        .sp-type-sp2 { color: #c05621; }
        .sp-type-sp3 { color: #9b2c2c; }

        .sp-status { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .sp-status-aktif   { background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }
        .sp-status-selesai { background: #f0fff4; color: #276749; border: 1px solid #9ae6b4; }

        .sp-body { padding: 10px 14px; }
        table.sp-fields { width: 100%; margin-bottom: 6px; }
        table.sp-fields td { padding: 2px 0; }
        .sp-lbl { font-size: 8px; color: #718096; text-transform: uppercase; letter-spacing: 0.3px; }
        .sp-val { font-size: 10px; font-weight: 600; color: #2d3748; margin-top: 1px; }

        .sp-desc { margin-top: 8px; padding: 8px 10px; background: #f7fafc; border-radius: 4px; border-left: 3px solid #cbd5e0; font-size: 9px; color: #4a5568; line-height: 1.5; }

        .empty-state { text-align: center; padding: 30px; color: #a0aec0; font-style: italic; }

        table.footer-table { width: 100%; margin-top: 24px; border-top: 1px solid #e2e8f0; }
        table.footer-table td { font-size: 8px; color: #a0aec0; padding: 10px 20px; }
    </style>
</head>
<body>

<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:60%;">
            <div class="company">{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }}</div>
            <div class="sub">
                @php
                    $sl=[];
                    if(!empty($alamatPerusahaan)){$sl[]=$alamatPerusahaan.(!empty($kotaPerusahaan)?', '.$kotaPerusahaan:'');}
                    if(!empty($noTelpPerusahaan)){$sl[]='Telp: '.$noTelpPerusahaan;}
                    echo empty($sl)?'Sistem Informasi Absensi &amp; HR':implode(' &bull; ',$sl);
                @endphp
            </div>
        </td>
        <td style="width:40%;">
            <div class="report-title">Riwayat Surat Peringatan</div>
            <div class="karyawan-sub">{{ $karyawan->nama }}</div>
            <div class="generated">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </td>
    </tr>
</table>

<div class="body">

    {{-- Karyawan Info --}}
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:25%;">
                <div class="label">NIK</div>
                <div class="value">{{ $karyawan->nik ?? '-' }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Nama</div>
                <div class="value">{{ $karyawan->nama }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Departemen</div>
                <div class="value">{{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}</div>
            </td>
            <td style="width:25%;">
                <div class="label">Jabatan</div>
                <div class="value">{{ $karyawan->jabatan ?? '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Status Karyawan</div>
                <div class="value">{{ ucfirst($karyawan->status_karyawan ?? '-') }}</div>
            </td>
            <td>
                <div class="label">Tgl Bergabung</div>
                <div class="value">{{ $karyawan->tanggal_masuk?->format('d/m/Y') ?? '-' }}</div>
            </td>
            <td colspan="2"></td>
        </tr>
    </table>

    {{-- Stats --}}
    <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="stat-box stat-total">
                <div class="sv">{{ $spList->count() }}</div>
                <div class="sl">Total SP</div>
            </td>
            <td class="stat-box stat-sp1">
                <div class="sv">{{ $spList->where('type','SP1')->count() }}</div>
                <div class="sl">SP-1</div>
            </td>
            <td class="stat-box stat-sp2">
                <div class="sv">{{ $spList->where('type','SP2')->count() }}</div>
                <div class="sl">SP-2</div>
            </td>
            <td class="stat-box stat-sp3">
                <div class="sv">{{ $spList->where('type','SP3')->count() }}</div>
                <div class="sl">SP-3</div>
            </td>
            <td class="stat-box stat-total">
                <div class="sv">{{ $spList->where('status','aktif')->count() }}</div>
                <div class="sl">Aktif</div>
            </td>
        </tr>
    </table>

    {{-- SP List --}}
    <div class="section-title">Riwayat Surat Peringatan</div>

    @forelse($spList as $sp)
    @php
        $typeClass = strtolower($sp->type);
        $statusClass = $sp->status === 'aktif' ? 'sp-status-aktif' : 'sp-status-selesai';
    @endphp
    <div class="sp-block">
        <table class="sp-header-table sp-header-{{ $typeClass }}" cellpadding="0" cellspacing="0">
            <tr>
                <td class="sp-header-{{ $typeClass }}">
                    <span class="sp-type sp-type-{{ $typeClass }}">{{ $sp->type }}</span>
                </td>
                <td class="sp-header-{{ $typeClass }}" style="text-align:right;">
                    <span class="sp-status {{ $statusClass }}">{{ ucfirst($sp->status) }}</span>
                </td>
            </tr>
        </table>
        <div class="sp-body">
            <table class="sp-fields" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width:50%;">
                        <div class="sp-lbl">Tanggal Dikeluarkan</div>
                        <div class="sp-val">{{ $sp->date?->translatedFormat('d F Y') ?? '-' }}</div>
                    </td>
                    <td style="width:50%;">
                        <div class="sp-lbl">Berlaku Sampai</div>
                        <div class="sp-val">
                            @if($sp->valid_until)
                                {{ $sp->valid_until->translatedFormat('d F Y') }}
                                @if($sp->valid_until->isPast() && $sp->status === 'aktif')
                                 <span style="color:#c53030;font-size:8px;">(sudah lewat)</span>
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
            @if($sp->description)
            <div class="sp-desc">{{ $sp->description }}</div>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">Tidak ada riwayat surat peringatan untuk karyawan ini.</div>
    @endforelse

</div>

<table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
        <td>{{ $namaPerusahaan ?? 'PT Global Multi Pharmalab' }} &mdash; Dokumen Konfidensial</td>
        <td style="text-align:right;">Dicetak dari Sistem Absensi Digital</td>
    </tr>
</table>

</body>
</html>
