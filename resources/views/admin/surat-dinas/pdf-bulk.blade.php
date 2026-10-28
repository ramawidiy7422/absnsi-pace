<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Surat Perintah Dinas</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
    .no-print { text-align:center; padding:12px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .page { padding: 15mm 15mm 10mm 20mm; }
    .kop { display:flex; align-items:center; gap:14px; padding-bottom:8px; border-bottom:3px solid #1a3a6e; margin-bottom:4px; }
    .kop-teks .perusahaan { font-size:14px; font-weight:bold; color:#1a3a6e; text-transform:uppercase; }
    .kop-teks .alamat { font-size:9px; color:#555; margin-top:2px; }
    .judul { text-align:center; margin:14px 0 12px; }
    .judul h2 { font-size:13px; font-weight:bold; text-transform:uppercase; }
    .judul .sub { font-size:10px; color:#555; margin-top:3px; }
    table { width:100%; border-collapse:collapse; font-size:10px; }
    th { background:#1a3a6e; color:white; padding:6px 8px; text-align:left; font-size:10px; }
    td { padding:5px 8px; border-bottom:1px solid #e2e8f0; vertical-align:top; }
    tr:nth-child(even) td { background:#f9fafb; }
    .badge { display:inline-block; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:bold; }
    .draft { background:#fffbeb; color:#c05621; }
    .disetujui { background:#f0fff4; color:#276749; }
    .ditolak { background:#fff5f5; color:#c53030; }
    .footer { text-align:center; font-size:8px; color:#aaa; margin-top:16px; padding-top:8px; border-top:1px solid #e2e8f0; }
    @media print {
        .no-print { display:none; }
        body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    }
</style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" style="background:#2b6cb0;color:white;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
        🖨️ Cetak / Save PDF
    </button>
    <button onclick="window.history.back()" style="background:#edf2f7;color:#4a5568;border:none;padding:10px 20px;border-radius:8px;font-size:14px;margin-left:10px;cursor:pointer;">
        ← Kembali
    </button>
</div>

<div class="page">
    <div class="kop">
        @php $logo = $pengaturan->get('logo_perusahaan') ?? ''; @endphp
        @if($logo)
        <img src="{{ public_path(ltrim($logo,'/?')) }}" style="width:50px;height:50px;object-fit:contain;" alt="">
        @else
        <div style="width:50px;height:50px;background:#1a3a6e;border-radius:6px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:14px;">GMP</div>
        @endif
        <div class="kop-teks">
            <div class="perusahaan">PT Global Multi Pharmalab</div>
            <div class="alamat">{{ $pengaturan->get('alamat_perusahaan','') }} | Telp: {{ $pengaturan->get('telepon_perusahaan','-') }}</div>
        </div>
    </div>

    <div class="judul">
        <h2>Daftar Surat Perintah Dinas (SPD)</h2>
        <div class="sub">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB | Total: {{ $dinas->count() }} SPD</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:13%">Nomor SPD</th>
                <th style="width:9%">Tanggal</th>
                <th style="width:12%">Departemen</th>
                <th style="width:18%">Keperluan</th>
                <th style="width:10%">Tujuan</th>
                <th style="width:9%">Berangkat</th>
                <th style="width:9%">Kembali</th>
                <th style="width:12%">Staf</th>
                <th style="width:4%">Dur.</th>
                <th style="width:8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dinas as $i => $spd)
            <tr>
                <td style="text-align:center;">{{ $i+1 }}</td>
                <td style="font-family:monospace;font-weight:700;font-size:9px;">{{ $spd->nomor_spd }}</td>
                <td>{{ $spd->tanggal_dibuat->format('d/m/Y') }}</td>
                <td>{{ $spd->department?->name ?? '-' }}</td>
                <td>{{ Str::limit($spd->keperluan, 40) }}</td>
                <td>{{ $spd->kota_tujuan }}</td>
                <td>{{ $spd->tanggal_berangkat?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $spd->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ ($spd->staf?->pluck('nama')->take(2)->join(', ') ?? '-') }}{{ ($spd->staf?->count() ?? 0) > 2 ? ' +'.($spd->staf->count()-2) : '' }}</td>
                <td style="text-align:center;">{{ ($spd->tanggal_berangkat && $spd->tanggal_kembali) ? ($spd->tanggal_berangkat->diffInDays($spd->tanggal_kembali)+1).'h' : '-' }}</td>
                <td><span class="badge {{ $spd->status }}">{{ $spd->label_status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center;padding:20px;color:#a0aec0;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistem Absensi PACE — PT Global Multi Pharmalab | {{ now()->translatedFormat('d F Y') }}
    </div>
</div>
</body>
</html>
