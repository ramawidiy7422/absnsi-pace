@extends('layouts.admin')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')

@section('content')
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-user-plus"></i> Form Tambah Karyawan</h6>
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

        <form method="POST" action="{{ route('admin.karyawan.store') }}" enctype="multipart/form-data" id="formKaryawan">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk_input" class="form-control"
                        value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required onchange="previewIdKaryawan()">
                    <small style="color:#718096;">
                        ID Karyawan akan di-generate otomatis:
                        <strong id="preview-id-karyawan" style="color:#2b6cb0;">{{ date('Ym') }}??</strong>
                    </small>
                </div>
                <div class="form-group">
                    <label>Inisial <small style="color:#94a3b8;">(opsional)</small></label>
                    <input type="text" name="inisial" class="form-control" value="{{ old('inisial') }}" placeholder="Contoh: DAA" maxlength="10"
                        style="text-transform:uppercase;">
                    <small style="color:#718096;">Singkatan nama, maks 10 karakter</small>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" id="nama_input" class="form-control" value="{{ old('nama') }}" required
                           oninput="autoEmail()">
                </div>
                <div class="form-group">
                    <label>Departemen *</label>
                    <select name="department_id" id="dept_select" class="form-select" required onchange="filterDivisi()">
                        <option value="">Pilih Departemen...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @if($departments->isEmpty())
                        <small style="color:#e53e3e;">Belum ada departemen. <a href="{{ route('admin.department.index') }}">Tambah dulu</a></small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Divisi <small style="color:#94a3b8;">(opsional)</small></label>
                    <select name="devision_id" id="divisi_select" class="form-select">
                        <option value="">— Pilih Divisi —</option>
                        @foreach($devisions as $dev)
                            <option value="{{ $dev->id }}"
                                    data-dept="{{ $dev->department_id }}"
                                    {{ old('devision_id') == $dev->id ? 'selected' : '' }}>
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
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->name }}@if($pos->level) — {{ $pos->level }}@endif
                            </option>
                        @endforeach
                    </select>
                    @if($positions->isEmpty())
                        <small style="color:#e53e3e;">Belum ada jabatan. <a href="{{ route('admin.position.index') }}">Tambah dulu</a></small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">Pilih...</option>
                        <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>No. HP / WA</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Email Pribadi</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@pribadi.com">
                    <small style="color:#718096;">Email kontak karyawan (beda dengan email login)</small>
                </div>
                <div class="form-group">
                    <label>Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir" class="form-select">
                        <option value="">Pilih...</option>
                        @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $p)
                            <option value="{{ $p }}" {{ old('pendidikan_terakhir') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Karyawan *</label>
                    <select name="status" class="form-select" required>
                        <option value="kontrak"  {{ old('status','kontrak')=='kontrak'  ? 'selected' : '' }}>Karyawan Kontrak</option>
                        <option value="tetap"    {{ old('status')=='tetap'    ? 'selected' : '' }}>Karyawan Tetap</option>
                        <option value="magang"   {{ old('status')=='magang'   ? 'selected' : '' }}>Magang</option>
                        <option value="training" {{ old('status')=='training' ? 'selected' : '' }}>Training</option>
                        <option value="harian"   {{ old('status')=='harian'   ? 'selected' : '' }}>Harian Lepas / Pocokan</option>
                        <option value="nonaktif" {{ old('status')=='nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Shift Kerja</label>
                    <select name="shift_id" class="form-select">
                        <option value="">Pilih Shift...</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->nama_shift }} ({{ $shift->jam_masuk }} - {{ $shift->jam_keluar }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place') }}" placeholder="Contoh: Jakarta">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                </div>
                <div class="form-group">
                    <label>Agama</label>
                    <select name="religion" class="form-select">
                        <option value="">Pilih...</option>
                        @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                            <option value="{{ $agama }}" {{ old('religion') == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Pernikahan</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Pilih...</option>
                        <option value="Belum Menikah" {{ old('marital_status') == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('marital_status') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Cerai" {{ old('marital_status') == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Tanggungan</label>
                    <input type="number" name="jumlah_tanggungan" class="form-control"
                           value="{{ old('jumlah_tanggungan', 0) }}" min="0" max="20" placeholder="0">
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
            </div>

            {{-- ===== FOTO WAJAH SECTION ===== --}}
            <div style="margin-bottom:20px;">
                <label style="font-weight:600; margin-bottom:4px; display:block;">Foto Wajah
                    <span style="font-weight:400; font-size:12px; color:#718096;">(opsional — bisa diisi nanti dari halaman detail karyawan)</span>
                </label>
                <div style="background:#fffbeb; border:1px solid #f6e05e; border-radius:8px; padding:8px 12px; margin-bottom:10px; font-size:12px; color:#92400e; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-info-circle"></i>
                    Foto wajah diperlukan untuk absensi <strong>Face Recognition</strong>. Jika belum ada, data karyawan tetap bisa disimpan dan foto dapat ditambahkan kapan saja.
                </div>

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

                {{-- Hidden input untuk foto webcam --}}
                <input type="hidden" name="foto_webcam" id="foto_webcam_input">
            </div>
            {{-- ===== END FOTO SECTION ===== --}}

            {{-- ===== BUAT AKUN LOGIN ===== --}}
            <div style="margin:24px 0;border:2px solid #bee3f8;border-radius:12px;overflow:hidden;">
                {{-- Header section --}}
                <div style="background:#ebf8ff;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-user-shield" style="color:#2b6cb0;font-size:18px;"></i>
                        <div>
                            <div style="font-weight:700;color:#2b6cb0;font-size:14px;">Akun Login Karyawan</div>
                            <div style="font-size:12px;color:#718096;">Untuk akses pengajuan cuti & izin secara online</div>
                        </div>
                    </div>
                    {{-- Toggle: Ya / Tidak --}}
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span style="font-size:12px;color:#4a5568;font-weight:600;">Buat akun?</span>
                        <button type="button" id="btn-akun-ya"
                                onclick="setAkun(true)"
                                style="padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #4299e1;background:#4299e1;color:white;">
                            Ya
                        </button>
                        <button type="button" id="btn-akun-tidak"
                                onclick="setAkun(false)"
                                style="padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #e2e8f0;background:white;color:#718096;">
                            Tidak
                        </button>
                    </div>
                </div>

                {{-- Form akun (default tampil) --}}
                <input type="hidden" name="buat_akun" id="buat_akun_val" value="{{ old('buat_akun', '1') }}">
                <div id="section-akun" style="display:block;padding:18px;">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Email Login <span style="color:#e53e3e;">*</span></label>
                            <input type="email" name="user_email" id="user_email" class="form-control"
                                   value="{{ old('user_email') }}"
                                   placeholder="email@erpgmp.com">
                        </div>
                        <div class="form-group">
                            <label>Password <span style="color:#e53e3e;">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="user_password" id="user_password"
                                       class="form-control" value="{{ old('user_password', 'gmp12345') }}">
                                <button type="button" onclick="togglePass()"
                                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#a0aec0;cursor:pointer;">
                                    <i class="fas fa-eye" id="icon-pass"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="user_role" value="karyawan">
                    </div>
                    <div style="background:#fffbeb;border:1px solid #fbd38d;border-radius:8px;padding:10px 14px;font-size:12px;color:#744210;">
                        <i class="fas fa-info-circle"></i>
                        Password default <strong>gmp12345</strong> — karyawan bisa ganti sendiri setelah login pertama.
                    </div>
                </div>

                {{-- Pesan jika pilih Tidak --}}
                <div id="section-tidak" style="display:none;padding:14px 18px;font-size:13px;color:#718096;">
                    <i class="fas fa-info-circle"></i>
                    Akun bisa dibuat nanti dari halaman detail karyawan.
                </div>
            </div>
            {{-- ===== END BUAT AKUN ===== --}}

            {{-- ===== DOKUMEN IDENTITAS (OPSIONAL) ===== --}}
            <div style="margin-bottom:24px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;cursor:pointer;"
                     onclick="toggleDokumen()">
                    <div>
                        <h6 style="margin:0;font-weight:700;color:#2d3748;">
                            <i class="fas fa-id-card" style="color:#805ad5;"></i> Dokumen Identitas
                            <span style="background:#e9d8fd;color:#553c9a;font-size:11px;padding:2px 8px;border-radius:10px;margin-left:8px;font-weight:600;">Opsional</span>
                        </h6>
                        <small style="color:#a0aec0;">NIK KTP (16 digit identitas negara), No. KK, NPWP, BPJS — bisa diisi nanti</small>
                    </div>
                    <i class="fas fa-chevron-down" id="icon-dokumen" style="color:#a0aec0;transition:transform 0.3s;"></i>
                </div>

                <div id="section-dokumen" style="display:none;">
                    <div style="background:#faf5ff;border:1px solid #d6bcfa;border-radius:12px;padding:20px;">
                        <div class="grid-2">
                            <div class="form-group">
                                <label>NIK KTP
                                    <span style="background:#fef9c3;color:#854d0e;font-size:10px;font-weight:600;padding:2px 7px;border-radius:8px;margin-left:4px;">Identitas Negara</span>
                                </label>
                                <input type="text" name="doc_nik" class="form-control" value="{{ old('doc_nik') }}"
                                       placeholder="16 digit NIK KTP" maxlength="20" pattern="[0-9]{16}"
                                       oninput="this.value=this.value.replace(/\D/g,'')">
                                <small style="color:#a0aec0;">Nomor Induk Kependudukan 16 digit di KTP — berbeda dengan NIK Karyawan</small>
                            </div>
                            <div class="form-group">
                                <label>No. KK</label>
                                <input type="text" name="doc_kk" class="form-control" value="{{ old('doc_kk') }}"
                                       placeholder="16 digit No. KK" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label>NPWP</label>
                                <input type="text" name="doc_npwp" class="form-control" value="{{ old('doc_npwp') }}"
                                       placeholder="XX.XXX.XXX.X-XXX.XXX" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label>No. BPJS Ketenagakerjaan</label>
                                <input type="text" name="doc_bpjs_tk" class="form-control" value="{{ old('doc_bpjs_tk') }}"
                                       placeholder="No. BPJS TK" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label>No. BPJS Kesehatan</label>
                                <input type="text" name="doc_bpjs_kesehatan" class="form-control" value="{{ old('doc_bpjs_kesehatan') }}"
                                       placeholder="No. BPJS Kesehatan" maxlength="25">
                            </div>
                        </div>
                        <div style="background:#f3e8ff;border-radius:8px;padding:10px 14px;font-size:12px;color:#553c9a;">
                            <i class="fas fa-lock"></i>
                            Data dokumen hanya dapat dilihat oleh HRD dan Admin. Karyawan lain tidak bisa mengakses.
                        </div>
                    </div>
                </div>
            </div>
            {{-- ===== END DOKUMEN IDENTITAS ===== --}}

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" onclick="return konfirmasiTanpaFoto()"><i class="fas fa-save"></i> Simpan Karyawan</button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function previewIdKaryawan() {
    var tgl = document.getElementById('tanggal_masuk_input').value;
    if (!tgl) return;
    var d = new Date(tgl);
    var yyyy = d.getFullYear();
    var mm   = String(d.getMonth() + 1).padStart(2, '0');
    document.getElementById('preview-id-karyawan').textContent = yyyy + '' + mm + 'XX (urutan dihitung otomatis)';
}
previewIdKaryawan();

let stream = null;
let activeTab = 'kamera';

function switchTab(tab) {
    activeTab = tab;
    document.getElementById('panel-kamera').style.display = tab === 'kamera' ? 'block' : 'none';
    document.getElementById('panel-upload').style.display  = tab === 'upload'  ? 'block' : 'none';

    document.getElementById('btn-tab-kamera').style.cssText = tab === 'kamera'
        ? 'padding:8px 18px;border-radius:8px;border:2px solid #667eea;background:#667eea;color:#fff;font-size:13px;cursor:pointer;'
        : 'padding:8px 18px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;';

    document.getElementById('btn-tab-upload').style.cssText = tab === 'upload'
        ? 'padding:8px 18px;border-radius:8px;border:2px solid #667eea;background:#667eea;color:#fff;font-size:13px;cursor:pointer;'
        : 'padding:8px 18px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;';

    // Hapus input dari tab yang tidak aktif
    if (tab === 'kamera') {
        document.getElementById('foto_input').value = '';
        document.getElementById('upload-preview').style.display = 'none';
        document.getElementById('upload-placeholder').style.display = 'block';
    } else {
        document.getElementById('foto_webcam_input').value = '';
        if (stream) stopKamera();
        document.getElementById('kamera-preview').style.display = 'none';
        document.getElementById('kamera-placeholder').style.display = 'block';
        document.getElementById('kamera-stream').style.display = 'none';
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

    // Tampilkan preview
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


function toggleDokumen() {
    const sec  = document.getElementById('section-dokumen');
    const icon = document.getElementById('icon-dokumen');
    const show = sec.style.display === 'none';
    sec.style.display  = show ? 'block' : 'none';
    icon.style.transform = show ? 'rotate(180deg)' : 'rotate(0deg)';
}

function setAkun(ya) {
    document.getElementById('buat_akun_val').value = ya ? '1' : '0';
    document.getElementById('section-akun').style.display   = ya ? 'block' : 'none';
    document.getElementById('section-tidak').style.display  = ya ? 'none'  : 'block';
    document.getElementById('btn-akun-ya').style.cssText    = ya
        ? 'padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #4299e1;background:#4299e1;color:white;'
        : 'padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #e2e8f0;background:white;color:#718096;';
    document.getElementById('btn-akun-tidak').style.cssText = ya
        ? 'padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #e2e8f0;background:white;color:#718096;'
        : 'padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #e53e3e;background:#e53e3e;color:white;';

    // Auto-generate email from nama when switching to Ya
    if (ya) autoEmail();
}

function autoEmail() {
    const nama  = document.getElementById('nama_input')?.value || '';
    const email = document.getElementById('user_email');
    if (email && !email.value) {
        const slug = nama.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9.]/g, '');
        if (slug) email.value = slug + '@erpgmp.com';
    }
}

function togglePass() {
    const inp  = document.getElementById('user_password');
    const icon = document.getElementById('icon-pass');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
}

function filterDivisi() {
    const deptId = document.getElementById('dept_select').value;
    const sel    = document.getElementById('divisi_select');
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return; // placeholder
        opt.hidden = deptId && opt.dataset.dept !== deptId;
    });
    // Reset pilihan jika tidak relevan
    if (sel.value && sel.options[sel.selectedIndex].hidden) sel.value = '';
}
// Jalankan saat load jika ada old value
document.addEventListener('DOMContentLoaded', filterDivisi);

function konfirmasiTanpaFoto() {
    const webcam = document.getElementById('foto_webcam_input').value;
    const file   = document.getElementById('foto_input').value;
    if (!webcam && !file) {
        return confirm(
            'Foto wajah belum diisi.\n\n' +
            'Data karyawan tetap bisa disimpan sekarang,\n' +
            'dan foto dapat ditambahkan nanti dari halaman detail karyawan.\n\n' +
            'Lanjutkan menyimpan tanpa foto?'
        );
    }
    return true;
}
</script>
@endpush
@endsection

