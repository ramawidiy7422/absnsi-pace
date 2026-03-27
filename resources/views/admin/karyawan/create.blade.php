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
                    <label>NIK *</label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" required placeholder="Contoh: KRY001">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                </div>
                <div class="form-group">
                    <label>Departemen *</label>
                    <input type="text" name="departemen" class="form-control" value="{{ old('departemen') }}" required placeholder="Contoh: Produksi, QC, Gudang">
                </div>
                <div class="form-group">
                    <label>Jabatan *</label>
                    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" required placeholder="Contoh: Operator, Supervisor">
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
                    <label>No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" class="form-control" value="{{ old('tanggal_masuk') }}" required>
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
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
            </div>

            {{-- ===== FOTO WAJAH SECTION ===== --}}
            <div style="margin-bottom:20px;">
                <label style="font-weight:600; margin-bottom:10px; display:block;">Foto Wajah *</label>

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

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" onclick="return validasiFoto()"><i class="fas fa-save"></i> Simpan Karyawan</button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

function validasiFoto() {
    const webcam = document.getElementById('foto_webcam_input').value;
    const file   = document.getElementById('foto_input').value;
    if (!webcam && !file) {
        alert('Harap ambil foto wajah karyawan terlebih dahulu!\nGunakan Kamera atau Upload File.');
        return false;
    }
    return true;
}
</script>
@endpush
