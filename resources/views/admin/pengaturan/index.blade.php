@extends('layouts.admin')
@section('title','Pengaturan Sistem')
@section('page-title','Pengaturan Sistem')

@section('content')
@php
    $all = $settings->flatten()->keyBy('key');
    $logo = $all->get('logo_perusahaan')?->value ?? '/images/logo-gmp.png';
    $sv = fn($key, $default = '') => $all->get($key)?->value ?? $default;
@endphp

<style>
.pg-tabs { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
.pg-tab  { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:2px solid #e2e8f0; background:#fff; color:#718096; transition:all .2s; }
.pg-tab.active { background:#4299e1; border-color:#4299e1; color:#fff; }
.pg-pane { display:none; } .pg-pane.active { display:block; }

.setting-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:20px; overflow:hidden; }
.setting-card-header { background:#f7fafc; padding:14px 20px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; gap:10px; }
.setting-card-header i { width:28px; height:28px; border-radius:6px; background:#ebf8ff; color:#4299e1; display:flex; align-items:center; justify-content:center; font-size:13px; }
.setting-card-header h6 { margin:0; font-size:14px; font-weight:700; color:#2d3748; }
.setting-card-body { padding:20px; }

.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-grid-1 { display:grid; grid-template-columns:1fr; gap:16px; }
@media(max-width:768px){ .form-grid-2{ grid-template-columns:1fr; } }

.form-group label { display:block; font-size:12px; font-weight:700; color:#4a5568; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
.form-group .hint  { font-size:11px; color:#a0aec0; margin-top:4px; }
.form-control, .form-select { width:100%; padding:9px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; color:#2d3748; transition:border .2s; }
.form-control:focus, .form-select:focus { outline:none; border-color:#4299e1; box-shadow:0 0 0 3px rgba(66,153,225,.1); }
textarea.form-control { resize:vertical; min-height:72px; }

.input-prefix { position:relative; }
.input-prefix span { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#718096; font-size:13px; font-weight:600; pointer-events:none; }
.input-prefix input { padding-left:36px; }

/* Logo preview */
.logo-upload-area { border:2px dashed #e2e8f0; border-radius:10px; padding:20px; text-align:center; cursor:pointer; transition:border .2s; }
.logo-upload-area:hover { border-color:#4299e1; background:#f0f9ff; }
.logo-preview { width:120px; height:80px; object-fit:contain; margin-bottom:10px; }
.logo-filename { font-size:12px; color:#718096; margin-top:6px; }

/* Toggle switch */
.toggle-wrap { display:flex; align-items:center; gap:12px; padding:10px 0; }
.toggle { position:relative; display:inline-block; width:44px; height:24px; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; top:0;left:0;right:0;bottom:0; background:#cbd5e0; border-radius:24px; transition:.3s; }
.toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
.toggle input:checked + .toggle-slider { background:#48bb78; }
.toggle input:checked + .toggle-slider:before { transform:translateX(20px); }
.toggle-label { font-size:13px; color:#4a5568; font-weight:600; }

/* Stat info bar */
.info-bar { background:#ebf8ff; border:1px solid #90cdf4; border-radius:8px; padding:12px 16px; display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
.info-bar-item { font-size:12px; color:#2b6cb0; }
.info-bar-item strong { font-weight:700; }

/* Save bar */
.save-bar { position:sticky; bottom:0; background:rgba(255,255,255,.95); backdrop-filter:blur(6px); border-top:1px solid #e2e8f0; padding:14px 0; margin-top:24px; display:flex; justify-content:flex-end; gap:10px; z-index:10; }
</style>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Info bar --}}
<div class="info-bar">
    <div class="info-bar-item"><strong>Perusahaan:</strong> {{ $sv('nama_perusahaan', '-') }}</div>
    <div class="info-bar-item"><strong>Telp:</strong> {{ $sv('no_telp_perusahaan') ?: '-' }}</div>
    <div class="info-bar-item"><strong>Cutoff Masuk:</strong> {{ $sv('cutoff_jam_masuk', 2) }} jam</div>
    <div class="info-bar-item"><strong>Jatah Cuti:</strong> {{ $sv('jatah_cuti_tahunan', 12) }} hari/tahun</div>
    <div class="info-bar-item"><strong>Notif Email:</strong> {{ $sv('email_notif_aktif', '0') == '1' ? 'Aktif' : 'Nonaktif' }}</div>
</div>

{{-- Tabs --}}
<div class="pg-tabs">
    <button type="button" class="pg-tab active" onclick="switchTab('perusahaan',this)"><i class="fas fa-building"></i> Perusahaan</button>
    <button type="button" class="pg-tab" onclick="switchTab('absensi',this)"><i class="fas fa-fingerprint"></i> Absensi</button>
    <button type="button" class="pg-tab" onclick="switchTab('cuti',this)"><i class="fas fa-calendar-alt"></i> Cuti & Lembur</button>
    <button type="button" class="pg-tab" onclick="switchTab('notifikasi',this)"><i class="fas fa-bell"></i> Notifikasi</button>
</div>

<form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data" id="form-pengaturan">
@csrf @method('PUT')

{{-- ═══ TAB PERUSAHAAN ═══ --}}
<div class="pg-pane active" id="pane-perusahaan">

    {{-- Logo --}}
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-image"></i>
            <h6>Logo Perusahaan</h6>
        </div>
        <div class="setting-card-body">
            <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
                <div>
                    <div class="logo-upload-area" onclick="document.getElementById('logo-input').click()" id="logo-drop">
                        <img id="logo-preview-img" src="{{ $logo }}" class="logo-preview" onerror="this.src='/images/logo-gmp.png'">
                        <div style="font-size:12px;color:#718096;">Klik untuk ganti logo</div>
                        <div class="logo-filename" id="logo-fname">PNG / JPG &bull; Maks. 2MB</div>
                    </div>
                    <input type="file" id="logo-input" name="logo_perusahaan" accept=".png,.jpg,.jpeg" style="display:none" onchange="previewLogo(this)">
                </div>
                <div style="flex:1; min-width:200px;">
                    <p style="font-size:13px;color:#4a5568;line-height:1.6;">Logo tampil di halaman login dan header PDF laporan.</p>
                    <ul style="font-size:12px;color:#718096;margin:10px 0 0 16px;line-height:2;">
                        <li>Format: PNG atau JPG</li>
                        <li>Ukuran maks: 2MB</li>
                        <li>Rekomendasi: background transparan (PNG)</li>
                        <li>Rasio ideal: landscape (lebar > tinggi)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Favicon --}}
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-globe"></i>
            <h6>Favicon (Ikon Tab Browser)</h6>
        </div>
        <div class="setting-card-body">
            <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div class="logo-upload-area" onclick="document.getElementById('favicon-input').click()" style="padding:16px 24px;">
                        <img id="favicon-preview-img" src="/favicon.ico?v={{ filemtime(public_path('favicon.ico')) }}"
                             style="width:48px;height:48px;object-fit:contain;margin-bottom:8px;image-rendering:pixelated;"
                             onerror="this.style.display='none'">
                        <div style="font-size:12px;color:#718096;">Klik untuk ganti favicon</div>
                        <div class="logo-filename" id="favicon-fname">PNG / ICO &bull; Maks. 512KB</div>
                    </div>
                    <input type="file" id="favicon-input" name="favicon" accept=".png,.ico,.jpg,.jpeg" style="display:none" onchange="previewFavicon(this)">
                </div>
                <div style="flex:1;min-width:200px;">
                    <p style="font-size:13px;color:#4a5568;line-height:1.6;">Ikon kecil yang muncul di tab browser dan bookmark.</p>
                    <ul style="font-size:12px;color:#718096;margin:10px 0 0 16px;line-height:2;">
                        <li>Format: PNG, ICO, atau JPG</li>
                        <li>Ukuran maks: 512KB</li>
                        <li>Rekomendasi: PNG ukuran 64×64 atau 128×128</li>
                        <li>Background transparan lebih baik</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Identitas --}}
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-building"></i>
            <h6>Identitas Perusahaan</h6>
        </div>
        <div class="setting-card-body">
            <div class="form-grid-2">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="nama_perusahaan" class="form-control" value="{{ $sv('nama_perusahaan') }}" placeholder="PT Global Multi Pharmalab">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Alamat</label>
                    <textarea name="alamat_perusahaan" class="form-control" rows="2" placeholder="Jl. ...">{{ $sv('alamat_perusahaan') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Kota / Kabupaten</label>
                    <input type="text" name="kota_perusahaan" class="form-control" value="{{ $sv('kota_perusahaan') }}" placeholder="Kab. Demak, Jawa Tengah 50118">
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_telp_perusahaan" class="form-control" value="{{ $sv('no_telp_perusahaan') }}" placeholder="(024) 1234567">
                </div>
                <div class="form-group">
                    <label>Email HRD</label>
                    <input type="email" name="email_hr" class="form-control" value="{{ $sv('email_hr') }}" placeholder="hrd@perusahaan.com">
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website_perusahaan" class="form-control" value="{{ $sv('website_perusahaan') }}" placeholder="https://www.perusahaan.com">
                </div>
            </div>
        </div>
    </div>

    {{-- Slip Footer --}}
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-file-invoice"></i>
            <h6>Footer Slip Absensi / Dokumen PDF</h6>
        </div>
        <div class="setting-card-body">
            <div class="form-group">
                <label>Teks Footer</label>
                <textarea name="slip_footer" class="form-control" rows="3" placeholder="Slip ini dicetak secara otomatis oleh sistem absensi digital">{{ $sv('slip_footer') }}</textarea>
                <div class="hint">Muncul di bagian bawah slip absensi PDF.</div>
            </div>
        </div>
    </div>

</div>{{-- end pane perusahaan --}}

{{-- ═══ TAB ABSENSI ═══ --}}
<div class="pg-pane" id="pane-absensi">
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-clock"></i>
            <h6>Jam Kerja</h6>
        </div>
        <div class="setting-card-body">
            <div class="hint" style="margin-bottom:12px;">
                Jam masuk, jam keluar, dan toleransi keterlambatan diatur per shift di menu <a href="{{ route('admin.shift.index') }}"><strong>Manajemen Shift</strong></a>.
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Batas Cutoff Absen Masuk (jam)</label>
                    <input type="number" name="cutoff_jam_masuk" class="form-control" value="{{ $sv('cutoff_jam_masuk', 2) }}" min="1" max="6">
                    <div class="hint">Kiosk menolak scan masuk jika melebihi N jam dari jam shift. Melewati ini = dianggap <strong>alpha</strong>.</div>
                </div>
                <div class="form-group">
                    <label>Toleransi Masuk Awal (menit)</label>
                    <input type="number" name="toleransi_masuk_awal_menit" class="form-control" value="{{ $sv('toleransi_masuk_awal_menit', 30) }}" min="0" max="120">
                    <div class="hint">Berapa menit sebelum jam shift karyawan sudah boleh scan masuk. Di luar window ini kiosk menolak dengan pesan "belum waktunya".</div>
                </div>
                <div class="form-group">
                    <label>Toleransi Pulang Cepat (menit)</label>
                    <input type="number" name="toleransi_pulang_cepat_menit" class="form-control" value="{{ $sv('toleransi_pulang_cepat_menit', 15) }}" min="0" max="60">
                    <div class="hint">Berapa menit sebelum jam keluar shift boleh scan pulang tanpa dianggap "pulang cepat".</div>
                </div>
                <div class="form-group">
                    <label>Minimal Menit Sebelum Bisa Scan Keluar</label>
                    <input type="number" name="min_menit_keluar" class="form-control" value="{{ $sv('min_menit_keluar', 60) }}" min="10" max="240">
                    <div class="hint">Karyawan harus menunggu minimal X menit setelah absen masuk sebelum bisa scan keluar. Default 60 menit.</div>
                </div>
                <div class="form-group">
                    <label>Wajib Izin untuk Pulang Sebelum Jam Shift Selesai</label>
                    <select name="wajib_izin_pulang_cepat" class="form-control">
                        <option value="1" {{ $sv('wajib_izin_pulang_cepat','0')==='1' ? 'selected' : '' }}>Aktif — tolak scan keluar sebelum jam shift selesai tanpa izin disetujui HRD</option>
                        <option value="0" {{ $sv('wajib_izin_pulang_cepat','0')==='0' ? 'selected' : '' }}>Nonaktif — scan keluar tetap boleh kapan saja (hanya dicatat sebagai pulang cepat)</option>
                    </select>
                    <div class="hint">Jika aktif, karyawan TIDAK BISA absen keluar sebelum jam shift selesai kecuali sudah ada <strong>Izin Pulang Cepat</strong> berstatus disetujui untuk hari itu. "Minimal Menit Sebelum Bisa Scan Keluar" di atas tetap berlaku sebagai batas bawah mutlak.</div>
                </div>
                <div class="form-group">
                    <label>Jam Generate Alpha Otomatis</label>
                    <input type="time" name="jam_generate_alpha" class="form-control" value="{{ $sv('jam_generate_alpha', '23:00') }}">
                    <div class="hint">Setiap hari sistem akan otomatis menandai karyawan yang tidak absen sebagai <strong>alpha</strong> pada jam ini.</div>
                </div>
                <div class="form-group">
                    <label>Jam Rekap Harian ke HRD</label>
                    <input type="time" name="jam_rekap_harian" class="form-control" value="{{ $sv('jam_rekap_harian', '17:00') }}">
                    <div class="hint">Jam pengiriman ringkasan absensi harian ke HRD via notifikasi.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-tablet-alt"></i>
            <h6>Kiosk Absensi</h6>
        </div>
        <div class="setting-card-body">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Cooldown Kiosk (detik)</label>
                    <input type="number" name="kiosk_cooldown_detik" class="form-control" value="{{ $sv('kiosk_cooldown_detik', 5) }}" min="2" max="30">
                    <div class="hint">Jeda waktu setelah scan sebelum kiosk siap digunakan lagi.</div>
                </div>
                <div class="form-group">
                    <label>Wajib Face Scan saat Keluar</label>
                    <select name="wajib_selfie_keluar" class="form-control">
                        <option value="0" {{ $sv('wajib_selfie_keluar','0')==='0' ? 'selected' : '' }}>Tidak (QR atau Face sama-sama boleh)</option>
                        <option value="1" {{ $sv('wajib_selfie_keluar','0')==='1' ? 'selected' : '' }}>Ya (wajib face scan saat pulang)</option>
                    </select>
                    <div class="hint">Jika aktif, karyawan hanya bisa scan keluar via kamera wajah.</div>
                </div>
                <div class="form-group">
                    <label>Notif Terlambat ke HRD/Supervisor</label>
                    <select name="notif_terlambat_aktif" class="form-control">
                        <option value="1" {{ $sv('notif_terlambat_aktif','1')==='1' ? 'selected' : '' }}>Aktif — kirim notif saat ada keterlambatan</option>
                        <option value="0" {{ $sv('notif_terlambat_aktif','1')==='0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <div class="hint">HRD dan supervisor mendapat notifikasi real-time ketika karyawan absen terlambat.</div>
                </div>
                <div class="form-group">
                    <label>Alasan Terlambat Wajib Diisi</label>
                    <select name="alasan_terlambat_wajib" class="form-control">
                        <option value="1" {{ $sv('alasan_terlambat_wajib','1')==='1' ? 'selected' : '' }}>Ya — karyawan wajib isi alasan dari portal</option>
                        <option value="0" {{ $sv('alasan_terlambat_wajib','1')==='0' ? 'selected' : '' }}>Tidak wajib</option>
                    </select>
                    <div class="hint">Jika aktif, karyawan yang terlambat akan mendapat reminder dan wajib mengisi alasan di portal.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB CUTI & LEMBUR ═══ --}}
<div class="pg-pane" id="pane-cuti">
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-calendar-alt"></i>
            <h6>Cuti Tahunan</h6>
        </div>
        <div class="setting-card-body">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Jatah Cuti Tahunan (hari)</label>
                    <input type="number" name="jatah_cuti_tahunan" class="form-control" value="{{ $sv('jatah_cuti_tahunan', 12) }}" min="0" max="365">
                    <div class="hint">Saldo cuti default untuk karyawan baru setiap tahun.</div>
                </div>
                <div class="form-group">
                    <label>Maks. Carry-Over Cuti (hari)</label>
                    <input type="number" name="max_carry_over_cuti" class="form-control" value="{{ $sv('max_carry_over_cuti', 0) }}" min="0" max="365">
                    <div class="hint">Sisa cuti tahun lalu yang boleh dibawa ke tahun berikutnya. Isi <strong>0</strong> untuk tidak dibatasi (semua sisa dibawa).</div>
                </div>
            </div>
        </div>
    </div>
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-business-time"></i>
            <h6>Lembur</h6>
        </div>
        <div class="setting-card-body">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Tarif Lembur per Jam</label>
                    <div class="input-prefix">
                        <span>Rp</span>
                        <input type="number" name="tarif_lembur_per_jam" class="form-control" value="{{ $sv('tarif_lembur_per_jam', 25000) }}" min="0" step="1000">
                    </div>
                    <div class="hint">Digunakan untuk perhitungan biaya lembur di laporan.</div>
                </div>
                <div class="form-group">
                    <label>Min. Durasi untuk Deteksi Lembur Otomatis (menit)</label>
                    <input type="number" name="min_lembur_otomatis_menit" class="form-control" value="{{ $sv('min_lembur_otomatis_menit', 30) }}" min="10" max="120">
                    <div class="hint">Kiosk mendeteksi lembur jika pulang lebih dari N menit setelah jam shift selesai.</div>
                </div>
                <div class="form-group">
                    <label>Maks. Jam Lembur per Hari</label>
                    <input type="number" name="max_jam_lembur_harian" class="form-control" value="{{ $sv('max_jam_lembur_harian', 3) }}" min="1" max="8">
                    <div class="hint">Pengajuan lembur yang melebihi batas ini akan ditolak otomatis. Isi <strong>0</strong> untuk tidak dibatasi.</div>
                </div>
                <div class="form-group">
                    <label>Batas Pengajuan Lembur Mendadak (jam sebelumnya)</label>
                    <input type="number" name="batas_pengajuan_lembur_jam" class="form-control" value="{{ $sv('batas_pengajuan_lembur_jam', 0) }}" min="0" max="48">
                    <div class="hint">Jika &gt; 0, karyawan harus ajukan lembur minimal N jam sebelum mulai. <strong>0</strong> = tidak dibatasi.</div>
                </div>
                <div class="form-group">
                    <label>Lembur Wajib Ada SPK</label>
                    <select name="lembur_wajib_spk" class="form-control">
                        <option value="0" {{ $sv('lembur_wajib_spk','0')==='0' ? 'selected' : '' }}>Tidak — lembur mandiri/mendadak diizinkan</option>
                        <option value="1" {{ $sv('lembur_wajib_spk','0')==='1' ? 'selected' : '' }}>Ya — lembur tanpa SPK ditolak otomatis</option>
                    </select>
                    <div class="hint">Jika aktif, pengajuan lembur yang tidak memiliki SPK aktif akan langsung ditolak.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB NOTIFIKASI ═══ --}}
<div class="pg-pane" id="pane-notifikasi">
    <div class="setting-card">
        <div class="setting-card-header">
            <i class="fas fa-bell"></i>
            <h6>Notifikasi Email</h6>
        </div>
        <div class="setting-card-body">
            <div class="toggle-wrap" style="margin-bottom:16px;">
                <label class="toggle">
                    <input type="hidden" name="email_notif_aktif" value="0">
                    <input type="checkbox" name="email_notif_aktif" value="1" {{ $sv('email_notif_aktif', '0') == '1' ? 'checked' : '' }}
                        onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label">Aktifkan notifikasi email ke HRD</span>
            </div>
            <div style="background:#fffff0;border:1px solid #fefcbf;border-radius:8px;padding:12px 16px;font-size:13px;color:#744210;">
                <i class="fas fa-info-circle"></i>
                Notifikasi email dikirim ke email HRD saat ada pengajuan cuti, izin, atau lembur baru.
                Pastikan konfigurasi SMTP di file <code>.env</code> sudah benar.
            </div>
            @if($sv('email_hr'))
            <div style="margin-top:12px;font-size:13px;color:#4a5568;">
                Email tujuan: <strong>{{ $sv('email_hr') }}</strong>
                &nbsp;<a href="#" onclick="switchTab('perusahaan', document.querySelector('.pg-tab')); setTimeout(()=>document.querySelector('[name=email_hr]').focus(),200); return false;" style="font-size:12px;color:#4299e1;">Ubah</a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Save bar --}}
<div class="save-bar">
    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
        <i class="fas fa-undo"></i> Reset
    </button>
    <button type="submit" class="btn btn-primary" style="padding:10px 32px;">
        <i class="fas fa-save"></i> Simpan Pengaturan
    </button>
</div>

</form>

<script>
function switchTab(name, el) {
    document.querySelectorAll('.pg-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.pg-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('pane-' + name).classList.add('active');
    // find the button with matching tab
    document.querySelectorAll('.pg-tab').forEach(t => {
        if (t.getAttribute('onclick') && t.getAttribute('onclick').includes("'" + name + "'")) t.classList.add('active');
    });
}

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('favicon-preview-img');
            img.src = e.target.result;
            img.style.display = '';
            document.getElementById('favicon-fname').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
        };
        reader.readAsDataURL(file);
    }
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logo-preview-img').src = e.target.result;
            document.getElementById('logo-fname').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
        };
        reader.readAsDataURL(file);
    }
}

// Drag & drop logo
const drop = document.getElementById('logo-drop');
drop.addEventListener('dragover', e => { e.preventDefault(); drop.style.borderColor='#4299e1'; });
drop.addEventListener('dragleave', () => { drop.style.borderColor=''; });
drop.addEventListener('drop', e => {
    e.preventDefault(); drop.style.borderColor='';
    const dt = new DataTransfer();
    dt.items.add(e.dataTransfer.files[0]);
    document.getElementById('logo-input').files = dt.files;
    previewLogo(document.getElementById('logo-input'));
});
</script>
@endsection
