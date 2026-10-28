<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>FMK {{ $meninggalkanKerja->nomor_fmk }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: white; }
    .no-print { text-align:center; padding:12px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 18mm 20mm 15mm 25mm; }
    .kop { display:flex; align-items:center; gap:16px; padding-bottom:10px; border-bottom:3px solid #c05621; margin-bottom:4px; }
    .kop img { width:58px; height:58px; object-fit:contain; }
    .kop-teks .perusahaan { font-size:15px; font-weight:bold; color:#1a3a6e; text-transform:uppercase; }
    .kop-teks .alamat { font-size:9.5px; color:#555; margin-top:3px; line-height:1.5; }
    .kop-sub { font-size:9px; text-align:center; color:#aaa; margin-bottom:16px; }

    /* Judul formal surat */
    .judul { text-align:center; margin:18px 0 6px; }
    .judul h2 { font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:1.5px; }
    .judul .nomor { font-size:11px; color:#555; margin-top:4px; }
    .garis-judul { border:none; border-bottom:2px solid #c05621; margin:8px auto 22px; width:80%; }

    /* Data karyawan surat-style */
    .surat-body { font-size:12px; line-height:2; }
    .surat-body .pernyataan { margin-bottom:14px; font-style:italic; }
    .data-grid { width:100%; border-collapse:collapse; margin-bottom:14px; }
    .data-grid td { padding:3px 0; vertical-align:top; font-size:12px; }
    .data-grid td:first-child { width:34%; color:#444; }
    .data-grid td:nth-child(2) { width:4%; text-align:center; color:#444; }
    .data-grid td:last-child { font-weight:600; }

    /* Permohonan block */
    .permohonan-box { border:1px solid #d0d7de; border-radius:5px; padding:12px 16px; margin:14px 0; background:#fafafa; }
    .permohonan-box .pb-title { font-size:11px; font-weight:bold; text-transform:uppercase; color:#c05621; margin-bottom:10px; border-bottom:1px dashed #e2e8f0; padding-bottom:5px; }
    .permohonan-box table { width:100%; border-collapse:collapse; }
    .permohonan-box table td { padding:3px 0; font-size:12px; vertical-align:top; }
    .permohonan-box table td:first-child { width:38%; color:#555; }
    .permohonan-box table td:nth-child(2) { width:4%; text-align:center; }
    .permohonan-box table td:last-child { font-weight:700; }

    /* Keterangan badge */
    .ket-box { margin:14px 0; padding:8px 14px; border-radius:5px; font-size:11.5px; font-weight:700; display:inline-block; }
    .ket-kembali { background:#f0fff4; color:#276749; border:1px solid #9ae6b4; }
    .ket-tidak   { background:#fff5f5; color:#c53030; border:1px solid #fc8181; }

    /* Status badge */
    .badge { display:inline-block; padding:3px 12px; border-radius:12px; font-size:11px; font-weight:bold; }
    .badge-menunggu  { background:#fffbeb; color:#c05621; border:1px solid #f6ad55; }
    .badge-disetujui { background:#f0fff4; color:#276749; border:1px solid #9ae6b4; }
    .badge-ditolak   { background:#fff5f5; color:#c53030; border:1px solid #fc8181; }

    /* Pernyataan bawah */
    .pernyataan-box { border:1px solid #e2e8f0; border-radius:5px; background:#f9fafb; padding:10px 14px; margin:18px 0; font-size:11px; line-height:1.8; color:#555; }

    /* TTD 3 kolom */
    .ttd-wrap { display:flex; margin-top:28px; border:1px solid #d0d7de; border-radius:5px; overflow:hidden; }
    .ttd-box { flex:1; text-align:center; padding:14px 10px; }
    .ttd-box + .ttd-box { border-left:1px solid #d0d7de; }
    .ttd-box .ttd-label { font-size:10px; font-weight:bold; text-transform:uppercase; color:#555; margin-bottom:5px; }
    .ttd-box .ttd-sub   { font-size:9.5px; color:#888; margin-bottom:52px; }
    .ttd-box .ttd-line  { border-bottom:1px solid #333; margin:0 10px 6px; }
    .ttd-box .ttd-nama  { font-size:11.5px; font-weight:bold; color:#1a3a6e; }
    .ttd-box .ttd-jabatan { font-size:9.5px; color:#718096; margin-top:2px; }

    .footer { text-align:center; font-size:9px; color:#aaa; margin-top:28px; padding-top:10px; border-top:1px solid #e2e8f0; }

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
        .no-print { display:none; }
        body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .watermark { position: fixed; }
    }
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="background:#c05621;color:white;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
        🖨️ Cetak / Save PDF
    </button>
    <button onclick="window.history.back()" style="background:#edf2f7;color:#4a5568;border:none;padding:10px 20px;border-radius:8px;font-size:14px;margin-left:10px;cursor:pointer;">
        ← Kembali
    </button>
</div>

@if($meninggalkanKerja->status === 'disetujui')
<div class="watermark disetujui">DISETUJUI</div>
@elseif($meninggalkanKerja->status === 'ditolak')
<div class="watermark ditolak">DITOLAK</div>
@endif

<div class="page">

    {{-- KOP SURAT --}}
    <div class="kop">
        @php $logo = $pengaturan->get('logo_perusahaan') ?? ''; @endphp
        @if($logo)
        <img src="{{ public_path(ltrim($logo,'/?')) }}" alt="Logo">
        @else
        <div style="width:58px;height:58px;background:#1a3a6e;border-radius:6px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;">GMP</div>
        @endif
        <div class="kop-teks">
            <div class="perusahaan">PT Global Multi Pharmalab</div>
            <div class="alamat">
                {{ $pengaturan->get('alamat_perusahaan','') }}<br>
                Telp: {{ $pengaturan->get('telepon_perusahaan','-') }} | Email: {{ $pengaturan->get('email_perusahaan','-') }}
            </div>
        </div>
    </div>
    <div class="kop-sub">Dokumen ini dibuat secara otomatis oleh Sistem Absensi PACE</div>

    {{-- JUDUL --}}
    <div class="judul">
        <h2>Formulir Meninggalkan Kerja</h2>
        <div class="nomor">No: {{ $meninggalkanKerja->nomor_fmk }}</div>
    </div>
    <div class="garis-judul"></div>

    {{-- IDENTITAS PEMOHON --}}
    <div class="surat-body">
        <div class="pernyataan">
            Saya yang bertanda tangan di bawah ini:
        </div>
        <table class="data-grid">
            <tr>
                <td>Nama</td><td>:</td>
                <td>{{ $meninggalkanKerja->karyawan?->nama }}</td>
            </tr>
            <tr>
                <td>NIK</td><td>:</td>
                <td>{{ $meninggalkanKerja->karyawan?->nik }}</td>
            </tr>
            <tr>
                <td>Departemen</td><td>:</td>
                <td>{{ $meninggalkanKerja->karyawan?->department?->name ?? '—' }}</td>
            </tr>
            @if($meninggalkanKerja->bagian)
            <tr>
                <td>Bagian</td><td>:</td>
                <td>{{ $meninggalkanKerja->bagian }}</td>
            </tr>
            @endif
            <tr>
                <td>Jabatan</td><td>:</td>
                <td>{{ $meninggalkanKerja->karyawan?->position?->name ?? '—' }}</td>
            </tr>
        </table>

        <div style="font-style:italic;margin-bottom:10px;">
            Mengajukan permohonan izin meninggalkan kerja pada:
        </div>

        {{-- DETAIL PERMOHONAN --}}
        <div class="permohonan-box">
            <div class="pb-title">Keterangan Meninggalkan Kerja</div>
            <table>
                <tr>
                    <td>Hari / Tanggal</td><td>:</td>
                    <td>{{ $meninggalkanKerja->tanggal->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td>Jam</td><td>:</td>
                    <td>
                        Pukul <strong>{{ substr($meninggalkanKerja->jam_keluar,0,5) }} WIB</strong>
                        s/d
                        {{ $meninggalkanKerja->jam_kembali ? 'Pukul '.substr($meninggalkanKerja->jam_kembali,0,5).' WIB' : '.............. WIB' }}
                    </td>
                </tr>
                <tr>
                    <td>Alasan</td><td>:</td>
                    <td>{{ $meninggalkanKerja->label_alasan }}</td>
                </tr>
                @if($meninggalkanKerja->keterangan)
                <tr>
                    <td>Keterangan</td><td>:</td>
                    <td>{{ $meninggalkanKerja->keterangan }}</td>
                </tr>
                @endif
                <tr>
                    <td>Status</td><td>:</td>
                    <td><span class="badge badge-{{ $meninggalkanKerja->status }}">{{ $meninggalkanKerja->label_status }}</span></td>
                </tr>
            </table>
        </div>

        {{-- KETERANGAN KEMBALI --}}
        <div style="margin:14px 0;">
            <span style="font-size:12px;margin-right:10px;">Keterangan:</span>
            @if($meninggalkanKerja->kembali_bekerja === 'kembali')
            <span class="ket-box ket-kembali">✓ Kembali Bekerja</span>
            @else
            <span class="ket-box ket-tidak">✗ Tidak Kembali</span>
            @endif
        </div>

        {{-- PERNYATAAN --}}
        <div class="pernyataan-box">
            Demikian permohonan ini saya sampaikan. Yang bertanda tangan di bawah ini menyatakan bahwa karyawan tersebut
            benar-benar meninggalkan area kerja pada waktu yang tercantum di atas dengan alasan yang telah disebutkan.
            Karyawan wajib melapor kepada atasan langsung setelah kembali ke tempat kerja.
        </div>
    </div>

    {{-- TANDA TANGAN 3 KOLOM --}}
    <div class="ttd-wrap">
        <div class="ttd-box">
            <div class="ttd-label">Pemohon / Karyawan</div>
            <div class="ttd-sub">Yang mengajukan izin</div>
            <div class="ttd-line"></div>
            <div class="ttd-nama">{{ $meninggalkanKerja->karyawan?->nama }}</div>
            <div class="ttd-jabatan">{{ $meninggalkanKerja->karyawan?->position?->name ?? 'Karyawan' }}</div>
        </div>
        <div class="ttd-box">
            <div class="ttd-label">Menyetujui</div>
            <div class="ttd-sub">SPV / Manager</div>
            <div class="ttd-line"></div>
            <div class="ttd-nama">{{ $meninggalkanKerja->spv_nama ?: '( ............................ )' }}</div>
            <div class="ttd-jabatan">Supervisor / Manager</div>
        </div>
        <div class="ttd-box">
            <div class="ttd-label">Mengetahui</div>
            <div class="ttd-sub">Human Resource Development</div>
            <div class="ttd-line"></div>
            <div class="ttd-nama">
                @if($meninggalkanKerja->hrd_nama)
                    {{ $meninggalkanKerja->hrd_nama }}
                @elseif($meninggalkanKerja->status === 'disetujui')
                    {{ $meninggalkanKerja->approver?->name ?? '( ............................ )' }}
                @else
                    ( ............................ )
                @endif
            </div>
            <div class="ttd-jabatan">HRD</div>
        </div>
    </div>

    <div class="footer">
        Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp; {{ $meninggalkanKerja->nomor_fmk }} &nbsp;|&nbsp; Sistem Absensi PACE — PT Global Multi Pharmalab
    </div>

</div>
</body>
</html>
