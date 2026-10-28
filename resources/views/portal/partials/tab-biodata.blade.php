@php
    $statusLabel = [
        'kontrak'=>'Kontrak','tetap'=>'Tetap','magang'=>'Magang',
        'training'=>'Training','outsourcing'=>'Outsourcing','harian'=>'Harian Lepas',
        'nonaktif'=>'Nonaktif','resign'=>'Resign','phk'=>'PHK',
    ];
@endphp

{{-- ── Info Pribadi ── --}}
<div class="bio-section">
    <div class="bio-section-title">
        <i class="fas fa-user" style="color:#4299e1;"></i> Data Pribadi
    </div>
    <div class="bio-grid">
        <div class="bio-row">
            <span class="bio-label">Nama Lengkap</span>
            <span class="bio-value">{{ $karyawan->nama }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Jenis Kelamin</span>
            <span class="bio-value">{{ $karyawan->jenis_kelamin === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Tempat Lahir</span>
            <span class="bio-value {{ !$karyawan->birth_place ? 'empty' : '' }}">
                {{ $karyawan->birth_place ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Tanggal Lahir</span>
            <span class="bio-value {{ !$karyawan->birth_date ? 'empty' : '' }}">
                @if($karyawan->birth_date)
                    {{ $karyawan->birth_date->format('d M Y') }}
                    <span style="color:#a0aec0;font-size:11px;font-weight:400;">
                        ({{ $karyawan->birth_date->age }} tahun)
                    </span>
                @else —
                @endif
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Agama</span>
            <span class="bio-value {{ !$karyawan->religion ? 'empty' : '' }}">
                {{ $karyawan->religion ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Status Pernikahan</span>
            <span class="bio-value {{ !$karyawan->marital_status ? 'empty' : '' }}">
                {{ $karyawan->marital_status ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Jumlah Tanggungan</span>
            <span class="bio-value">
                {{ $karyawan->jumlah_tanggungan ?? 0 }} orang
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">No. HP / WA</span>
            <span class="bio-value {{ !$karyawan->no_hp ? 'empty' : '' }}">
                {{ $karyawan->no_hp ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Email Pribadi</span>
            <span class="bio-value {{ !$karyawan->email ? 'empty' : '' }}">
                @if($karyawan->email)
                    <a href="mailto:{{ $karyawan->email }}" style="color:#2b6cb0;text-decoration:none;">{{ $karyawan->email }}</a>
                @else —
                @endif
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Pendidikan Terakhir</span>
            <span class="bio-value {{ !$karyawan->pendidikan_terakhir ? 'empty' : '' }}">
                {{ $karyawan->pendidikan_terakhir ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Golongan Darah</span>
            <span class="bio-value {{ !$karyawan->golongan_darah ? 'empty' : '' }}">
                {{ $karyawan->golongan_darah ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Alamat</span>
            <span class="bio-value {{ !$karyawan->alamat ? 'empty' : '' }}" style="line-height:1.5;">
                {{ $karyawan->alamat ?? '—' }}
            </span>
        </div>
    </div>
</div>

{{-- ── Kontak Darurat ── --}}
@if($karyawan->kontak_darurat_nama || $karyawan->kontak_darurat_no_hp)
<div class="bio-section" style="border-left:3px solid #fc8181;">
    <div class="bio-section-title">
        <i class="fas fa-phone-alt" style="color:#e53e3e;"></i> Kontak Darurat
    </div>
    <div class="bio-grid">
        <div class="bio-row">
            <span class="bio-label">Nama</span>
            <span class="bio-value">{{ $karyawan->kontak_darurat_nama ?? '—' }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Hubungan</span>
            <span class="bio-value">{{ $karyawan->kontak_darurat_hubungan ?? '—' }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">No. HP</span>
            <span class="bio-value">
                @if($karyawan->kontak_darurat_no_hp)
                    <a href="tel:{{ $karyawan->kontak_darurat_no_hp }}" style="color:#2b6cb0;text-decoration:none;">{{ $karyawan->kontak_darurat_no_hp }}</a>
                @else —
                @endif
            </span>
        </div>
    </div>
</div>
@endif

{{-- ── Rekening Bank ── --}}
@if($karyawan->bank_no_rekening)
<div class="bio-section" style="border-left:3px solid #68d391;">
    <div class="bio-section-title">
        <i class="fas fa-university" style="color:#276749;"></i> Rekening Bank
    </div>
    <div class="bio-grid">
        <div class="bio-row">
            <span class="bio-label">Bank</span>
            <span class="bio-value">{{ $karyawan->bank_nama ?? '—' }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">No. Rekening</span>
            <span class="bio-value" style="font-family:monospace;font-weight:700;">{{ $karyawan->bank_no_rekening }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Atas Nama</span>
            <span class="bio-value">{{ $karyawan->bank_atas_nama ?? '—' }}</span>
        </div>
    </div>
</div>
@endif

{{-- ── Info Pekerjaan ── --}}
<div class="bio-section">
    <div class="bio-section-title">
        <i class="fas fa-briefcase" style="color:#48bb78;"></i> Data Pekerjaan
    </div>
    <div class="bio-grid">
        <div class="bio-row">
            <span class="bio-label">ID Karyawan</span>
            <span class="bio-value" style="color:#2b6cb0;font-weight:700;">
                {{ $karyawan->nik }}
            </span>
        </div>
        @if($karyawan->inisial)
        <div class="bio-row">
            <span class="bio-label">Inisial</span>
            <span class="bio-value" style="font-weight:700;letter-spacing:1px;">
                {{ $karyawan->inisial }}
            </span>
        </div>
        @endif
        <div class="bio-row">
            <span class="bio-label">Status Karyawan</span>
            <span class="bio-value">
                <span class="badge badge-{{ $karyawan->status }}">{{ $statusLabel[$karyawan->status] ?? ucfirst($karyawan->status) }}</span>
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Departemen</span>
            <span class="bio-value">{{ $karyawan->department?->name ?? $karyawan->departemen ?? '—' }}</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Divisi</span>
            <span class="bio-value {{ !$karyawan->devision ? 'empty' : '' }}">
                {{ $karyawan->devision?->name ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Jabatan</span>
            <span class="bio-value">
                {{ $karyawan->position?->name ?? $karyawan->jabatan ?? '—' }}
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Shift Default</span>
            <span class="bio-value {{ !$karyawan->shift ? 'empty' : '' }}">
                {{ $karyawan->shift?->nama_shift ?? '—' }}
                @if($karyawan->shift)
                    <span style="color:#a0aec0;font-size:11px;">
                        ({{ substr($karyawan->shift->jam_masuk,0,5) }}–{{ substr($karyawan->shift->jam_keluar,0,5) }})
                    </span>
                @endif
            </span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Tanggal Masuk</span>
            <span class="bio-value {{ !$karyawan->tanggal_masuk ? 'empty' : '' }}">
                {{ $karyawan->tanggal_masuk?->translatedFormat('d F Y') ?? '—' }}
            </span>
        </div>
        @if($karyawan->status === 'kontrak' && $karyawan->tanggal_kontrak_berakhir)
        <div class="bio-row">
            <span class="bio-label">Kontrak Berakhir</span>
            <span class="bio-value">
                {{ $karyawan->tanggal_kontrak_berakhir->translatedFormat('d F Y') }}
            </span>
        </div>
        @endif
    </div>
</div>

{{-- ── Dokumen Identitas ── --}}
<div class="bio-section">
    <div class="bio-section-title">
        <i class="fas fa-shield-alt" style="color:#e53e3e;"></i> Dokumen Identitas
        <span style="font-size:10px;font-weight:400;color:#fc8181;margin-left:4px;">
            <i class="fas fa-lock"></i> Rahasia
        </span>
    </div>
    @php $legaldoc = $karyawan->legaldoc; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
        <div class="doc-card">
            <div class="doc-icon" style="background:#ebf8ff;color:#2b6cb0;">
                <i class="fas fa-id-card"></i>
            </div>
            <div>
                <div class="doc-label">NIK KTP</div>
                <div class="doc-value {{ !($legaldoc->nik ?? null) ? 'empty' : '' }}">
                    {{ $legaldoc->nik ?? '—' }}
                </div>
            </div>
        </div>
        <div class="doc-card">
            <div class="doc-icon" style="background:#fffbeb;color:#c05621;">
                <i class="fas fa-home"></i>
            </div>
            <div>
                <div class="doc-label">No. KK</div>
                <div class="doc-value {{ !($legaldoc->kk ?? null) ? 'empty' : '' }}">
                    {{ $legaldoc->kk ?? '—' }}
                </div>
            </div>
        </div>
        <div class="doc-card">
            <div class="doc-icon" style="background:#f3e8ff;color:#6b46c1;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div>
                <div class="doc-label">NPWP</div>
                <div class="doc-value {{ !($legaldoc->npwp ?? null) ? 'empty' : '' }}">
                    {{ $legaldoc->npwp ?? '—' }}
                </div>
            </div>
        </div>
        <div class="doc-card">
            <div class="doc-icon" style="background:#ebf8ff;color:#2c7a7b;">
                <i class="fas fa-hard-hat"></i>
            </div>
            <div>
                <div class="doc-label">BPJS Ketenagakerjaan</div>
                <div class="doc-value {{ !($legaldoc->bpjs_tk ?? null) ? 'empty' : '' }}">
                    {{ $legaldoc->bpjs_tk ?? '—' }}
                </div>
            </div>
        </div>
        <div class="doc-card">
            <div class="doc-icon" style="background:#fff5f5;color:#c53030;">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div>
                <div class="doc-label">BPJS Kesehatan</div>
                <div class="doc-value {{ !($legaldoc->bpjs_kesehatan ?? null) ? 'empty' : '' }}">
                    {{ $legaldoc->bpjs_kesehatan ?? '—' }}
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:10px;font-size:11px;color:#a0aec0;">
        <i class="fas fa-info-circle"></i> Untuk mengubah data dokumen legal, hubungi HRD.
    </div>
</div>

{{-- ── Ringkasan ── --}}
<div class="bio-section">
    <div class="bio-section-title">
        <i class="fas fa-chart-pie" style="color:#805ad5;"></i> Ringkasan
    </div>
    <div class="bio-grid">
        <div class="bio-row">
            <span class="bio-label">Sisa Cuti</span>
            <span class="bio-value">{{ $saldoCuti->sisa ?? 0 }} hari</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Hadir Tahun Ini</span>
            <span class="bio-value">{{ $totalAbsensi }} hari</span>
        </div>
        <div class="bio-row">
            <span class="bio-label">Surat Peringatan Aktif</span>
            <span class="bio-value" style="color:{{ $spAktif > 0 ? '#c53030' : '#2d3748' }};">{{ $spAktif }}</span>
        </div>
    </div>
</div>

{{-- Form Edit Data Kontak --}}
<div class="card" id="form-edit-biodata" style="margin-top:8px;">
    <div class="card-header" style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-user-edit"></i> Edit Data Kontak &amp; Rekening</h6>
        <span style="font-size:11px;opacity:.8;">Data yang dapat diperbarui mandiri oleh karyawan</span>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#276749;font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        <form method="POST" action="{{ route('portal.profil.update') }}" enctype="multipart/form-data">
            @csrf
            {{-- Upload Foto --}}
            <div style="background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:16px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:16px;">
                <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;border:3px solid #e2e8f0;flex-shrink:0;">
                    @if($karyawan->foto_wajah)
                        <img src="{{ asset('storage/'.$karyawan->foto_wajah) }}" id="previewFotoLengkap" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div id="previewFotoLengkap" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:26px;font-weight:800;">
                            {{ strtoupper(substr($karyawan->nama,0,1)) }}
                        </div>
                    @endif
                </div>
                <div style="flex:1;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Foto Profil</label>
                    <input type="file" name="foto_wajah" accept="image/jpg,image/jpeg,image/png" style="font-size:12px;" onchange="previewImageLengkap(this)">
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
    </div>
</div>

<script>
function previewImageLengkap(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const el = document.getElementById('previewFotoLengkap');
            if (el.tagName === 'IMG') {
                el.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id = 'previewFotoLengkap';
                img.src = e.target.result;
                img.style = 'width:100%;height:100%;object-fit:cover;';
                el.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
