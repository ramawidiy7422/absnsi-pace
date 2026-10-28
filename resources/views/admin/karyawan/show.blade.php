@extends('layouts.admin')
@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@push('styles')
<style>
.tab-nav {
    display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px; overflow-x: auto; flex-wrap: nowrap;
}
.tab-btn {
    padding: 10px 16px; border: none; background: none; cursor: pointer;
    font-size: 13px; font-weight: 500; color: #718096;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    white-space: nowrap; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px 6px 0 0; text-decoration: none;
}
.tab-btn:hover  { color: #4299e1; background: #f7fafc; }
.tab-btn.active { color: #4299e1; border-bottom-color: #4299e1; background: #ebf8ff; font-weight: 600; }
.tab-content    { display: none; }
.tab-content.active { display: block; }

/* Biodata sections */
.bio-section {
    margin-bottom: 24px;
}
.bio-section-title {
    font-size: 11px; font-weight: 700; color: #a0aec0;
    text-transform: uppercase; letter-spacing: 1px;
    margin-bottom: 12px; padding-bottom: 6px;
    border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 6px;
}
.bio-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0;
}
.bio-row {
    display: flex; flex-direction: column; padding: 10px 0;
    border-bottom: 1px solid #f7fafc;
}
.bio-row:nth-child(odd)  { padding-right: 24px; }
.bio-row:nth-child(even) { padding-left: 24px; border-left: 1px solid #f7fafc; }
.bio-label { font-size: 11px; color: #a0aec0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 3px; }
.bio-value { font-size: 13px; font-weight: 600; color: #2d3748; }
.bio-value.empty { color: #cbd5e0; font-weight: 400; font-style: italic; }

/* Doc card */
.doc-card {
    background: #f7fafc; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 14px 16px;
    display: flex; align-items: center; gap: 12px;
}
.doc-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.doc-label { font-size: 11px; color: #718096; margin-bottom: 2px; }
.doc-value { font-size: 13px; font-weight: 700; color: #2d3748; font-family: monospace; letter-spacing: 0.5px; }
.doc-value.empty { color: #cbd5e0; font-weight: 400; font-style: italic; font-family: inherit; }

/* Layout utama: kartu profil + panel tab */
.karyawan-detail-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 20px;
    align-items: start;
}
.karyawan-profil-card {
    position: sticky;
    top: 80px;
}

@media (max-width: 768px) {
    .bio-grid { grid-template-columns: 1fr; }
    .bio-row:nth-child(odd)  { padding-right: 0; }
    .bio-row:nth-child(even) { padding-left: 0; border-left: none; }

    /* Kartu profil pindah ke atas, tidak lagi sejajar-samping */
    .karyawan-detail-layout {
        grid-template-columns: 1fr;
    }
    .karyawan-profil-card {
        position: static;
        top: auto;
    }

    /* Tab-nav: dari scroll-horizontal jadi grid rapi, tanpa perlu geser */
    .tab-nav {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        overflow-x: visible;
        flex-wrap: unset;
        gap: 6px;
        border-bottom: none;
        margin-bottom: 16px;
    }
    .tab-btn {
        justify-content: center;
        text-align: center;
        white-space: normal;
        line-height: 1.3;
        padding: 10px 8px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .tab-btn.active {
        border-color: #4299e1;
    }
}

@media (max-width: 420px) {
    .tab-nav {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="karyawan-detail-layout">

    {{-- ── Kartu Profil Kiri ───────────────────────────────────────── --}}
    <div class="card karyawan-profil-card">
        <div class="card-body" style="text-align:center; padding:20px;">

            {{-- Foto --}}
            @if(auth()->user()->hasPermission('karyawan.edit'))
            <div style="position:relative; display:inline-block; cursor:pointer;" onclick="document.getElementById('modal-foto').style.display='flex'" title="Klik untuk {{ $karyawan->foto_wajah ? 'ganti' : 'tambah' }} foto wajah">
            @else
            <div style="position:relative; display:inline-block;">
            @endif
                @if($karyawan->foto_wajah)
                    <img src="{{ asset('storage/' . $karyawan->foto_wajah) }}"
                         id="foto-avatar"
                         style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #4299e1;"
                         onerror="this.style.display='none';document.getElementById('foto-placeholder').style.display='flex';">
                    <div id="foto-placeholder" style="width:110px;height:110px;background:#e2e8f0;border-radius:50%;display:none;align-items:center;justify-content:center;margin:0 auto;font-size:42px;color:#a0aec0;">
                        <i class="fas fa-user"></i>
                    </div>
                @else
                    <div id="foto-placeholder" style="width:110px;height:110px;background:linear-gradient(135deg,#fff5f5,#fffbeb);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:42px;color:#fc8181;border:3px dashed #fbd38d;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                @endif
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <div style="position:absolute;bottom:0;right:0;width:30px;height:30px;background:#4299e1;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;">
                    <i class="fas fa-camera"></i>
                </div>
                @endif
            </div>
            @if(!$karyawan->foto_wajah)
            <div style="font-size:11px;color:#dd6b20;margin-top:4px;font-weight:600;">
                <i class="fas fa-exclamation-triangle"></i> Foto wajah belum ada
                @if(auth()->user()->hasPermission('karyawan.edit'))
                &mdash; <span style="text-decoration:underline;cursor:pointer;" onclick="document.getElementById('modal-foto').style.display='flex'">tambah sekarang</span>
                @endif
            </div>
            @endif

            <h5 style="margin-top:14px;font-size:16px;font-weight:800;color:#2d3748;">{{ $karyawan->nama }}</h5>
            <p style="color:#718096;font-size:12px;margin-top:2px;">{{ $karyawan->position?->name ?? $karyawan->jabatan ?? '-' }}</p>
            <div style="display:flex;justify-content:center;gap:6px;margin-top:6px;flex-wrap:wrap;">
                <span class="badge badge-{{ $karyawan->status }}">{{ ucfirst($karyawan->status) }}</span>
                <span style="background:#ebf8ff;color:#2b6cb0;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;border:1px solid #bee3f8;">
                    {{ $karyawan->nik }}
                </span>
                @if($karyawan->inisial)
                <span style="background:#f0fff4;color:#276749;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;border:1px solid #9ae6b4;">
                    {{ $karyawan->inisial }}
                </span>
                @endif
            </div>

            {{-- Quick info --}}
            <div style="margin-top:16px;text-align:left;border-top:1px solid #f0f0f0;padding-top:14px;">
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:12px;color:#4a5568;">
                    <i class="fas fa-sitemap" style="width:14px;color:#a0aec0;"></i>
                    <span>{{ $karyawan->department?->name ?? $karyawan->departemen ?? '-' }}</span>
                </div>
                @if($karyawan->devision)
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:12px;color:#4a5568;">
                    <i class="fas fa-layer-group" style="width:14px;color:#a0aec0;"></i>
                    <span>{{ $karyawan->devision->name }}</span>
                </div>
                @endif
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:12px;color:#4a5568;">
                    <i class="fas fa-clock" style="width:14px;color:#a0aec0;"></i>
                    <span>{{ $karyawan->shift?->nama_shift ?? 'Belum ada shift' }}</span>
                </div>
                @if($karyawan->no_hp)
                <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:12px;color:#4a5568;">
                    <i class="fas fa-phone" style="width:14px;color:#a0aec0;"></i>
                    <span>{{ $karyawan->no_hp }}</span>
                </div>
                @endif
            </div>

            {{-- Info Akun Login --}}
            @if(auth()->user()->hasPermission('users.kelola'))
            <div style="margin-top:14px;padding:10px 12px;border-radius:10px;font-size:12px;text-align:left;
                        {{ $akunUser ? 'background:#f0fff4;border:1px solid #9ae6b4;' : 'background:#fff5f5;border:1px solid #fed7d7;' }}">
                @if($akunUser)
                    <div style="color:#276749;font-weight:700;margin-bottom:3px;">
                        <i class="fas fa-user-check"></i> Punya akun login
                    </div>
                    <div style="color:#4a5568;font-size:11px;">{{ $akunUser->email }}</div>
                    <div style="color:#718096;font-size:11px;">Role: <strong>{{ ucfirst($akunUser->role) }}</strong></div>
                @else
                    <div style="color:#c53030;font-weight:700;margin-bottom:6px;">
                        <i class="fas fa-user-times"></i> Belum punya akun
                    </div>
                    <button onclick="document.getElementById('modalBuatAkun').style.display='flex'"
                            style="width:100%;padding:6px;background:#4299e1;color:white;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-plus"></i> Buat Akun
                    </button>
                @endif
            </div>
            @endif

            {{-- QR Code --}}
            <div style="margin-top:14px;padding:14px;background:#f8f4ff;border-radius:12px;border:1px solid #d6bcfa;text-align:center;">
                <p style="font-size:11px;color:#805ad5;font-weight:700;margin-bottom:8px;"><i class="fas fa-qrcode"></i> QR Absensi</p>
                @if($qrBase64)
                    <img src="{{ $qrBase64 }}"
                         style="width:130px;height:130px;border-radius:8px;background:#fff;padding:4px;">
                @else
                    <div style="width:130px;height:130px;background:#f0e6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto;color:#b794f4;font-size:30px;">
                        <i class="fas fa-qrcode"></i>
                    </div>
                @endif
                <p style="font-size:10px;color:#718096;margin-top:6px;">Backup scan via QR di kiosk</p>
                <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap;justify-content:center;">
                    <a href="{{ route('admin.karyawan.print-qr', $karyawan) }}" target="_blank"
                       style="padding:5px 12px;background:#805ad5;color:#fff;border-radius:6px;font-size:11px;text-decoration:none;">
                        <i class="fas fa-qrcode"></i> Kartu QR
                    </a>
                    <a href="{{ route('admin.karyawan.id-card', $karyawan) }}" target="_blank"
                       style="padding:5px 12px;background:#2b6cb0;color:#fff;border-radius:6px;font-size:11px;text-decoration:none;">
                        <i class="fas fa-id-card"></i> ID Card
                    </a>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
                @if(auth()->user()->hasPermission('karyawan.edit'))
                <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-warning" style="flex:1;justify-content:center;">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary" style="flex:1;justify-content:center;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ── Panel Kanan ─────────────────────────────────────────────── --}}
    <div>
        <div class="card">
            <div class="card-body">
                {{-- Tab Navigation --}}
                <div class="tab-nav">
                    <button class="tab-btn" data-tab="tab-biodata"       onclick="switchTab('tab-biodata')"><i class="fas fa-id-card"></i> Biodata</button>
                    <button class="tab-btn" data-tab="tab-absensi"       onclick="switchTab('tab-absensi')"><i class="fas fa-clipboard-list"></i> Absensi</button>
                    <button class="tab-btn" data-tab="tab-education"     onclick="switchTab('tab-education')"><i class="fas fa-graduation-cap"></i> Pendidikan</button>
                    <button class="tab-btn" data-tab="tab-workhistory"   onclick="switchTab('tab-workhistory')"><i class="fas fa-briefcase"></i> Riwayat Kerja</button>
                    <button class="tab-btn" data-tab="tab-ketidakhadiran" onclick="switchTab('tab-ketidakhadiran')"><i class="fas fa-calendar-alt"></i> Cuti & Izin</button>
                    <button class="tab-btn" data-tab="tab-warninglettter" onclick="switchTab('tab-warninglettter')"><i class="fas fa-exclamation-triangle"></i> Surat Peringatan</button>
                    <button class="tab-btn" data-tab="tab-employeshift"  onclick="switchTab('tab-employeshift')"><i class="fas fa-calendar-week"></i> Jadwal Shift</button>
                    <button class="tab-btn" data-tab="tab-lemburan"     onclick="switchTab('tab-lemburan')"><i class="fas fa-business-time"></i> Lemburan</button>
                    <button class="tab-btn" data-tab="tab-pelatihan"   onclick="switchTab('tab-pelatihan')"><i class="fas fa-certificate"></i> Pelatihan & Sertifikasi</button>
                    <button class="tab-btn" data-tab="tab-slip"      onclick="switchTab('tab-slip')"><i class="fas fa-file-invoice-dollar"></i> Slip Gaji</button>
                </div>

                {{-- ══ TAB: BIODATA ══════════════════════════════════════════ --}}
                <div class="tab-content" id="tab-biodata">

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
                                    <span class="badge badge-{{ $karyawan->status }}">{{ ucfirst($karyawan->status) }}</span>
                                    @if($karyawan->bebas_absen)
                                    <span style="margin-left:6px;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;">
                                        <i class="fas fa-star"></i> Bebas Absen
                                    </span>
                                    @endif
                                </span>
                            </div>
                            @if(in_array($karyawan->status, ['resign','phk','nonaktif']))
                            <div class="bio-row">
                                <span class="bio-label">Tanggal Keluar</span>
                                <span class="bio-value {{ !$karyawan->tanggal_keluar ? 'empty' : '' }}">
                                    {{ $karyawan->tanggal_keluar?->format('d F Y') ?? '—' }}
                                </span>
                            </div>
                            <div class="bio-row">
                                <span class="bio-label">Alasan Keluar</span>
                                <span class="bio-value {{ !$karyawan->alasan_keluar ? 'empty' : '' }}">
                                    {{ $karyawan->alasan_keluar ?? '—' }}
                                </span>
                            </div>
                            @endif
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
                                <span class="bio-label">SPV Approval</span>
                                <span class="bio-value {{ !$karyawan->department?->spvUser ? 'empty' : '' }}">
                                    {{ $karyawan->department?->spvUser?->name ?? '—' }}
                                    @if($karyawan->department && !$karyawan->department->spvUser)
                                    <span style="color:#c05621;font-size:11px;font-weight:400;"> (belum diatur — approval lompat ke Manajer)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="bio-row">
                                <span class="bio-label">Manager Approval</span>
                                <span class="bio-value {{ !$karyawan->department?->managerUser ? 'empty' : '' }}">
                                    {{ $karyawan->department?->managerUser?->name ?? '—' }}
                                    @if($karyawan->department && !$karyawan->department->managerUser)
                                    <span style="color:#c05621;font-size:11px;font-weight:400;"> (belum diatur — approval lompat ke HRD)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="bio-row">
                                <span class="bio-label">Jabatan</span>
                                <span class="bio-value">
                                    {{ $karyawan->position?->name ?? $karyawan->jabatan ?? '—' }}
                                    @if($karyawan->position?->salary_grade)
                                        <span style="color:#a0aec0;font-size:11px;"> · {{ $karyawan->position->salary_grade }}</span>
                                    @endif
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
                                    @if($karyawan->tanggal_masuk)
                                        <span style="color:#a0aec0;font-size:11px;"> · {{ $karyawan->masa_kerja }}</span>
                                    @endif
                                </span>
                            </div>
                            @if($karyawan->tanggal_kontrak_berakhir)
                            <div class="bio-row">
                                <span class="bio-label">Kontrak Berakhir</span>
                                <span class="bio-value">
                                    @php $sisaHari = (int) now()->startOfDay()->diffInDays($karyawan->tanggal_kontrak_berakhir->startOfDay(), false); @endphp
                                    @if($sisaHari < 0)
                                        <span style="color:#c53030;font-weight:700;">{{ $karyawan->tanggal_kontrak_berakhir->translatedFormat('d F Y') }} <i class="fas fa-exclamation-circle"></i> Sudah berakhir</span>
                                    @elseif($sisaHari <= 30)
                                        <span style="color:#c05621;font-weight:700;">{{ $karyawan->tanggal_kontrak_berakhir->translatedFormat('d F Y') }} <i class="fas fa-clock"></i> {{ $sisaHari }} hari lagi</span>
                                    @else
                                        <span style="color:#276749;">{{ $karyawan->tanggal_kontrak_berakhir->translatedFormat('d F Y') }}</span>
                                        <span style="color:#a0aec0;font-size:11px;"> · {{ $sisaHari }} hari lagi</span>
                                    @endif
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Dokumen Identitas ── --}}
                    {{-- superadmin/admin/hrd: lihat semua. karyawan: hanya lihat punya sendiri --}}
                    @php
                        $bolehLihatDokumen = auth()->user()->hasPermission('karyawan.lihat')
                            || (auth()->user()->karyawan_id == $karyawan->id);
                        $bolehLihatSemua   = auth()->user()->hasPermission('karyawan.lihat');
                    @endphp
                    @if($bolehLihatDokumen)
                    <div class="bio-section">
                        <div class="bio-section-title">
                            <i class="fas fa-shield-alt" style="color:#e53e3e;"></i> Dokumen Identitas
                            <span style="font-size:10px;font-weight:400;color:#fc8181;margin-left:4px;">
                                <i class="fas fa-lock"></i> Rahasia
                            </span>
                        </div>
                        @php $legaldoc = $karyawan->legaldoc; @endphp
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">

                            {{-- NIK KTP dari legaldoc --}}
                            @php
                                $nikKtp = $legaldoc->nik ?? null;
                            @endphp
                            <div class="doc-card">
                                <div class="doc-icon" style="background:#ebf8ff;color:#2b6cb0;">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <div class="doc-label">NIK KTP</div>
                                    <div class="doc-value {{ !$nikKtp ? 'empty' : '' }}">
                                        {{ $nikKtp ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Dokumen detail hanya untuk admin/hrd/superadmin --}}
                            @if($bolehLihatSemua)

                            {{-- KK --}}
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

                            {{-- NPWP --}}
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

                            {{-- BPJS TK --}}
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

                            {{-- BPJS Kesehatan --}}
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

                            @endif {{-- bolehLihatSemua --}}
                        </div>

                        @if($bolehLihatSemua)
                        <div style="margin-top:10px;text-align:right;">
                            <button onclick="switchTab('tab-legaldoc')"
                                    style="font-size:12px;color:#4299e1;background:none;border:none;cursor:pointer;text-decoration:underline;">
                                <i class="fas fa-edit"></i> Edit dokumen legal →
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Edit button --}}
                    @if(auth()->user()->hasPermission('karyawan.edit'))
                    <div style="margin-top:8px;padding-top:16px;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;">
                        <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Data Karyawan
                        </a>
                    </div>
                    @endif
                </div>

                {{-- ══ TAB: ABSENSI ══════════════════════════════════════════ --}}
                <div class="tab-content" id="tab-absensi">
                    @php
                        $absensiByTgl = $absensiTerbaru->keyBy(fn($a) => \Carbon\Carbon::parse($a->tanggal)->format('Y-m-d'));
                        $bulanKal = now()->month;
                        $tahunKal = now()->year;
                        $hariDlmBulan = now()->daysInMonth;
                        $hariPertama  = \Carbon\Carbon::createFromDate($tahunKal, $bulanKal, 1)->dayOfWeek; // 0=Sun
                        // Statistik 30 hari terakhir
                        $statHadir    = $absensiTerbaru->whereIn('status', ['hadir','terlambat'])->count();
                        $statTelat    = $absensiTerbaru->where('status', 'terlambat')->count();
                        $statIzin     = $absensiTerbaru->where('status', 'izin')->count();
                        $statSakit    = $absensiTerbaru->where('status', 'sakit')->count();
                        $statAlpha    = $absensiTerbaru->where('status', 'alpha')->count();
                        $statusWarna  = ['hadir'=>'#48bb78','terlambat'=>'#ed8936','izin'=>'#4299e1','sakit'=>'#9f7aea','alpha'=>'#fc8181','cuti'=>'#38b2ac'];
                        $statusLabel  = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha','cuti'=>'Cuti'];
                    @endphp

                    {{-- Statistik singkat --}}
                    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:80px;background:#f0fff4;border:1px solid #9ae6b4;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#276749;">{{ $statHadir }}</div>
                            <div style="font-size:11px;color:#276749;">Hadir</div>
                        </div>
                        <div style="flex:1;min-width:80px;background:#fffaf0;border:1px solid #fbd38d;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#c05621;">{{ $statTelat }}</div>
                            <div style="font-size:11px;color:#c05621;">Terlambat</div>
                        </div>
                        <div style="flex:1;min-width:80px;background:#ebf8ff;border:1px solid #90cdf4;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#2b6cb0;">{{ $statIzin }}</div>
                            <div style="font-size:11px;color:#2b6cb0;">Izin</div>
                        </div>
                        <div style="flex:1;min-width:80px;background:#faf5ff;border:1px solid #d6bcfa;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#6b46c1;">{{ $statSakit }}</div>
                            <div style="font-size:11px;color:#6b46c1;">Sakit</div>
                        </div>
                        <div style="flex:1;min-width:80px;background:#fff5f5;border:1px solid #feb2b2;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#c53030;">{{ $statAlpha }}</div>
                            <div style="font-size:11px;color:#c53030;">Alpha</div>
                        </div>
                    </div>

                    {{-- Kalender bulan ini --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <span style="font-weight:700;font-size:13px;color:#2d3748;">
                                <i class="fas fa-calendar-alt" style="color:#4299e1;"></i>
                                Kalender {{ now()->translatedFormat('F Y') }}
                            </span>
                            <div style="display:flex;gap:10px;font-size:11px;flex-wrap:wrap;">
                                <span><span style="display:inline-block;width:10px;height:10px;background:#48bb78;border-radius:2px;"></span> Hadir</span>
                                <span><span style="display:inline-block;width:10px;height:10px;background:#ed8936;border-radius:2px;"></span> Terlambat</span>
                                <span><span style="display:inline-block;width:10px;height:10px;background:#4299e1;border-radius:2px;"></span> Izin</span>
                                <span><span style="display:inline-block;width:10px;height:10px;background:#9f7aea;border-radius:2px;"></span> Sakit</span>
                                <span><span style="display:inline-block;width:10px;height:10px;background:#fc8181;border-radius:2px;"></span> Alpha</span>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center;">
                            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $hn)
                                <div style="font-size:10px;font-weight:700;color:#718096;padding:3px 0;">{{ $hn }}</div>
                            @endforeach
                            {{-- Offset awal bulan --}}
                            @for($off = 0; $off < $hariPertama; $off++)
                                <div></div>
                            @endfor
                            {{-- Hari-hari dalam bulan --}}
                            @for($tgl = 1; $tgl <= $hariDlmBulan; $tgl++)
                                @php
                                    $tglKey = sprintf('%d-%02d-%02d', $tahunKal, $bulanKal, $tgl);
                                    $rec    = $absensiByTgl->get($tglKey);
                                    $status = $rec?->status;
                                    $warna  = $status ? ($statusWarna[$status] ?? '#a0aec0') : null;
                                    $isToday = $tgl === now()->day;
                                    $isFuture = \Carbon\Carbon::createFromDate($tahunKal,$bulanKal,$tgl)->isFuture();
                                    $isWeekend = in_array(\Carbon\Carbon::createFromDate($tahunKal,$bulanKal,$tgl)->dayOfWeek, [0,6]);
                                @endphp
                                <div title="{{ $tglKey }}{{ $status ? ': ' . ($statusLabel[$status] ?? $status) : '' }}"
                                     style="padding:4px 2px;border-radius:5px;font-size:11px;font-weight:{{ $isToday ? '800' : '600' }};
                                            background:{{ $warna ?? ($isWeekend ? '#f7fafc' : 'transparent') }};
                                            color:{{ $warna ? '#fff' : ($isWeekend ? '#a0aec0' : ($isFuture ? '#cbd5e0' : '#4a5568')) }};
                                            border:{{ $isToday ? '2px solid #3182ce' : '1px solid transparent' }};">
                                    {{ $tgl }}
                                    @if($status === 'terlambat')<div style="font-size:7px;line-height:1;">telat</div>@endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Tabel riwayat --}}
                    <h6 style="font-weight:600;color:#2d3748;margin-bottom:10px;">
                        <i class="fas fa-list" style="color:#4299e1;"></i> Riwayat 30 Terakhir
                    </h6>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absensiTerbaru as $a)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $a->jam_masuk ?? '-' }}</td>
                                    <td>{{ $a->jam_keluar ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $a->badge_class }}" title="{{ $a->keterangan_terlambat ?? '' }}">
                                            @if($a->status === 'terlambat')<i class="fas fa-clock"></i> @endif
                                            {{ $a->badge_label }}
                                            @if($a->has_alasan_terlambat)<i class="fas fa-comment-alt" style="font-size:9px;"></i>@endif
                                        </span>
                                    </td>
                                    <td>
                                        @if(($a->metode ?? 'wajah') === 'qrcode')
                                            <span style="font-size:11px;background:#f3e8ff;color:#805ad5;padding:2px 8px;border-radius:10px;"><i class="fas fa-qrcode"></i> QR</span>
                                        @else
                                            <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 8px;border-radius:10px;"><i class="fas fa-face-smile"></i> Wajah</span>
                                        @endif
                                    </td>
                                    <td>{{ $a->confidence_masuk ? $a->confidence_masuk . '%' : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">
                                        <i class="fas fa-calendar-times" style="font-size:30px;opacity:0.3;display:block;margin-bottom:8px;"></i>
                                        Belum ada data absensi
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ══ TAB: PENDIDIKAN ═══════════════════════════════════════ --}}
                <div class="tab-content" id="tab-education">
                    @include('admin.karyawan.partials.education')
                </div>

                {{-- ══ TAB: DOKUMEN LEGAL ════════════════════════════════════ --}}
                <div class="tab-content" id="tab-legaldoc">
                    @include('admin.karyawan.partials.legaldoc')
                </div>

                {{-- ══ TAB: RIWAYAT KERJA ════════════════════════════════════ --}}
                <div class="tab-content" id="tab-workhistory">
                    @include('admin.karyawan.partials.workhistory')
                </div>

                {{-- ══ TAB: SURAT PERINGATAN ════════════════════════════════ --}}
                <div class="tab-content" id="tab-warninglettter">
                    @include('admin.karyawan.partials.warninglettter')
                </div>

                {{-- ══ TAB: CUTI & KETIDAKHADIRAN (termasuk Izin Terlambat) ══ --}}
                <div class="tab-content" id="tab-ketidakhadiran">
                    @include('admin.karyawan.partials.ketidakhadiran')
                </div>

                {{-- ══ TAB: JADWAL SHIFT ══════════════════════════════════════ --}}
                <div class="tab-content" id="tab-employeshift">
                    @include('admin.karyawan.partials.employeshift')
                </div>

                {{-- ══ TAB: LEMBURAN ══════════════════════════════════════════ --}}
                <div class="tab-content" id="tab-lemburan">
                    @include('admin.karyawan.partials.lemburan')
                </div>

                {{-- ══ TAB: PELATIHAN & SERTIFIKASI ══════════════════════════ --}}
                <div class="tab-content" id="tab-pelatihan">
                    @include('admin.karyawan.partials.pelatihan')
                </div>

                {{-- ══ TAB: SLIP GAJI ══════════════════════════════════════ --}}
                <div class="tab-content" id="tab-slip">
                    @php
                        $tahunSlip = request('slip_tahun', now()->year);
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
                        <div style="font-weight:700;font-size:14px;color:#2d3748;"><i class="fas fa-file-invoice-dollar" style="color:#6b46c1;margin-right:6px;"></i>Slip Gaji</div>
                        <form method="GET" action="" style="display:flex;gap:8px;align-items:center;">
                            <select name="slip_tahun" onchange="this.form.submit()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:13px;">
                                @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ $y == $tahunSlip ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <input type="hidden" name="tab" value="tab-slip">
                        </form>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                        @php
                            $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        @endphp
                        @for($m = 1; $m <= 12; $m++)
                        @php
                            $adaAbsensi = \App\Models\Absensi::where('karyawan_id',$karyawan->id)
                                ->whereMonth('tanggal',$m)->whereYear('tanggal',$tahunSlip)->exists();
                            $isFuture = ($tahunSlip == now()->year && $m > now()->month);
                        @endphp
                        <div style="border:1px solid {{ $adaAbsensi ? '#c3dafe' : '#e2e8f0' }};border-radius:10px;padding:14px;background:{{ $adaAbsensi ? '#ebf8ff' : '#f9fafb' }};text-align:center;">
                            <div style="font-size:13px;font-weight:600;color:{{ $adaAbsensi ? '#2b6cb0' : '#a0aec0' }};margin-bottom:8px;">{{ $bulanNames[$m] }}</div>
                            @if($adaAbsensi)
                            <a href="{{ route('admin.laporan.slip-pdf', ['karyawan'=>$karyawan->id,'bulan'=>$m,'tahun'=>$tahunSlip]) }}"
                               target="_blank"
                               style="display:inline-block;padding:5px 12px;background:#2b6cb0;color:white;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;">
                                <i class="fas fa-file-pdf"></i> Unduh PDF
                            </a>
                            @elseif($isFuture)
                            <span style="font-size:11px;color:#cbd5e0;">Belum tersedia</span>
                            @else
                            <span style="font-size:11px;color:#e2e8f0;">Tidak ada data</span>
                            @endif
                        </div>
                        @endfor
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Modal Buat Akun --}}
@if(auth()->user()->hasPermission('users.kelola') && !$akunUser)
<div id="modalBuatAkun" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;padding:28px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;font-size:15px;">
                <i class="fas fa-user-plus" style="color:#4299e1;"></i> Buat Akun Login
            </h6>
            <button onclick="document.getElementById('modalBuatAkun').style.display='none'"
                    style="background:none;border:none;font-size:20px;cursor:pointer;color:#a0aec0;">&times;</button>
        </div>
        <div style="background:#ebf8ff;border-radius:9px;padding:10px 14px;margin-bottom:18px;font-size:13px;">
            <i class="fas fa-user" style="color:#4299e1;"></i>
            <strong>{{ $karyawan->nama }}</strong>
            &nbsp;·&nbsp; {{ $karyawan->nik }}
            <br><span style="color:#718096;">{{ $karyawan->jabatan }} — {{ $karyawan->department?->name ?? $karyawan->departemen }}</span>
        </div>
        @if(session('error_akun'))
            <div style="background:#fff5f5;border:1px solid #fed7d7;color:#c53030;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error_akun') }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.karyawan.buat-akun', $karyawan) }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Email Login <span style="color:#e53e3e;">*</span></label>
                <input type="email" name="email" required
                       value="{{ old('email', strtolower(str_replace(' ', '.', $karyawan->nama)) . '@erpgmp.com') }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">Password <span style="color:#e53e3e;">*</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="mdl_pass" required value="gmp12345"
                           style="width:100%;padding:9px 36px 9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    <button type="button" onclick="toggleMdlPass()"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#a0aec0;cursor:pointer;">
                        <i class="fas fa-eye" id="mdl_pass_icon"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="role" value="karyawan">
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modalBuatAkun').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Buat Akun</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal Foto Wajah --}}
@if(auth()->user()->hasPermission('karyawan.edit'))
<div id="modal-foto" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:1001;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:480px;padding:28px;margin:16px;box-shadow:0 24px 60px rgba(0,0,0,0.25);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h6 style="font-weight:700;color:#2d3748;margin:0;font-size:15px;">
                <i class="fas fa-camera" style="color:#4299e1;"></i>
                {{ $karyawan->foto_wajah ? 'Ganti Foto Wajah' : 'Tambah Foto Wajah' }}
            </h6>
            <button onclick="tutupModalFoto()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#a0aec0;line-height:1;">&times;</button>
        </div>

        <div style="background:#fffbeb;border:1px solid #f6e05e;border-radius:9px;padding:10px 14px;margin-bottom:18px;font-size:12px;color:#92400e;">
            <i class="fas fa-info-circle"></i> Foto akan langsung didaftarkan ke sistem <strong>Face Recognition</strong>.
            Pastikan wajah terlihat jelas, pencahayaan cukup, dan tampak dari depan.
        </div>

        <form method="POST" action="{{ route('admin.karyawan.foto', $karyawan) }}" enctype="multipart/form-data" id="form-foto">
            @csrf
            <input type="hidden" name="foto_webcam" id="foto-webcam-val">

            {{-- Tab pilihan --}}
            <div style="display:flex;gap:8px;margin-bottom:14px;">
                <button type="button" id="ftab-kamera"
                    onclick="switchFotoTab('kamera')"
                    style="flex:1;padding:8px;border-radius:8px;border:2px solid #4299e1;background:#4299e1;color:#fff;font-size:13px;cursor:pointer;font-weight:600;">
                    <i class="fas fa-camera"></i> Kamera
                </button>
                <button type="button" id="ftab-upload"
                    onclick="switchFotoTab('upload')"
                    style="flex:1;padding:8px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;font-weight:600;">
                    <i class="fas fa-upload"></i> Upload File
                </button>
            </div>

            {{-- Panel Kamera --}}
            <div id="fpanel-kamera" style="text-align:center;margin-bottom:16px;">
                <div id="fkamera-stream" style="display:none;">
                    <video id="fvideo" autoplay playsinline style="width:100%;max-width:340px;border-radius:12px;background:#000;"></video>
                    <div style="margin-top:10px;display:flex;gap:8px;justify-content:center;">
                        <button type="button" onclick="fAmbilFoto()"
                            style="padding:9px 22px;background:#48bb78;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                        <button type="button" onclick="fStopKamera()"
                            style="padding:9px 14px;background:#e53e3e;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <canvas id="fcanvas" style="display:none;"></canvas>
                <div id="fkamera-preview" style="display:none;">
                    <img id="fimg-preview" style="width:200px;height:200px;border-radius:50%;object-fit:cover;border:3px solid #48bb78;">
                    <br>
                    <button type="button" onclick="fUlangiKamera()"
                        style="margin-top:10px;padding:7px 18px;background:#667eea;color:#fff;border:none;border-radius:8px;font-size:12px;cursor:pointer;">
                        <i class="fas fa-redo"></i> Ambil Ulang
                    </button>
                    <p style="color:#48bb78;font-size:12px;margin-top:6px;"><i class="fas fa-check-circle"></i> Foto siap dikirim</p>
                </div>
                <div id="fkamera-placeholder">
                    <div style="width:120px;height:120px;background:#f7fafc;border-radius:50%;border:2px dashed #cbd5e0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:40px;color:#a0aec0;">
                        <i class="fas fa-camera"></i>
                    </div>
                    <button type="button" onclick="fBukaKamera()"
                        style="padding:10px 24px;background:#4299e1;color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-video"></i> Buka Kamera
                    </button>
                </div>
            </div>

            {{-- Panel Upload --}}
            <div id="fpanel-upload" style="display:none;text-align:center;margin-bottom:16px;">
                <label for="finput-file" style="cursor:pointer;display:block;">
                    <div id="fupload-preview" style="display:none;">
                        <img id="fupload-img" style="width:200px;height:200px;border-radius:50%;object-fit:cover;border:3px solid #48bb78;margin-bottom:10px;">
                    </div>
                    <div id="fupload-placeholder" style="border:2px dashed #e2e8f0;border-radius:12px;padding:28px;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:40px;color:#a0aec0;display:block;margin-bottom:10px;"></i>
                        <p style="color:#718096;font-size:13px;">Klik untuk pilih foto</p>
                        <small style="color:#a0aec0;">JPG/PNG, maks 2MB</small>
                    </div>
                </label>
                <input type="file" id="finput-file" name="foto_wajah" accept="image/*" style="display:none;" onchange="fPreviewUpload(this)">
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="tutupModalFoto()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Foto</button>
            </div>
        </form>
    </div>
</div>
@endif
@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    document.querySelectorAll('.tab-btn[data-tab="' + tabId + '"]').forEach(btn => btn.classList.add('active'));
    history.replaceState(null, null, '#' + tabId);
}

function toggleMdlPass() {
    const inp  = document.getElementById('mdl_pass');
    const icon = document.getElementById('mdl_pass_icon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
}

const modalBuatAkun = document.getElementById('modalBuatAkun');
if (modalBuatAkun) {
    modalBuatAkun.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
}

// ── MODAL FOTO ────────────────────────────────────────────────────────────────
let fStream = null;

function tutupModalFoto() {
    document.getElementById('modal-foto').style.display = 'none';
    fStopKamera();
}
document.getElementById('modal-foto')?.addEventListener('click', function(e) {
    if (e.target === this) tutupModalFoto();
});

function switchFotoTab(tab) {
    document.getElementById('fpanel-kamera').style.display = tab === 'kamera' ? 'block' : 'none';
    document.getElementById('fpanel-upload').style.display = tab === 'upload' ? 'block' : 'none';
    document.getElementById('ftab-kamera').style.cssText = tab === 'kamera'
        ? 'flex:1;padding:8px;border-radius:8px;border:2px solid #4299e1;background:#4299e1;color:#fff;font-size:13px;cursor:pointer;font-weight:600;'
        : 'flex:1;padding:8px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;font-weight:600;';
    document.getElementById('ftab-upload').style.cssText = tab === 'upload'
        ? 'flex:1;padding:8px;border-radius:8px;border:2px solid #4299e1;background:#4299e1;color:#fff;font-size:13px;cursor:pointer;font-weight:600;'
        : 'flex:1;padding:8px;border-radius:8px;border:2px solid #e2e8f0;background:#fff;color:#4a5568;font-size:13px;cursor:pointer;font-weight:600;';
    if (tab === 'upload') {
        fStopKamera();
        document.getElementById('foto-webcam-val').value = '';
    }
    if (tab === 'kamera') {
        document.getElementById('finput-file').value = '';
    }
}

function fBukaKamera() {
    navigator.mediaDevices.getUserMedia({ video: { width:{ideal:640}, height:{ideal:480}, facingMode:'user' } })
    .then(function(stream) {
        fStream = stream;
        const video = document.getElementById('fvideo');
        video.srcObject = stream;
        video.play();
        document.getElementById('fkamera-placeholder').style.display = 'none';
        document.getElementById('fkamera-stream').style.display = 'block';
    }).catch(function(err) {
        alert('Tidak bisa mengakses kamera: ' + err.message);
    });
}

function fStopKamera() {
    if (fStream) { fStream.getTracks().forEach(t => t.stop()); fStream = null; }
    const vEl = document.getElementById('fvideo');
    if (vEl) { vEl.srcObject = null; }
    const st = document.getElementById('fkamera-stream');
    const ph = document.getElementById('fkamera-placeholder');
    if (st) st.style.display = 'none';
    if (ph) ph.style.display = 'block';
}

function fAmbilFoto() {
    const video = document.getElementById('fvideo');
    const canvas = document.getElementById('fcanvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    document.getElementById('foto-webcam-val').value = dataUrl;
    document.getElementById('fimg-preview').src = dataUrl;
    document.getElementById('fkamera-stream').style.display = 'none';
    document.getElementById('fkamera-preview').style.display = 'block';
    fStopKamera();
}

function fUlangiKamera() {
    document.getElementById('fkamera-preview').style.display = 'none';
    document.getElementById('foto-webcam-val').value = '';
    fBukaKamera();
    document.getElementById('fkamera-stream').style.display = 'block';
    document.getElementById('fkamera-placeholder').style.display = 'none';
}

function fPreviewUpload(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('fupload-img').src = e.target.result;
        document.getElementById('fupload-preview').style.display = 'block';
        document.getElementById('fupload-placeholder').style.display = 'none';
        document.getElementById('foto-webcam-val').value = '';
    };
    reader.readAsDataURL(input.files[0]);
}

(function() {
    const hash = window.location.hash.replace('#', '');
    const validTabs = ['tab-biodata','tab-absensi','tab-education','tab-legaldoc','tab-workhistory','tab-warninglettter','tab-ketidakhadiran','tab-employeshift','tab-lemburan','tab-pelatihan','tab-slip'];
    switchTab(validTabs.includes(hash) ? hash : 'tab-biodata');
})();
</script>
@endpush
@endsection

