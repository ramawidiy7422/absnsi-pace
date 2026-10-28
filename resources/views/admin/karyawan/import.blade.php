@extends('layouts.admin')
@section('title', 'Import Karyawan')
@section('page-title', 'Import Karyawan via CSV')

@section('content')

@if(session('importErrors') && count(session('importErrors')) > 0)
<div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
    <div style="font-weight:700;color:#744210;margin-bottom:8px;"><i class="fas fa-exclamation-triangle"></i> Baris Dilewati:</div>
    @foreach(session('importErrors') as $err)
    <div style="font-size:12px;color:#744210;padding:2px 0;">• {{ $err }}</div>
    @endforeach
</div>
@endif

@if(session('importWarnings') && count(session('importWarnings')) > 0)
<div style="background:#fff5f5;border:1px solid #feb2b2;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
    <div style="font-weight:700;color:#9b2c2c;margin-bottom:8px;"><i class="fas fa-link-slash"></i> Berhasil Ditambahkan, Namun Perlu Dicek Manual:</div>
    @foreach(session('importWarnings') as $warn)
    <div style="font-size:12px;color:#9b2c2c;padding:2px 0;">• {{ $warn }}</div>
    @endforeach
</div>
@endif

@if($errors->any())
<div class="alert alert-danger" style="border-radius:8px;margin-bottom:16px;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    {{-- Form Upload --}}
    <div class="card">
        <div class="card-header">
            <h6 style="margin:0;font-weight:700;"><i class="fas fa-file-upload" style="color:#4299e1;"></i> Upload File CSV</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.karyawan.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:30px;text-align:center;margin-bottom:16px;cursor:pointer;transition:border-color .2s;"
                     id="dropzone"
                     ondragover="event.preventDefault();this.style.borderColor='#4299e1'"
                     ondragleave="this.style.borderColor='#e2e8f0'"
                     ondrop="handleDrop(event)"
                     onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size:36px;color:#a0aec0;display:block;margin-bottom:10px;" id="dropIcon"></i>
                    <div style="font-weight:600;color:#4a5568;" id="dropText">Klik atau drag & drop file CSV di sini</div>
                    <div style="font-size:11px;color:#a0aec0;margin-top:4px;">Maksimal 5MB, format .csv</div>
                    <input type="file" id="fileInput" name="file" accept=".csv,.txt" style="display:none;" onchange="showFileName(this)">
                </div>

                <div style="background:#ebf8ff;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#2c5282;">
                    <i class="fas fa-info-circle" style="margin-right:6px;"></i>
                    <strong>Kolom wajib:</strong> nik, nama, departemen, jabatan, status, jenis_kelamin<br>
                    <strong>Status valid:</strong> kontrak, tetap, magang, training, outsourcing, nonaktif<br>
                    <strong>Jenis kelamin:</strong> L atau P
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <i class="fas fa-upload"></i> Import Sekarang
                    </button>
                    <a href="{{ route('admin.karyawan.import.template') }}" class="btn btn-secondary" style="white-space:nowrap;">
                        <i class="fas fa-download"></i> Template CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Panduan --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <h6 style="margin:0;font-weight:700;"><i class="fas fa-book" style="color:#805ad5;"></i> Panduan Import</h6>
            </div>
            <div class="card-body" style="font-size:13px;color:#4a5568;line-height:1.8;">
                <div style="margin-bottom:10px;"><strong style="color:#553c9a;">1. Download Template</strong><br>
                Klik tombol "Template CSV" untuk mendapatkan file contoh dengan format yang benar.</div>
                <div style="margin-bottom:10px;"><strong style="color:#553c9a;">2. Isi Data</strong><br>
                Buka file template di Excel atau Google Sheets. Isi data karyawan satu baris per karyawan. Hapus baris contoh.</div>
                <div style="margin-bottom:10px;"><strong style="color:#553c9a;">3. Simpan sebagai CSV</strong><br>
                Di Excel: File → Simpan Sebagai → CSV UTF-8<br>
                Di Google Sheets: File → Download → CSV</div>
                <div><strong style="color:#553c9a;">4. Upload & Import</strong><br>
                Upload file CSV. Sistem akan memproses dan menampilkan hasil.</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 style="margin:0;font-weight:700;"><i class="fas fa-table" style="color:#38a169;"></i> Daftar Kolom</h6>
            </div>
            <div class="card-body" style="padding:0;">
                @php
                $cols = [
                    ['nik','Wajib','Nomor Induk Karyawan (unik)'],
                    ['kode_karyawan','Opsional','Kode karyawan internal'],
                    ['nama','Wajib','Nama lengkap'],
                    ['jenis_kelamin','Wajib','L atau P'],
                    ['departemen','Wajib','Nama departemen'],
                    ['jabatan','Wajib','Nama jabatan'],
                    ['status','Wajib','kontrak / tetap / magang / training / outsourcing / nonaktif'],
                    ['email','Opsional','Alamat email'],
                    ['no_hp','Opsional','Nomor HP'],
                    ['alamat','Opsional','Alamat lengkap'],
                    ['pendidikan_terakhir','Opsional','SD/SMP/SMA-SMK/D1/D2/D3/S1/S2/S3'],
                    ['marital_status','Opsional','Belum Menikah / Menikah / Cerai'],
                    ['jumlah_tanggungan','Opsional','Angka (contoh: 2), default 0'],
                    ['birth_date','Opsional','Format: YYYY-MM-DD'],
                    ['birth_place','Opsional','Kota kelahiran'],
                    ['religion','Opsional','Agama'],
                ];
                @endphp
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                            <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;">Kolom</th>
                            <th style="padding:8px 8px;font-weight:700;color:#4a5568;">Status</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cols as [$col, $status, $ket])
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:7px 12px;font-family:monospace;color:#553c9a;font-weight:600;">{{ $col }}</td>
                            <td style="padding:7px 8px;text-align:center;">
                                <span style="background:{{ $status==='Wajib'?'#fed7e2':'#f0fff4' }};color:{{ $status==='Wajib'?'#c53030':'#276749' }};padding:1px 8px;border-radius:10px;font-size:10px;font-weight:700;">
                                    {{ $status }}
                                </span>
                            </td>
                            <td style="padding:7px 12px;color:#718096;">{{ $ket }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('dropText').textContent = '✓ ' + input.files[0].name;
        document.getElementById('dropIcon').style.color = '#38a169';
    }
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = '#e2e8f0';
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('fileInput').files = e.dataTransfer.files;
        document.getElementById('dropText').textContent = '✓ ' + file.name;
        document.getElementById('dropIcon').style.color = '#38a169';
    }
}
</script>
@endpush

@endsection
