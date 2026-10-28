@extends('layouts.admin')
@section('title', 'Profil Lengkap Saya')
@section('page-title', 'Profil Lengkap Saya')

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

@media (max-width: 768px) {
    .bio-grid { grid-template-columns: 1fr; }
    .bio-row:nth-child(odd)  { padding-right: 0; }
    .bio-row:nth-child(even) { padding-left: 0; border-left: none; }
}
</style>
@endpush

@php
    $statusLabelHead = [
        'kontrak'=>'Kontrak','tetap'=>'Tetap','magang'=>'Magang',
        'training'=>'Training','outsourcing'=>'Outsourcing','harian'=>'Harian Lepas',
        'nonaktif'=>'Nonaktif','resign'=>'Resign','phk'=>'PHK',
    ];
@endphp

@section('content')
<div style="display:grid; grid-template-columns:260px 1fr; gap:20px; align-items:start;">

    {{-- ── Kartu Profil Kiri ───────────────────────────────────────── --}}
    <div class="card" style="position:sticky; top:80px;">
        <div class="card-body" style="text-align:center; padding:20px;">

            @if($karyawan->foto_wajah)
                <img src="{{ asset('storage/' . $karyawan->foto_wajah) }}"
                     style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #4299e1;">
            @else
                <div style="width:110px;height:110px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:36px;font-weight:800;color:#fff;">
                    {{ strtoupper(substr($karyawan->nama,0,1)) }}
                </div>
            @endif

            <h5 style="margin-top:14px;font-size:16px;font-weight:800;color:#2d3748;">{{ $karyawan->nama }}</h5>
            <p style="color:#718096;font-size:12px;margin-top:2px;">{{ $karyawan->position?->name ?? $karyawan->jabatan ?? '-' }}</p>
            <div style="display:flex;justify-content:center;gap:6px;margin-top:6px;flex-wrap:wrap;">
                <span class="badge badge-{{ $karyawan->status }}">{{ $statusLabelHead[$karyawan->status] ?? ucfirst($karyawan->status) }}</span>
                <span style="background:#ebf8ff;color:#2b6cb0;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;border:1px solid #bee3f8;">
                    {{ $karyawan->nik }}
                </span>
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

            {{-- Tombol Aksi --}}
            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
                <button type="button" class="btn btn-warning" style="flex:1;justify-content:center;" onclick="bukaEditBiodata()">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="{{ route('portal.index') }}" class="btn btn-secondary" style="flex:1;justify-content:center;">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- ── Panel Kanan ─────────────────────────────────────────────── --}}
    <div>
        <div class="card">
            <div class="card-body">
                <div class="tab-nav">
                    <button class="tab-btn" data-tab="tab-biodata" onclick="switchTab('tab-biodata')"><i class="fas fa-id-card"></i> Biodata</button>
                    <button class="tab-btn" data-tab="tab-cuti" onclick="switchTab('tab-cuti')"><i class="fas fa-calendar-alt"></i> Cuti & Izin</button>
                    <button class="tab-btn" data-tab="tab-fmk" onclick="switchTab('tab-fmk')"><i class="fas fa-door-open"></i> Izin Keluar (FMK)</button>
                    <button class="tab-btn" data-tab="tab-pelatihan" onclick="switchTab('tab-pelatihan')"><i class="fas fa-certificate"></i> Pelatihan & Sertifikasi</button>
                    <button class="tab-btn" data-tab="tab-riwayat-kerja" onclick="switchTab('tab-riwayat-kerja')"><i class="fas fa-briefcase"></i> Riwayat Kerja</button>
                    <button class="tab-btn" data-tab="tab-surat-peringatan" onclick="switchTab('tab-surat-peringatan')"><i class="fas fa-exclamation-triangle"></i> Surat Peringatan</button>
                </div>

                <div class="tab-content" id="tab-biodata">
                    @include('portal.partials.tab-biodata')
                </div>

                <div class="tab-content" id="tab-cuti">
                    @include('portal.partials.tab-cuti')
                </div>

                <div class="tab-content" id="tab-fmk">
                    @include('portal.partials.tab-fmk')
                </div>

                <div class="tab-content" id="tab-pelatihan">
                    @include('portal.partials.tab-pelatihan')
                </div>

                <div class="tab-content" id="tab-riwayat-kerja">
                    @include('portal.partials.tab-riwayat-kerja')
                </div>

                <div class="tab-content" id="tab-surat-peringatan">
                    @include('portal.partials.tab-surat-peringatan')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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

function bukaEditBiodata() {
    switchTab('tab-biodata');
    const form = document.getElementById('form-edit-biodata');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function() {
    const validTabs = ['tab-biodata','tab-cuti','tab-fmk','tab-pelatihan','tab-riwayat-kerja','tab-surat-peringatan'];
    const hash = window.location.hash.replace('#', '');
    const initialTab = validTabs.includes(hash) ? hash : 'tab-biodata';
    switchTab(initialTab);
})();
</script>
@endpush
