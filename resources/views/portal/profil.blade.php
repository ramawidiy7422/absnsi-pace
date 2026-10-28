@extends('layouts.admin')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
@php
    $statusLabel = [
        'kontrak'=>'Kontrak','tetap'=>'Tetap','magang'=>'Magang',
        'training'=>'Training','outsourcing'=>'Outsourcing','harian'=>'Harian Lepas',
        'nonaktif'=>'Nonaktif','resign'=>'Resign','phk'=>'PHK',
    ];
    $jenisKelaminLabel = ['L'=>'Laki-laki','P'=>'Perempuan'];
@endphp

<div style="max-width:860px;margin:0 auto;">

{{-- Header kartu profil --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:16px;padding:28px 28px 80px;position:relative;margin-bottom:70px;">
    <div style="color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:0.5px;">PROFIL KARYAWAN</div>
    <div style="color:white;font-size:22px;font-weight:800;margin-top:6px;">{{ $karyawan->nama }}</div>
    <div style="color:rgba(255,255,255,0.8);font-size:13px;margin-top:4px;">
        {{ $karyawan->position?->name ?? $karyawan->jabatan ?? '-' }} &bull;
        {{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}
    </div>

    {{-- Avatar --}}
    <div style="position:absolute;bottom:-50px;left:28px;width:100px;height:100px;border-radius:50%;border:4px solid white;overflow:hidden;background:#e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,0.15);">
        @if($karyawan->foto_wajah)
            <img src="{{ asset('storage/'.$karyawan->foto_wajah) }}" style="width:100%;height:100%;object-fit:cover;">
        @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:32px;font-weight:800;">
                {{ strtoupper(substr($karyawan->nama,0,1)) }}
            </div>
        @endif
    </div>

    {{-- Badge status --}}
    <div style="position:absolute;bottom:-20px;left:148px;">
        <span style="background:white;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;color:#2b6cb0;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
            {{ $statusLabel[$karyawan->status] ?? ucfirst($karyawan->status) }}
        </span>
    </div>
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
    <div style="background:white;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#2b6cb0;">{{ $saldoCuti->sisa ?? 0 }}</div>
        <div style="font-size:12px;color:#718096;margin-top:2px;">Sisa Cuti</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#276749;">{{ $totalAbsensi }}</div>
        <div style="font-size:12px;color:#718096;margin-top:2px;">Hadir Tahun Ini</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:{{ $spAktif > 0 ? '#c53030' : '#718096' }};">{{ $spAktif }}</div>
        <div style="font-size:12px;color:#718096;margin-top:2px;">SP Aktif</div>
    </div>
</div>

{{-- Data biodata --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;font-weight:700;font-size:14px;color:#2d3748;">
        <i class="fas fa-id-card" style="color:#4299e1;margin-right:8px;"></i>Data Kepegawaian
    </div>
    <div style="padding:4px 0;">
        @php
        $rows = [
            ['NIK / ID', $karyawan->nik ?? '-'],
            ['Jabatan', $karyawan->position?->name ?? $karyawan->jabatan ?? '-'],
            ['Departemen', $karyawan->department?->name ?? $karyawan->departemen ?? '-'],
            ['Area / Divisi', $karyawan->devision?->name ?? '-'],
            ['Shift Kerja', $karyawan->shift ? $karyawan->shift->nama_shift . ' (' . substr($karyawan->shift->jam_masuk,0,5) . ' – ' . substr($karyawan->shift->jam_keluar,0,5) . ')' : '-'],
            ['Status Karyawan', $statusLabel[$karyawan->status] ?? ucfirst($karyawan->status)],
            ['Tanggal Masuk', $karyawan->tanggal_masuk ? $karyawan->tanggal_masuk->translatedFormat('d F Y') : '-'],
            ['Masa Kerja', $karyawan->tanggal_masuk ? $karyawan->tanggal_masuk->diff(now())->format('%y tahun %m bulan') : '-'],
        ];
        if ($karyawan->status === 'kontrak' && $karyawan->tanggal_kontrak_berakhir)
            $rows[] = ['Kontrak Berakhir', $karyawan->tanggal_kontrak_berakhir->translatedFormat('d F Y')];
        @endphp
        @foreach($rows as [$label,$val])
        <div style="display:flex;padding:10px 20px;border-bottom:1px solid #f7fafc;">
            <div style="width:160px;font-size:12.5px;color:#718096;flex-shrink:0;">{{ $label }}</div>
            <div style="font-size:13px;color:#2d3748;font-weight:500;">{{ $val }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Data pribadi --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;font-weight:700;font-size:14px;color:#2d3748;">
        <i class="fas fa-user" style="color:#805ad5;margin-right:8px;"></i>Data Pribadi
    </div>
    <div style="padding:4px 0;">
        @php
        $pribadi = [
            ['Jenis Kelamin', $jenisKelaminLabel[$karyawan->jenis_kelamin] ?? $karyawan->jenis_kelamin ?? '-'],
            ['Tempat, Tanggal Lahir', ($karyawan->birth_place ? $karyawan->birth_place . ', ' : '') . ($karyawan->birth_date ? $karyawan->birth_date->translatedFormat('d F Y') . ' (' . $karyawan->birth_date->age . ' tahun)' : '-')],
            ['Agama', $karyawan->religion ?? '-'],
            ['Status Pernikahan', $karyawan->marital_status ?? '-'],
            ['Golongan Darah', $karyawan->golongan_darah ?? '-'],
            ['Jumlah Tanggungan', $karyawan->jumlah_tanggungan ?? '-'],
            ['Pendidikan Terakhir', $karyawan->pendidikan_terakhir ?? '-'],
            ['No. HP', $karyawan->no_hp ?? '-'],
            ['Alamat', $karyawan->alamat ?? '-'],
        ];
        @endphp
        @foreach($pribadi as [$label,$val])
        <div style="display:flex;padding:10px 20px;border-bottom:1px solid #f7fafc;">
            <div style="width:160px;font-size:12.5px;color:#718096;flex-shrink:0;">{{ $label }}</div>
            <div style="font-size:13px;color:#2d3748;font-weight:500;">{{ $val }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Dokumen Legal --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;font-weight:700;font-size:14px;color:#2d3748;">
        <i class="fas fa-id-badge" style="color:#276749;margin-right:8px;"></i>Dokumen Legal
    </div>
    <div style="padding:4px 0;">
        @php
        $legal = [
            ['NIK KTP', $karyawan->legaldoc?->nik ?? '-'],
            ['No. KK', $karyawan->legaldoc?->kk ?? '-'],
            ['NPWP', $karyawan->legaldoc?->npwp ?? '-'],
            ['BPJS Tenaga Kerja', $karyawan->legaldoc?->bpjs_tk ?? '-'],
            ['BPJS Kesehatan', $karyawan->legaldoc?->bpjs_kesehatan ?? '-'],
        ];
        @endphp
        @foreach($legal as [$label,$val])
        <div style="display:flex;padding:10px 20px;border-bottom:1px solid #f7fafc;">
            <div style="width:160px;font-size:12.5px;color:#718096;flex-shrink:0;">{{ $label }}</div>
            <div style="font-size:13px;color:#2d3748;font-weight:500;">{{ $val }}</div>
        </div>
        @endforeach
        <div style="padding:10px 20px;font-size:11px;color:#a0aec0;">
            <i class="fas fa-info-circle"></i> Untuk mengubah data dokumen legal, hubungi HRD.
        </div>
    </div>
</div>

{{-- Riwayat Pendidikan --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;font-weight:700;font-size:14px;color:#2d3748;">
        <i class="fas fa-graduation-cap" style="color:#805ad5;margin-right:8px;"></i>Riwayat Pendidikan
    </div>
    <div style="padding:4px 0;">
        @forelse($karyawan->educations as $edu)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 20px;border-bottom:1px solid #f7fafc;gap:10px;flex-wrap:wrap;">
            <div>
                <span style="font-size:13px;font-weight:700;color:#2d3748;">{{ $edu->level }}</span>
                @if($edu->major)<span style="font-size:12.5px;color:#718096;"> — {{ $edu->major }}</span>@endif
                @if($edu->institution)<div style="font-size:12px;color:#a0aec0;margin-top:2px;">{{ $edu->institution }}</div>@endif
            </div>
            @if($edu->year)
            <span style="font-size:12px;color:#4a5568;font-weight:600;flex-shrink:0;">Lulus {{ $edu->year }}</span>
            @endif
        </div>
        @empty
        <div style="padding:20px;text-align:center;color:#a0aec0;font-size:13px;">Belum ada data riwayat pendidikan.</div>
        @endforelse
    </div>
</div>

{{-- Link aksi --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;">
    <a href="{{ route('portal.ganti-password') }}" style="padding:10px 18px;background:#2b6cb0;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
        <i class="fas fa-key"></i> Ganti Password
    </a>
    @if($spAktif > 0)
    <a href="{{ route('portal.surat-peringatan') }}" style="padding:10px 18px;background:#fed7d7;color:#c53030;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
        <i class="fas fa-exclamation-triangle"></i> Lihat Surat Peringatan ({{ $spAktif }} aktif)
    </a>
    @endif
    <a href="{{ route('portal.slip-pdf', ['bulan'=>now()->month,'tahun'=>now()->year]) }}" target="_blank" style="padding:10px 18px;background:#f0fff4;color:#276749;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;border:1px solid #9ae6b4;">
        <i class="fas fa-file-pdf"></i> Slip Gaji Bulan Ini
    </a>
</div>

{{-- Form Edit Data Kontak --}}
@if(session('success'))
<div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#276749;font-weight:600;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-user-edit"></i> Edit Data Kontak &amp; Rekening</h6>
        <span style="font-size:11px;opacity:.8;">Data yang dapat diperbarui mandiri oleh karyawan</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.profil.update') }}" enctype="multipart/form-data">
            @csrf
            {{-- Upload Foto --}}
            <div style="background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:16px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:16px;">
                <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;border:3px solid #e2e8f0;flex-shrink:0;">
                    @if($karyawan->foto_wajah)
                        <img src="{{ asset('storage/'.$karyawan->foto_wajah) }}" id="previewFoto" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div id="previewFoto" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:26px;font-weight:800;">
                            {{ strtoupper(substr($karyawan->nama,0,1)) }}
                        </div>
                    @endif
                </div>
                <div style="flex:1;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Foto Profil</label>
                    <input type="file" name="foto_wajah" id="inputFoto" accept="image/jpg,image/jpeg,image/png" style="font-size:12px;" onchange="previewImage(this)">
                    @error('foto_wajah') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                    <div style="font-size:11px;color:#a0aec0;margin-top:3px;">JPG/PNG maks. 2MB. Tampil di kartu absensi & ID card.</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:16px;">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $karyawan->no_hp) }}" placeholder="08xx-xxxx-xxxx" maxlength="20">
                    @error('no_hp') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Email Pribadi</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $karyawan->email) }}" placeholder="email@contoh.com" maxlength="100">
                    @error('email') <div style="font-size:11px;color:#e53e3e;margin-top:3px;">{{ $message }}</div> @enderror
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="2" maxlength="300">{{ old('alamat', $karyawan->alamat) }}</textarea>
                </div>
            </div>

            <div style="background:#fff5f5;border-radius:8px;padding:14px;margin-bottom:14px;border:1px solid #fed7d7;">
                <div style="font-size:11px;font-weight:700;color:#c53030;margin-bottom:10px;text-transform:uppercase;"><i class="fas fa-phone-alt"></i> Kontak Darurat</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Nama</label>
                        <input type="text" name="kontak_darurat_nama" class="form-control" value="{{ old('kontak_darurat_nama', $karyawan->kontak_darurat_nama) }}" maxlength="100">
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Hubungan</label>
                        <select name="kontak_darurat_hubungan" class="form-select">
                            <option value="">-- Pilih --</option>
                            @foreach(['Suami','Istri','Orang Tua','Anak','Saudara','Teman','Lainnya'] as $hub)
                            <option value="{{ $hub }}" {{ old('kontak_darurat_hubungan', $karyawan->kontak_darurat_hubungan) == $hub ? 'selected' : '' }}>{{ $hub }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">No. HP</label>
                        <input type="text" name="kontak_darurat_no_hp" class="form-control" value="{{ old('kontak_darurat_no_hp', $karyawan->kontak_darurat_no_hp) }}" maxlength="20">
                    </div>
                </div>
            </div>

            <div style="background:#f0fff4;border-radius:8px;padding:14px;margin-bottom:14px;border:1px solid #9ae6b4;">
                <div style="font-size:11px;font-weight:700;color:#276749;margin-bottom:10px;text-transform:uppercase;"><i class="fas fa-university"></i> Rekening Bank</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Nama Bank</label>
                        <select name="bank_nama" class="form-select">
                            <option value="">-- Pilih --</option>
                            @foreach(['BCA','BRI','BNI','Mandiri','BSI','CIMB Niaga','Danamon','Permata','BTN','Maybank','OCBC NISP','Bukopin','Lainnya'] as $bank)
                            <option value="{{ $bank }}" {{ old('bank_nama', $karyawan->bank_nama) == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">No. Rekening</label>
                        <input type="text" name="bank_no_rekening" class="form-control" value="{{ old('bank_no_rekening', $karyawan->bank_no_rekening) }}" maxlength="30">
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Atas Nama</label>
                        <input type="text" name="bank_atas_nama" class="form-control" value="{{ old('bank_atas_nama', $karyawan->bank_atas_nama) }}" maxlength="100">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="font-weight:700;">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <small style="color:#a0aec0;margin-left:12px;">Perubahan data penting seperti nama, NIK, dan jabatan hanya bisa dilakukan oleh HRD.</small>
        </form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const el = document.getElementById('previewFoto');
            if (el.tagName === 'IMG') {
                el.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id = 'previewFoto';
                img.src = e.target.result;
                img.style = 'width:100%;height:100%;object-fit:cover;';
                el.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
    </div>
</div>

</div>
@endsection
