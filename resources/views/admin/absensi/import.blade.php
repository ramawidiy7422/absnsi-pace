@extends('layouts.admin')
@section('title', 'Import Absensi CSV')
@section('page-title', 'Import Absensi CSV')

@section('content')


@if(session('importErrors') && count(session('importErrors')))
<div style="background:#fffbeb;border:1px solid #f6e05e;border-radius:10px;padding:16px;margin-bottom:16px;">
    <div style="font-weight:700;color:#744210;margin-bottom:10px;"><i class="fas fa-exclamation-triangle"></i> Detail baris yang dilewati ({{ count(session('importErrors')) }})</div>
    <div style="max-height:200px;overflow-y:auto;font-size:12px;color:#744210;line-height:1.8;">
        @foreach(session('importErrors') as $err)
        <div><i class="fas fa-times" style="color:#c05621;width:14px;"></i> {{ $err }}</div>
        @endforeach
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;align-items:start;">

    {{-- Form Import --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-file-upload" style="color:#3182ce;"></i> Import Absensi dari CSV</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.absensi.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label style="font-weight:700;">File CSV <span style="color:#c53030;">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    <small style="color:#718096;">Format: CSV, maksimal 5MB</small>
                </div>

                <div class="form-group">
                    <label style="font-weight:700;">Mode Import <span style="color:#c53030;">*</span></label>
                    <select name="mode" class="form-select" required>
                        <option value="skip">Lewati jika sudah ada (aman)</option>
                        <option value="update">Perbarui jika sudah ada (timpa)</option>
                    </select>
                    <small style="color:#718096;">Mode <em>Timpa</em> akan mengganti data yang sudah ada di database.</small>
                </div>

                <div style="background:#ebf8ff;border:1px solid #bee3f8;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;color:#2b6cb0;">
                    <i class="fas fa-info-circle"></i> <strong>Tips:</strong> Download template dulu, isi data, lalu upload.
                    Kolom <code>jam_keluar</code> dan <code>keterangan</code> boleh kosong.
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import Sekarang
                    </button>
                    <a href="{{ route('admin.absensi.import.template') }}" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Panduan --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <h6><i class="fas fa-table" style="color:#38a169;"></i> Format Kolom CSV</h6>
            </div>
            <div class="card-body" style="padding:0;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                            <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;">Kolom</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;">Keterangan</th>
                            <th style="padding:8px 8px;text-align:center;font-weight:700;color:#4a5568;">Wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $cols = [
                            ['nik_karyawan', 'ID Karyawan (bukan NIK KTP)', true],
                            ['tanggal', 'Format: YYYY-MM-DD', true],
                            ['jam_masuk', 'Format: HH:MM atau HH:MM:SS', true],
                            ['jam_keluar', 'Boleh kosong', false],
                            ['status', 'hadir / terlambat / izin / sakit / alpha / libur', true],
                            ['keterangan', 'Catatan tambahan (boleh kosong)', false],
                        ];
                        @endphp
                        @foreach($cols as [$col, $ket, $wajib])
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:8px 12px;font-family:monospace;font-size:11px;color:#2d3748;">{{ $col }}</td>
                            <td style="padding:8px 12px;color:#718096;">{{ $ket }}</td>
                            <td style="padding:8px;text-align:center;">
                                @if($wajib)
                                <span style="color:#e53e3e;font-weight:700;">Ya</span>
                                @else
                                <span style="color:#a0aec0;">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-lightbulb" style="color:#d69e2e;"></i> Contoh Isi CSV</h6>
            </div>
            <div class="card-body">
                <pre style="background:#1a202c;color:#68d391;border-radius:8px;padding:14px;font-size:11px;overflow-x:auto;margin:0;">nik_karyawan,tanggal,jam_masuk,jam_keluar,status,keterangan
1234567890,2026-04-01,08:05,17:00,hadir,
1234567891,2026-04-01,08:45,,terlambat,masuk telat
1234567892,2026-04-01,,,sakit,surat dokter terlampir</pre>
            </div>
        </div>
    </div>
</div>
@endsection
