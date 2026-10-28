<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QR Absensi - {{ $karyawan->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
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
        .btn-print { background: #4a5568; color: #fff; }
        .btn-back  { background: #e2e8f0; color: #4a5568; }

        /* Kartu QR */
        .qr-card {
            background: #fff;
            border-radius: 16px;
            width: 260px;
            padding: 24px 20px 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Strip warna atas */
        .qr-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .card-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #805ad5;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .qr-box {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            display: inline-block;
            margin-bottom: 14px;
        }
        .qr-box img { display: block; width: 180px; height: 180px; }

        .nama {
            font-size: 15px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .jabatan {
            font-size: 11px;
            color: #718096;
            margin-bottom: 3px;
        }
        .departemen {
            display: inline-block;
            background: #e9d8fd;
            color: #553c9a;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 4px;
        }

        .divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 12px 0;
        }

        .scan-hint {
            font-size: 10px;
            color: #a0aec0;
            line-height: 1.5;
        }

        /* Print styles */
        @media print {
            body { background: #fff; min-height: unset; }
            .page-actions { display: none; }
            .qr-card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <a href="{{ url()->previous() }}" class="btn-back">&#8592; Kembali</a>
        <button class="btn-print" onclick="window.print()">&#128438; Print Kartu</button>
    </div>

    <div class="qr-card">
        <div class="card-title">Kartu Absensi</div>

        <div class="qr-box">
            <img src="{{ $qrPng }}" alt="QR {{ $karyawan->nik }}">
        </div>

        <div class="nama">{{ $karyawan->nama }}</div>
        <div class="jabatan">{{ $karyawan->jabatan }}</div>
        <div class="departemen">{{ $karyawan->department?->name ?? $karyawan->departemen }}</div>

        <hr class="divider">

        <div class="scan-hint">
            Tunjukkan QR Code ini di mesin absensi<br>
            <strong>{{ $namaPerusahaan }}</strong>
        </div>
    </div>
</body>
</html>
