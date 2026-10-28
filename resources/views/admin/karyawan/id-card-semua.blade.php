<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ID Card Semua Karyawan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #e2e8f0;
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
        .btn-print { background: #2b6cb0; color: #fff; }
        .btn-back  { background: #e2e8f0; color: #4a5568; }

        h2 {
            text-align: center;
            color: #2d3748;
            font-size: 16px;
            margin-bottom: 20px;
            padding-right: 160px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
        }

        .card-unit {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
            page-break-inside: avoid;
        }

        .id-card-front {
            width: 286px;
            height: 180px;
            background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 60%, #3182ce 100%);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            position: relative;
            overflow: hidden;
            color: white;
        }
        .id-card-front::before {
            content: '';
            position: absolute;
            width: 130px; height: 130px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            top: -40px; right: -30px;
        }

        .front-header {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px 7px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            position: relative;
            z-index: 1;
        }
        .front-logo {
            width: 24px; height: 24px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .front-logo img { width: 100%; height: 100%; object-fit: contain; }
        .front-company { font-size: 7.5px; font-weight: 700; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.3px; }
        .front-badge { margin-left: auto; background: rgba(255,255,255,0.2); font-size: 6.5px; font-weight: 700; padding: 2px 6px; border-radius: 8px; text-transform: uppercase; }

        .front-body {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            position: relative;
            z-index: 1;
        }
        .front-photo {
            width: 56px; height: 68px;
            border-radius: 7px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.4);
            overflow: hidden;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .front-photo img { width: 100%; height: 100%; object-fit: cover; }
        .front-photo .no-photo { font-size: 22px; font-weight: 800; opacity: 0.6; }

        .front-nama { font-size: 11.5px; font-weight: 800; line-height: 1.25; margin-bottom: 3px; }
        .front-jabatan { font-size: 8px; opacity: 0.85; margin-bottom: 4px; }
        .front-dept { background: rgba(255,255,255,0.2); font-size: 7px; font-weight: 700; padding: 2px 7px; border-radius: 8px; display: inline-block; margin-bottom: 5px; }
        .front-nik { font-size: 8px; opacity: 0.65; font-family: 'Courier New', monospace; }

        .front-footer {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.2);
            padding: 4px 12px;
            display: flex;
            justify-content: space-between;
            z-index: 1;
        }
        .front-status { font-size: 7px; font-weight: 700; text-transform: uppercase; }
        .front-year { font-size: 7px; opacity: 0.6; }

        @media print {
            body { background: white; padding: 5mm; }
            .page-actions { display: none; }
            h2 { padding-right: 0; font-size: 13px; margin-bottom: 10px; }
            .grid { gap: 8mm; }
            .id-card-front { box-shadow: none; border: 0.5px solid #e2e8f0; }
            .card-unit { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <a href="{{ url()->previous() }}" class="btn-back">&#8592; Kembali</a>
        <button class="btn-print" onclick="window.print()">&#128438; Cetak Semua ({{ $cards->count() }} kartu)</button>
    </div>

    <h2>ID Card Karyawan &mdash; {{ $namaPerusahaan }}</h2>

    <div class="grid">
        @foreach($cards as $item)
        @php $k = $item['karyawan']; @endphp
        <div class="card-unit">
            <div class="id-card-front">
                <div class="front-header">
                    <div class="front-logo">
                        @if($logoPerusahaan)
                            <img src="{{ asset('storage/' . $logoPerusahaan) }}" alt="Logo">
                        @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
                        @endif
                    </div>
                    <div class="front-company">{{ $namaPerusahaan }}</div>
                    <div class="front-badge">ID Card</div>
                </div>
                <div class="front-body">
                    <div class="front-photo">
                        @if($k->foto_wajah)
                            <img src="{{ asset('storage/' . $k->foto_wajah) }}" alt="{{ $k->nama }}">
                        @else
                            <span class="no-photo">{{ strtoupper(substr($k->nama, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <div class="front-nama">{{ $k->nama }}</div>
                        <div class="front-jabatan">{{ $k->jabatan ?? 'Karyawan' }}</div>
                        <div class="front-dept">{{ $k->department?->name ?? $k->departemen ?? '-' }}</div>
                        <div class="front-nik">NIK: {{ $k->nik }}</div>
                    </div>
                </div>
                <div class="front-footer">
                    <span class="front-status">{{ strtoupper($k->status) }}</span>
                    <span class="front-year">{{ now()->year }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
