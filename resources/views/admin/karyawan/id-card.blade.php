<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ID Card - {{ $karyawan->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 30px 20px;
            gap: 20px;
        }

        .page-actions {
            position: fixed;
            top: 20px; right: 20px;
            display: flex; gap: 8px;
            z-index: 999;
        }
        .page-actions button, .page-actions a {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #2b6cb0; color: #fff; }
        .btn-back  { background: #e2e8f0; color: #4a5568; }

        /* ID Card — credit card size: 85.6mm × 54mm at 96dpi ≈ 324px × 204px */
        .id-card-wrap {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .card-label {
            font-size: 12px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── DEPAN ── */
        .id-card-front {
            width: 324px;
            height: 204px;
            background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 60%, #3182ce 100%);
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
            color: white;
        }

        /* decorative circles */
        .id-card-front::before {
            content: '';
            position: absolute;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            top: -50px; right: -40px;
        }
        .id-card-front::after {
            content: '';
            position: absolute;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -30px; left: 20px;
        }

        .front-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px 8px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            position: relative;
            z-index: 1;
        }
        .front-logo {
            width: 28px; height: 28px;
            border-radius: 6px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .front-logo img { width: 100%; height: 100%; object-fit: contain; }
        .front-company {
            font-size: 8.5px;
            font-weight: 700;
            line-height: 1.3;
            opacity: 0.95;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .front-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
            font-size: 7px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .front-body {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            position: relative;
            z-index: 1;
        }

        .front-photo {
            width: 66px; height: 78px;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.4);
            overflow: hidden;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .front-photo img { width: 100%; height: 100%; object-fit: cover; }
        .front-photo .no-photo {
            font-size: 28px;
            font-weight: 800;
            opacity: 0.6;
        }

        .front-info {
            flex: 1;
            min-width: 0;
        }
        .front-nama {
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 4px;
        }
        .front-jabatan {
            font-size: 9px;
            opacity: 0.85;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .front-dept {
            background: rgba(255,255,255,0.2);
            font-size: 8px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 6px;
        }
        .front-nik {
            font-size: 9px;
            opacity: 0.7;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        .front-footer {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.2);
            padding: 5px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1;
        }
        .front-status {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .front-year {
            font-size: 8px;
            opacity: 0.6;
        }

        /* ── BELAKANG ── */
        .id-card-back {
            width: 324px;
            height: 204px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
        }

        .back-top {
            background: linear-gradient(135deg, #1a365d, #2b6cb0);
            height: 10px;
        }

        .back-body {
            display: flex;
            padding: 14px;
            gap: 14px;
            align-items: center;
        }

        .back-qr {
            flex-shrink: 0;
        }
        .back-qr img {
            width: 90px; height: 90px;
            display: block;
        }

        .back-detail {
            flex: 1;
            min-width: 0;
        }
        .back-title {
            font-size: 9px;
            font-weight: 700;
            color: #2b6cb0;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .back-row {
            display: flex;
            gap: 4px;
            margin-bottom: 5px;
            font-size: 9px;
        }
        .back-key {
            color: #718096;
            font-weight: 600;
            width: 64px;
            flex-shrink: 0;
        }
        .back-val {
            color: #2d3748;
            font-weight: 500;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .back-footer {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 6px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-hint {
            font-size: 8px;
            color: #a0aec0;
        }
        .back-company {
            font-size: 8px;
            font-weight: 700;
            color: #2b6cb0;
        }

        @media print {
            body {
                background: white;
                min-height: unset;
                justify-content: flex-start;
                padding: 10mm;
            }
            .page-actions { display: none; }
            .id-card-wrap { gap: 8mm; }
            .id-card-front, .id-card-back {
                box-shadow: none;
                border: 0.5px solid #e2e8f0;
                page-break-inside: avoid;
            }
            .card-label { font-size: 10px; }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <a href="{{ url()->previous() }}" class="btn-back">&#8592; Kembali</a>
        <button class="btn-print" onclick="window.print()">&#128438; Cetak ID Card</button>
    </div>

    <div class="id-card-wrap">

        {{-- DEPAN --}}
        <div class="card-label">Sisi Depan</div>
        <div class="id-card-front">
            <div class="front-header">
                <div class="front-logo">
                    @if($logoPerusahaan)
                        <img src="{{ asset('storage/' . $logoPerusahaan) }}" alt="Logo">
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                    @endif
                </div>
                <div class="front-company">{{ $namaPerusahaan }}</div>
                <div class="front-badge">ID Card</div>
            </div>

            <div class="front-body">
                <div class="front-photo">
                    @if($karyawan->foto_wajah)
                        <img src="{{ asset('storage/' . $karyawan->foto_wajah) }}" alt="{{ $karyawan->nama }}">
                    @else
                        <span class="no-photo">{{ strtoupper(substr($karyawan->nama, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="front-info">
                    <div class="front-nama">{{ $karyawan->nama }}</div>
                    <div class="front-jabatan">{{ $karyawan->jabatan ?? 'Karyawan' }}</div>
                    <div class="front-dept">{{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}</div>
                    <div class="front-nik">NIK: {{ $karyawan->nik }}</div>
                </div>
            </div>

            <div class="front-footer">
                <div class="front-status">{{ strtoupper($karyawan->status) }}</div>
                <div class="front-year">{{ now()->year }}</div>
            </div>
        </div>

        {{-- BELAKANG --}}
        <div class="card-label">Sisi Belakang</div>
        <div class="id-card-back">
            <div class="back-top"></div>
            <div class="back-body">
                <div class="back-qr">
                    <img src="{{ $qrPng }}" alt="QR {{ $karyawan->nik }}">
                    <div style="text-align:center;font-size:8px;color:#a0aec0;margin-top:3px;">Scan untuk absensi</div>
                </div>
                <div class="back-detail">
                    <div class="back-title">Data Karyawan</div>
                    <div class="back-row">
                        <span class="back-key">Nama</span>
                        <span class="back-val">{{ $karyawan->nama }}</span>
                    </div>
                    <div class="back-row">
                        <span class="back-key">NIK</span>
                        <span class="back-val" style="font-family:monospace;">{{ $karyawan->nik }}</span>
                    </div>
                    <div class="back-row">
                        <span class="back-key">Jabatan</span>
                        <span class="back-val">{{ $karyawan->jabatan ?? '-' }}</span>
                    </div>
                    <div class="back-row">
                        <span class="back-key">Departemen</span>
                        <span class="back-val">{{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}</span>
                    </div>
                    <div class="back-row">
                        <span class="back-key">Status</span>
                        <span class="back-val" style="text-transform:capitalize;">{{ $karyawan->status }}</span>
                    </div>
                    @if($karyawan->tanggal_bergabung)
                    <div class="back-row">
                        <span class="back-key">Bergabung</span>
                        <span class="back-val">{{ \Carbon\Carbon::parse($karyawan->tanggal_bergabung)->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="back-footer">
                <span class="back-hint">Kartu ini adalah milik {{ $namaPerusahaan }}. Harap kembalikan jika ditemukan.</span>
            </div>
        </div>

    </div>
</body>
</html>
