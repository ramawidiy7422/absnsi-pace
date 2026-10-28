<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SPD {{ $suratDinas->nomor_spd }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: white; }

    .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm 20mm 15mm 25mm; }

    /* KOP */
    .kop { display:flex; align-items:center; gap:16px; padding-bottom:10px; border-bottom:3px solid #1a3a6e; margin-bottom:6px; }
    .kop img { width:60px; height:60px; object-fit:contain; }
    .kop-teks { flex:1; }
    .kop-teks .perusahaan { font-size:15px; font-weight:bold; color:#1a3a6e; text-transform:uppercase; letter-spacing:0.5px; }
    .kop-teks .alamat { font-size:10px; color:#555; margin-top:3px; line-height:1.5; }
    .kop-sub { font-size:9px; text-align:center; color:#888; margin-bottom:14px; }

    /* JUDUL */
    .judul { text-align:center; margin:18px 0 4px; }
    .judul h2 { font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
    .judul .nomor { font-size:11px; color:#555; margin-top:3px; }
    .garis-judul { border-bottom:2px solid #1a3a6e; margin:10px 0 16px; }

    /* TABEL ISI */
    .tbl { width:100%; border-collapse:collapse; margin-bottom:14px; }
    .tbl td { padding:5px 8px; vertical-align:top; font-size:11.5px; }
    .tbl td:first-child { width:38%; font-weight:600; color:#333; }
    .tbl td:nth-child(2) { width:4%; text-align:center; }
    .tbl td:last-child { width:58%; }

    /* SECTION TITLE */
    .section-title { background:#1a3a6e; color:white; padding:5px 10px; font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; margin:14px 0 6px; border-radius:3px; }

    /* STAF TABLE */
    .staf-tbl { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:11px; }
    .staf-tbl th { background:#f0f4ff; padding:6px 10px; text-align:left; font-weight:700; border:1px solid #cdd5e0; color:#1a3a6e; }
    .staf-tbl td { padding:5px 10px; border:1px solid #e2e8f0; }
    .staf-tbl tr:nth-child(even) td { background:#f9fafc; }

    /* TANDA TANGAN */
    .ttd-wrap { display:flex; gap:0; margin-top:20px; }
    .ttd-box { flex:1; text-align:center; padding:12px; border:1px solid #cdd5e0; }
    .ttd-box:first-child { border-right:none; }
    .ttd-box .label { font-size:10px; font-weight:bold; text-transform:uppercase; color:#555; margin-bottom:4px; }
    .ttd-box .kota-tgl { font-size:10px; color:#333; margin-bottom:50px; }
    .ttd-box .garis { border-bottom:1px solid #333; margin:0 20px 6px; }
    .ttd-box .nama { font-size:11.5px; font-weight:bold; }
    .ttd-box .jabatan { font-size:10px; color:#555; }

    .badge { display:inline-block; padding:2px 10px; border-radius:10px; font-size:10px; font-weight:bold; }
    .badge-draft { background:#fffbeb; color:#c05621; border:1px solid #f6ad55; }
    .badge-disetujui { background:#f0fff4; color:#276749; border:1px solid #9ae6b4; }
    .badge-ditolak { background:#fff5f5; color:#c53030; border:1px solid #fc8181; }

    .footer { text-align:center; font-size:9px; color:#aaa; margin-top:20px; padding-top:10px; border-top:1px solid #e2e8f0; }

    /* Watermark stamp */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-35deg);
        font-size: 72px;
        font-weight: 900;
        font-family: Arial, sans-serif;
        letter-spacing: 4px;
        pointer-events: none;
        z-index: 9999;
        white-space: nowrap;
        text-transform: uppercase;
        opacity: 0.12;
    }
    .watermark.disetujui { color: #276749; border: 8px solid #276749; padding: 8px 20px; border-radius: 10px; }
    .watermark.ditolak   { color: #c53030; border: 8px solid #c53030; padding: 8px 20px; border-radius: 10px; }

    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display:none; }
        .watermark { position: fixed; }
    }
</style>
</head>
<body>

{{-- PRINT BUTTON --}}
<div class="no-print" style="text-align:center;padding:12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;margin-bottom:0;">
    <button onclick="window.print()" style="background:#2b6cb0;color:white;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
        🖨️ Cetak / Save PDF
    </button>
    <button onclick="window.history.back()" style="background:#edf2f7;color:#4a5568;border:none;padding:10px 20px;border-radius:8px;font-size:14px;margin-left:10px;cursor:pointer;">
        ← Kembali
    </button>
</div>

@if($suratDinas->status === 'disetujui')
<div class="watermark disetujui">DISETUJUI</div>
@elseif($suratDinas->status === 'ditolak')
<div class="watermark ditolak">DITOLAK</div>
@endif

<div class="page">

    {{-- KOP SURAT --}}
    <div class="kop">
        @php $logo = $pengaturan->get('logo_perusahaan') ?? ''; @endphp
        @if($logo)
        <img src="{{ public_path(ltrim($logo,'/?')) }}" alt="Logo">
        @else
        <div style="width:60px;height:60px;background:#1a3a6e;border-radius:6px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;">GMP</div>
        @endif
        <div class="kop-teks">
            <div class="perusahaan">PT Global Multi Pharmalab</div>
            <div class="alamat">
                {{ $pengaturan->get('alamat_perusahaan') ?? 'Jl. Raya Industri, Kawasan Industri' }}<br>
                Telp: {{ $pengaturan->get('telepon_perusahaan') ?? '-' }} &nbsp;|&nbsp;
                Email: {{ $pengaturan->get('email_perusahaan') ?? '-' }}
            </div>
        </div>
    </div>
    <div class="kop-sub">Dokumen ini dibuat secara otomatis oleh Sistem Absensi PACE</div>

    {{-- JUDUL --}}
    <div class="judul">
        <h2>Surat Perintah Dinas</h2>
        <div class="nomor">Nomor: {{ $suratDinas->nomor_spd }}</div>
    </div>
    <div class="garis-judul"></div>

    {{-- INTRO --}}
    <p style="font-size:11.5px;line-height:1.8;margin-bottom:14px;">
        Yang bertanda tangan di bawah ini, dengan ini memberikan perintah dinas kepada karyawan berikut untuk melaksanakan tugas kedinasan sebagaimana tercantum dalam surat ini.
    </p>

    {{-- STAF BERTUGAS --}}
    <div class="section-title">Staf yang Bertugas</div>
    <table class="staf-tbl">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:35%">Nama</th>
                <th style="width:30%">Departemen</th>
                <th style="width:30%">Jabatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suratDinas->staf as $i => $s)
            <tr>
                <td style="text-align:center;">{{ $i+1 }}</td>
                <td style="font-weight:600;">{{ $s->nama }}</td>
                <td>{{ $s->department?->name ?? '—' }}</td>
                <td>{{ $s->position?->name ?? $s->jabatan ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- DETAIL DINAS --}}
    <div class="section-title">Keterangan Dinas</div>
    <table class="tbl">
        <tr>
            <td>Keperluan / Tujuan</td>
            <td>:</td>
            <td><strong>{{ $suratDinas->keperluan }}</strong></td>
        </tr>
        <tr>
            <td>Departemen</td>
            <td>:</td>
            <td>{{ $suratDinas->department?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Kota Tujuan</td>
            <td>:</td>
            <td>{{ $suratDinas->kota_tujuan }}</td>
        </tr>
        <tr>
            <td>Tempat / Lokasi</td>
            <td>:</td>
            <td>{{ $suratDinas->lokasi_tujuan }}</td>
        </tr>
        <tr>
            <td>Tanggal Berangkat</td>
            <td>:</td>
            <td>{{ $suratDinas->tanggal_berangkat->translatedFormat('d F Y') }}
                @if($suratDinas->jam_berangkat)
                pukul {{ substr($suratDinas->jam_berangkat, 0, 5) }} WIB
                @endif
            </td>
        </tr>
        <tr>
            <td>Tanggal Kembali</td>
            <td>:</td>
            <td>{{ $suratDinas->tanggal_kembali->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Durasi</td>
            <td>:</td>
            <td>{{ $suratDinas->tanggal_berangkat->diffInDays($suratDinas->tanggal_kembali) + 1 }} hari</td>
        </tr>
        <tr>
            <td>Jenis Kendaraan</td>
            <td>:</td>
            <td>{{ $suratDinas->label_kendaraan }}</td>
        </tr>
        @if($suratDinas->nomor_kendaraan)
        <tr>
            <td>Nomor Kendaraan</td>
            <td>:</td>
            <td>{{ strtoupper($suratDinas->nomor_kendaraan) }}</td>
        </tr>
        @endif
        @if($suratDinas->catatan)
        <tr>
            <td>Catatan</td>
            <td>:</td>
            <td><em>{{ $suratDinas->catatan }}</em></td>
        </tr>
        @endif
    </table>

    {{-- PENUTUP --}}
    <p style="font-size:11.5px;line-height:1.8;margin:14px 0;">
        Demikian Surat Perintah Dinas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
    </p>

    {{-- TANDA TANGAN --}}
    <div class="ttd-wrap">
        <div class="ttd-box">
            <div class="label">Yang Memberi Tugas</div>
            <div class="kota-tgl">
                {{ $suratDinas->pemberi_tugas_jabatan ?: 'SPV / Manager' }}
            </div>
            <div class="garis"></div>
            <div class="nama">{{ $suratDinas->pemberi_tugas_nama ?: '( ........................ )' }}</div>
            <div class="jabatan">{{ $suratDinas->pemberi_tugas_jabatan ?: '' }}</div>
        </div>
        <div class="ttd-box">
            <div class="label">Mengetahui</div>
            <div class="kota-tgl">HRD</div>
            <div class="garis"></div>
            <div class="nama">{{ $suratDinas->hrd_nama ?: '( ........................ )' }}</div>
            <div class="jabatan">Human Resource Development</div>
        </div>
        <div class="ttd-box">
            <div class="label">Yang Melaksanakan Tugas</div>
            <div class="kota-tgl">
                @if($suratDinas->staf->count() === 1)
                {{ $suratDinas->staf->first()?->jabatan ?? '' }}
                @else
                Tim yang Bertugas
                @endif
            </div>
            <div class="garis"></div>
            <div class="nama">
                @if($suratDinas->staf->count() === 1)
                {{ $suratDinas->staf->first()?->nama ?? '( ........................ )' }}
                @else
                ( ........................ )
                @endif
            </div>
            <div class="jabatan">
                @if($suratDinas->staf->count() === 1)
                {{ $suratDinas->staf->first()?->position?->name ?? '' }}
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp;
        {{ $suratDinas->nomor_spd }} &nbsp;|&nbsp;
        Sistem Absensi PACE — PT Global Multi Pharmalab
    </div>

</div>
</body>
</html>
