<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print QR Semua Karyawan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        .page-actions {
            position: fixed;
            top: 16px; right: 16px;
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

        .page-header {
            text-align: center;
            margin-bottom: 24px;
            padding-top: 10px;
        }
        .page-header h2 { font-size: 18px; color: #2d3748; }
        .page-header p  { font-size: 12px; color: #718096; margin-top: 4px; }

        /* Grid kartu */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Kartu individual */
        .qr-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px 14px 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .qr-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .card-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: #805ad5;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .qr-box {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .qr-box img { display: block; width: 148px; height: 148px; }

        .nama {
            font-size: 12px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .jabatan    { font-size: 10px; color: #718096; margin-bottom: 3px; }
        .departemen {
            display: inline-block;
            background: #e9d8fd;
            color: #553c9a;
            font-size: 9px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .divider { border: none; border-top: 1px dashed #e2e8f0; margin: 8px 0; }

        .scan-hint { font-size: 9px; color: #a0aec0; }

        /* Print */
        @media print {
            body { background: #fff; padding: 10px; }
            .page-actions { display: none; }
            .page-header { margin-bottom: 16px; }
            .cards-grid { gap: 10px; }
            .qr-card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <a href="{{ route('admin.karyawan.index') }}" class="btn-back">&#8592; Kembali</a>
        <button class="btn-print" onclick="window.print()">&#128438; Print Semua</button>
    </div>

    <div class="page-header">
        <h2>QR Code Absensi Karyawan</h2>
        <p>Total {{ $qrList->count() }} karyawan aktif &mdash; {{ $namaPerusahaan }}</p>
    </div>

    <div class="cards-grid">
        @foreach($qrList as $item)
        <div class="qr-card">
            <div class="card-title">Kartu Absensi</div>
            <div class="qr-box">
                <img src="{{ $item['qrPng'] }}" alt="QR {{ $item['karyawan']->nik }}">
            </div>
            <div class="nama">{{ $item['karyawan']->nama }}</div>
            <div class="jabatan">{{ $item['karyawan']->jabatan }}</div>
            <div class="departemen">{{ $item['karyawan']->department?->name ?? $item['karyawan']->departemen }}</div>
            <hr class="divider">
            <div class="scan-hint">Scan di mesin absensi</div>
        </div>
        @endforeach
    </div>
</body>
</html>
