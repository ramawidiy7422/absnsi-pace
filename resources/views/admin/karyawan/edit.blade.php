@extends('layouts.admin')
@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-user-edit"></i> Edit Data: {{ $karyawan->nama }}</h6>
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.karyawan.update', $karyawan) }}" enctype="multipart/form-data" id="formKaryawan">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>ID Karyawan *</label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $karyawan->nik) }}" required>
                    <small style="color:#718096;">Nomor ID internal karyawan — digunakan untuk absensi & QR code (bukan NIK KTP)</small>
                </div>
                <div class="form-group">
                    <label>Inisial <small style="color:#94a3b8;">(opsional)</small></label>
                    <input type="text" name="inisial" class="form-control" value="{{ old('inisial', $karyawan->inisial) }}" placeholder="Contoh: DAA" maxlength="10"
                        style="text-transform:uppercase;">
                    <small style="color:#718096;">Singkatan nama, maks 10 karakter</small>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', $karyawan->nama) }}" required>
                </div>
                <div class="form-group">
                    <label>Departemen *</label>
                    <select name="department_id" id="dept_select" class="form-select" required onchange="filterDivisi()">
                        <option value="">Pilih Departemen...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $karyawan->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Divisi <small style="color:#94a3b8;">(opsional)</small></label>
                    <select name="devision_id" id="divisi_select" class="form-select">
                        <option value="">— Pilih Divisi —</option>
                        @foreach($devisions as $dev)
                            <option value="{{ $dev->id }}"
                                    data-dept="{{ $dev->department_id }}"
                                    {{ old('devision_id', $karyawan->devision_id) == $dev->id ? 'selected' : '' }}>
                                {{ $dev->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jabatan *</label>
                    <select name="position_id" class="form-select" required>
                        <option value="">Pilih Jabatan...</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id', $karyawan->position_id) == $pos->id ? 'selected' : '' }}>
                                {{ $pos->name }}@if($pos->level) — {{ $pos->level }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="L" {{ $karyawan->jenis_kelamin=='L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $karyawan->jenis_kelamin=='P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>No. HP / WA</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $karyawan->no_hp) }}">
                </div>
                <div class="form-group">
                    <label>Email Pribadi</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $karyawan->email) }}" placeholder="email@pribadi.com">
                    <small style="color:#718096;">Email kontak karyawan</small>
                </div>
                <div class="form-group">
                    <label>Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir" class="form-select">
                        <option value="">Pilih...</option>
                        @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $p)
                            <option value="{{ $p }}" {{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir) == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Shift Kerja</label>
                    <select name="shift_id" class="form-select">
                        <option value="">Pilih Shift...</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ $karyawan->shift_id == $shift->id ? 'selected' : '' }}>
                                {{ $shift->nama_shift }} ({{ $shift->jam_masuk }} - {{ $shift->jam_keluar }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Karyawan *</label>
                    <select name="status" class="form-select" required>
                        @php $curStatus = old('status', $karyawan->status); @endphp
                        <option value="kontrak"  {{ $curStatus=='kontrak'  ? 'selected' : '' }}>Karyawan Kontrak</option>
                        <option value="tetap"    {{ $curStatus=='tetap'    ? 'selected' : '' }}>Karyawan Tetap</option>
                        <option value="magang"   {{ $curStatus=='magang'   ? 'selected' : '' }}>Magang</option>
                        <option value="training" {{ $curStatus=='training' ? 'selected' : '' }}>Training</option>
                        <option value="harian"   {{ $curStatus=='harian'   ? 'selected' : '' }}>Harian Lepas / Pocokan</option>
                        <option value="nonaktif" {{ $curStatus=='nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Hak Akses Absensi</label>
                    <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:14px 16px;display:flex;align-items:flex-start;gap:14px;">
                        <div style="padding-top:2px;">
                            <input type="hidden" name="bebas_absen" value="0">
                            <input type="checkbox" name="bebas_absen" id="bebas_absen" value="1"
                                {{ old('bebas_absen', $karyawan->bebas_absen) ? 'checked' : '' }}
                                style="width:18px;height:18px;cursor:pointer;accent-color:#d97706;">
                        </div>
                        <div>
                            <label for="bebas_absen" style="font-size:14px;font-weight:700;color:#92400e;cursor:pointer;text-transform:none;letter-spacing:0;">
                                Bebas Absen (Manager ke atas)
                            </label>
                            <div style="font-size:12px;color:#78350f;margin-top:3px;">
                                Jika dicentang: tidak dihitung terlambat, tidak kena alpha otomatis, dan bebas keluar jam berapa saja (pulang cepat tidak dicatat).
                                Karyawan dengan jabatan <strong>Direktur, Plant Manager, Manager, atau Asst. Manager</strong> otomatis bebas absen meskipun tidak dicentang.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $karyawan->birth_place) }}">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $karyawan->birth_date) }}">
                </div>
                <div class="form-group">
                    <label>Agama</label>
                    <select name="religion" class="form-select">
                        <option value="">Pilih...</option>
                        @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                            <option value="{{ $agama }}" {{ old('religion', $karyawan->religion) == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Pernikahan</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Pilih...</option>
                        @foreach(['Belum Menikah','Menikah','Cerai'] as $ms)
                            <option value="{{ $ms }}" {{ old('marital_status', $karyawan->marital_status) == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Tanggungan</label>
                    <input type="number" name="jumlah_tanggungan" class="form-control"
                           value="{{ old('jumlah_tanggungan', $karyawan->jumlah_tanggungan ?? 0) }}" min="0" max="20">
                </div>
            </div>

            <div class="form-group">
                <label>Golongan Darah</label>
                <select name="golongan_darah" class="form-select">
                    <option value="">-- Tidak Diketahui --</option>
                    @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gol)
                    <option value="{{ $gol }}" {{ old('golongan_darah', $karyawan->golongan_darah) == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $karyawan->alamat) }}</textarea>
            </div>

            {{-- Kontak Darurat --}}
            <div style="background:#fff5f5;border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid #fed7d7;">
                <div style="font-size:12px;font-weight:700;color:#c53030;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">
                    <i class="fas fa-phone-alt"></i> Kontak Darurat
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label>Nama Kontak</label>
                        <input type="text" name="kontak_darurat_nama" class="form-control" maxlength="100"
                               value="{{ old('kontak_darurat_nama', $karyawan->kontak_darurat_nama) }}" placeholder="Nama lengkap">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Hubungan</label>
                        <select name="kontak_darurat_hubungan" class="form-select">
                            <option value="">-- Pilih --</option>
                            @foreach(['Suami','Istri','Orang Tua','Anak','Saudara','Teman','Lainnya'] as $hub)
                            <option value="{{ $hub }}" {{ old('kontak_darurat_hubungan', $karyawan->kontak_darurat_hubungan) == $hub ? 'selected' : '' }}>{{ $hub }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>No. HP Kontak</label>
                        <input type="text" name="kontak_darurat_no_hp" class="form-control" maxlength="20"
                               value="{{ old('kontak_darurat_no_hp', $karyawan->kontak_darurat_no_hp) }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>
            </div>

            {{-- Rekening Bank --}}
            <div style="background:#f0fff4;border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid #9ae6b4;">
                <div style="font-size:12px;font-weight:700;color:#276749;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">
                    <i class="fas fa-university"></i> Rekening Bank (untuk transfer gaji)
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label>Nama Bank</label>
                        <select name="bank_nama" class="form-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach(['BCA','BRI','BNI','Mandiri','BSI','CIMB Niaga','Danamon','Permata','BTN','Maybank','OCBC NISP','Bukopin','Lainnya'] as $bank)
                            <option value="{{ $bank }}" {{ old('bank_nama', $karyawan->bank_nama) == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>No. Rekening</label>
                        <input type="text" name="bank_no_rekening" class="form-control" maxlength="30"
                               value="{{ old('bank_no_rekening', $karyawan->bank_no_rekening) }}" placeholder="Nomor rekening">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Atas Nama</label>
                        <input type="text" name="bank_atas_nama" class="form-control" maxlength="100"
                               value="{{ old('bank_atas_nama', $karyawan->bank_atas_nama) }}" placeholder="Nama pemilik rekening">
                    </div>
                </div>
            </div>

            {{-- ===== FOTO WAJAH SECTION ===== --}}
            <div style="margin-bottom:20px;">
                <label style="font-weight:600; margin-bottom:10px; display:block;">Foto Wajah (kosongkan jika tidak ingin mengubah)</label>

                @if($karyawan->foto_wajah)
                <div style="margin-bottom:12px; padding:12px; background:#f0fff4; border-radius:8px; border:1px solid #9ae6b4; display:flex; align-items:center; gap:12px;">
                    <img src="{{ asset('storage/' . $karyawan->foto_wajah) }}" style="width:64px; height:64px; border-radius:8px; object-fit:cover; border:2px solid #48bb78;" onerror="this.src=''">
                    <div>
                        <p style="margin:0; font-size:13px; color:#276749; font-weight:600;"><i class="fas fa-check-circle"></i> Foto wajah terdaftar</p>
                        <p style="margin:0; font-size:12px; color:#38a169;">Ambil foto baru untuk memperbarui wajah di sistem</p>
                    </div>
                </div>
                @endif

                {{-- Tab pilihan metode --}}
                <div style="display:flex; gap:8px; margin-bottom:16px;">
                    <button type="button" id="btn-tab-kamera" onclick="switchTab('kamera')"
                        style="padding:8px 18px; border-radius:8px; border:2px solid #667eea; background:#667eea; color:#fff; font-size:13px; cursor:pointer;">
                        <i class="fas fa-camera"></i> Ambil dari Kamera
                    </button>
                    <button type="button" id="btn-tab-upload" onclick="switchTab('upload')"
                        style="padding:8px 18px; border-radius:8px; border:2px solid #e2e8f0; background:#fff; color:#4a5568; font-size:13px; cursor:pointer;">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                    @if($karyawan->foto_wajah)
                    <button type="button" id="btn-tab-tetap" onclick="switchTab('tetap')"
                        style="padding:8px 18px; border-radius:8px; border:2px solid #e2e8f0; background:#fff; color:#4a5568; font-size:13px; cursor:pointer;">
                        <i class="fas fa-check"></i> Tetap Gunakan Foto Lama
                    </button>
                    @endif
                </div>

                {{-- PANEL KAMERA --}}
                <div id="panel-kamera" style="border:2px dashed #667eea; border-radius:12px; padding:20px; text-align:center;">
                    <div id="kamera-stream" style="display:none;">
                        <video id="video-cam" autoplay playsinline style="width:320px; height:240px; border-radius:10px; background:#000; object-fit:cover;"></video>
                        <br>
                        <button type="button" onclick="ambilFoto()"
                            style="margin-top:12px; padding:10px 28px; background:#48bb78; color:#fff; border:none; border-radius:8px; font-size:14px; cursor:pointer;">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                        <button type="button" onclick="stopKamera()"
                            style="margin-top:12px; margin-left:8px; padding:10px 18px; background:#e53e3e; color:#fff; border:none; border-radius:8px; font-size:14px; cursor:pointer;">
                            <i class="fas fa-times"></i> Tutup Kamera
                        </button>
                    </div>

                    <div id="kamera-preview" style="display:none;">
                        <canvas id="canvas-cam" style="display:none;"></canvas>
                        <img id="img-cam-preview" style="width:240px; height:240px; border-radius:10px; object-fit:cover; border:3px solid #48bb78;">
                        <br>
                        <button type="button" onclick="ulangiKamera()"
                            style="margin-top:12px; padding:8px 20px; background:#667eea; color:#fff; border:none; border-radius:8px; font-size:13px; cursor:pointer;">
                            <i class="fas fa-redo"></i> Ambil Ulang
                        </button>
                        <p style="color:#48bb78; margin-top:8px; font-size:13px;"><i class="fas fa-check-circle"></i> Foto berhasil diambil</p>
                    </div>

                    <div id="kamera-placeholder">
                        <i class="fas fa-camera" style="font-size:48px; color:#a0aec0; margin-bottom:12px; display:block;"></i>
                        <p style="color:#718096; font-size:14px; margin-bottom:12px;">Klik tombol di bawah untuk membuka kamera</p>
                        <button type="button" onclick="bukaKamera()"
                            style="padding:10px 24px; background:#667eea; color:#fff; border:none; border-radius:8px; font-size:14px; cursor:pointer;">
                            <i class="fas fa-video"></i> Buka Kamera
                        </button>
                    </div>
                </div>

                {{-- PANEL UPLOAD --}}
                <div id="panel-upload" style="border:2px dashed #e2e8f0; border-radius:12px; padding:20px; text-align:center; display:none;">
                    <label for="foto_input" style="cursor:pointer; display:block;">
                        <div id="upload-preview" style="display:none;">
                            <img id="preview-img" style="max-width:240px; max-height:240px; border-radius:10px; margin-bottom:10px; border:3px solid #48bb78;">
                        </div>
                        <div id="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt" style="font-size:48px; color:#a0aec0; margin-bottom:12px; display:block;"></i>
                            <p style="color:#718096; font-size:14px;">Klik untuk pilih foto dari komputer</p>
                            <small style="color:#a0aec0;">JPG/PNG, maks 2MB. Pastikan wajah terlihat jelas!</small>
                        </div>
                    </label>
                    <input type="file" id="foto_input" name="foto_wajah" accept="image/*" style="display:none;" onchange="previewUpload(this)">
                </div>

                {{-- PANEL TETAP (hanya saat ada foto lama) --}}
                @if($karyawan->foto_wajah)
                <div id="panel-tetap" style="border:2px dashed #9ae6b4; border-radius:12px; padding:20px; text-align:center; display:none;">
                    <i class="fas fa-check-circle" style="font-size:36px; color:#48bb78; margin-bottom:10px; display:block;"></i>
                    <p style="color:#276749; font-size:14px;">Foto wajah lama akan tetap digunakan, tidak ada perubahan.</p>
                </div>
                @endif

                {{-- Hidden input untuk foto webcam --}}
                <input type="hidden" name="foto_webcam" id="foto_webcam_input">
            </div>
            {{-- ===== END FOTO SECTION ===== --}}

            {{-- ===== DOKUMEN IDENTITAS / LEGAL ===== --}}
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:20px 22px;margin-top:20px;">
                <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0fff4;">
                    <i class="fas fa-id-card" style="color:#276749;margin-right:8px;"></i>Dokumen Identitas & BPJS
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;color:#4a5568;">NIK / KTP</label>
                        <input type="text" name="doc_nik" class="form-control" maxlength="20"
                               value="{{ old('doc_nik', $karyawan->legaldoc?->nik ?? '') }}"
                               placeholder="16 digit NIK KTP">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;color:#4a5568;">No. Kartu Keluarga (KK)</label>
                        <input type="text" name="doc_kk" class="form-control" maxlength="20"
                               value="{{ old('doc_kk', $karyawan->legaldoc?->kk ?? '') }}"
                               placeholder="16 digit No. KK">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;color:#4a5568;">NPWP</label>
                        <input type="text" name="doc_npwp" class="form-control" maxlength="30"
                               value="{{ old('doc_npwp', $karyawan->legaldoc?->npwp ?? '') }}"
                               placeholder="XX.XXX.XXX.X-XXX.XXX">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;color:#4a5568;">BPJS Ketenagakerjaan (TK)</label>
                        <input type="text" name="doc_bpjs_tk" class="form-control" maxlength="30"
                               value="{{ old('doc_bpjs_tk', $karyawan->legaldoc?->bpjs_tk ?? '') }}"
                               placeholder="No. BPJS TK">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:600;color:#4a5568;">BPJS Kesehatan</label>
                        <input type="text" name="doc_bpjs_kesehatan" class="form-control" maxlength="30"
                               value="{{ old('doc_bpjs_kesehatan', $karyawan->legaldoc?->bpjs_kesehatan ?? '') }}"
                               placeholder="No. BPJS Kesehatan">
                    </div>
                </div>
            </div>
            {{-- ===== END DOKUMEN IDENTITAS ===== --}}

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Karyawan</button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
let stream = null;
let activeTab = 'kamera';


// Jika ada foto lama, default ke "tetap"
@if($karyawan->foto_wajah)
document.addEventListener('DOMContentLoaded', () => switchTab('tetap'));
@endif

function switchTab(tab) {
    activeTab = tab;

    // Sembunyikan semua panel
    document.getElementById('panel-kamera').style.display = 'none';
    document.getElementById('panel-upload').style.display  = 'none';
    const panelTetap = document.getElementById('panel-tetap');
    if (panelTetap) panelTetap.style.display = 'none';

    // Tampilkan panel aktif
    if (tab === 'kamera') document.getElementById('panel-kamera').style.display = 'block';
    if (tab === 'upload')  document.getElementById('panel-upload').style.display  = 'block';
    if (tab === 'tetap' && panelTetap)  panelTetap.style.display  = 'block';

    // Update style tombol tab
    const styleAktif  = 'padding:8px 18px;border-radius:8px;border:2px solid #667eea;background:#667eea;color:#fff;font-size:13px;cursor:pointer;';
    const styleNormal = 'padding:8px 18px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;';

    document.getElementById('btn-tab-kamera').style.cssText = tab === 'kamera' ? styleAktif : styleNormal;
    document.getElementById('btn-tab-upload').style.cssText  = tab === 'upload'  ? styleAktif : styleNormal;
    const btnTetap = document.getElementById('btn-tab-tetap');
    if (btnTetap) btnTetap.style.cssText = tab === 'tetap' ? styleAktif : styleNormal;

    // Reset input dari tab non-aktif
    if (tab !== 'kamera') {
        document.getElementById('foto_webcam_input').value = '';
        if (stream) stopKamera();
        document.getElementById('kamera-preview').style.display = 'none';
        document.getElementById('kamera-placeholder').style.display = 'block';
        document.getElementById('kamera-stream').style.display = 'none';
    }
    if (tab !== 'upload') {
        document.getElementById('foto_input').value = '';
        document.getElementById('upload-preview').style.display = 'none';
        document.getElementById('upload-placeholder').style.display = 'block';
    }
}

function bukaKamera() {
    document.getElementById('kamera-placeholder').style.display = 'none';
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } })
        .then(s => {
            stream = s;
            const video = document.getElementById('video-cam');
            video.srcObject = s;
            document.getElementById('kamera-stream').style.display = 'block';
        })
        .catch(err => {
            alert('Tidak bisa mengakses kamera: ' + err.message + '\nCoba gunakan tab Upload File.');
            document.getElementById('kamera-placeholder').style.display = 'block';
        });
}

function ambilFoto() {
    const video  = document.getElementById('video-cam');
    const canvas = document.getElementById('canvas-cam');
    canvas.width  = video.videoWidth  || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('foto_webcam_input').value = dataUrl;
    document.getElementById('img-cam-preview').src = dataUrl;

    document.getElementById('kamera-stream').style.display = 'none';
    document.getElementById('kamera-preview').style.display = 'block';

    stopKamera();
}

function stopKamera() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    document.getElementById('kamera-stream').style.display = 'none';
}

function ulangiKamera() {
    document.getElementById('kamera-preview').style.display = 'none';
    document.getElementById('foto_webcam_input').value = '';
    bukaKamera();
}

function filterDivisi() {
    const deptId = document.getElementById('dept_select').value;
    const sel    = document.getElementById('divisi_select');
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return;
        opt.hidden = deptId && opt.dataset.dept !== deptId;
    });
    if (sel.value && sel.options[sel.selectedIndex].hidden) sel.value = '';
}
document.addEventListener('DOMContentLoaded', filterDivisi);

function previewUpload(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('upload-preview').style.display = 'block';
            document.getElementById('upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection

