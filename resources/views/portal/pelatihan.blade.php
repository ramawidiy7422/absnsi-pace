@extends('layouts.admin')
@section('title','Pelatihan & Sertifikasi Saya')
@section('page-title','Pelatihan & Sertifikasi Saya')

@section('content')

{{-- ── Status Wajib ─────────────────────────────────────────────────── --}}
@php
    $jenisWajibLabel = ['cpob'=>'CPOB','k3'=>'K3','hygiene'=>'Higiene'];
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
    @foreach($jenisWajib as $j)
    @php
        $st  = $statusWajib[$j];
        $cfg = match($st) {
            'ok'      => ['bg'=>'#f0fff4','border'=>'#9ae6b4','icon'=>'fa-check-circle','ic'=>'#38a169','txt'=>'Valid & Aktif','lbl'=>'#276749'],
            'expired' => ['bg'=>'#fff5f5','border'=>'#feb2b2','icon'=>'fa-times-circle','ic'=>'#e53e3e','txt'=>'Expired / Tidak Lulus','lbl'=>'#c53030'],
            default   => ['bg'=>'#fffff0','border'=>'#f6e05e','icon'=>'fa-exclamation-circle','ic'=>'#d69e2e','txt'=>'Belum Ada Data','lbl'=>'#b7791f'],
        };
    @endphp
    <div style="background:{{ $cfg['bg'] }};border:2px solid {{ $cfg['border'] }};border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;">
        <i class="fas {{ $cfg['icon'] }}" style="font-size:28px;color:{{ $cfg['ic'] }};flex-shrink:0;"></i>
        <div>
            <div style="font-size:12px;font-weight:800;color:{{ $cfg['lbl'] }};text-transform:uppercase;letter-spacing:0.5px;">{{ $jenisWajibLabel[$j] }}</div>
            <div style="font-size:12px;color:{{ $cfg['lbl'] }};margin-top:3px;">{{ $cfg['txt'] }}</div>
            @if($st === 'expired')
            <div style="font-size:10px;color:#c53030;margin-top:3px;font-weight:600;">Hubungi HRD untuk pembaruan</div>
            @elseif($st === 'missing')
            <div style="font-size:10px;color:#b7791f;margin-top:3px;">Belum pernah mengikuti pelatihan ini</div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ── Info banner jika ada yang bermasalah ───────────────────────────── --}}
@php
    $adaMasalah = collect($statusWajib)->contains(fn($s) => $s !== 'ok');
@endphp
@if($adaMasalah)
<div style="background:#fffbeb;border:1.5px solid #f6ad55;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start;">
    <i class="fas fa-exclamation-triangle" style="color:#c05621;font-size:18px;margin-top:2px;flex-shrink:0;"></i>
    <div>
        <div style="font-size:13px;font-weight:700;color:#c05621;">Perhatian: Ada Sertifikasi Wajib yang Perlu Diperbarui</div>
        <div style="font-size:12px;color:#744210;margin-top:4px;">Sertifikasi CPOB, K3, dan Higiene wajib dimiliki dan masih berlaku untuk seluruh karyawan di lingkungan produksi farmasi. Segera hubungi HRD atau atasan Anda.</div>
    </div>
</div>
@endif

{{-- ── Daftar Pelatihan ─────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-certificate" style="color:#4299e1;"></i> Riwayat Pelatihan & Sertifikasi</h6>
        <span style="font-size:12px;color:#718096;">Total: {{ $pelatihans->count() }} entri</span>
    </div>
    <div class="card-body" style="padding:{{ $pelatihans->isEmpty() ? '30px' : '0' }};">
        @forelse($pelatihans as $p)
        @php $sc = $p->status_color; @endphp
        <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 18px;border-bottom:1px solid #f0f0f0;flex-wrap:wrap;">

            {{-- Ikon jenis --}}
            <div style="width:42px;height:42px;border-radius:10px;background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @php $icon = match($p->jenis) {
                    'cpob'    => 'fa-capsules',
                    'k3'      => 'fa-hard-hat',
                    'hygiene' => 'fa-soap',
                    'sop'     => 'fa-file-alt',
                    'external'=> 'fa-building',
                    default   => 'fa-certificate',
                }; @endphp
                <i class="fas {{ $icon }}" style="color:{{ $sc['color'] }};font-size:16px;"></i>
            </div>

            {{-- Info utama --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                    <span style="font-size:14px;font-weight:700;color:#2d3748;">{{ $p->nama_pelatihan }}</span>
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['border'] }};">
                        {{ match($p->status) {
                            'lulus'           => 'Lulus',
                            'tidak_lulus'     => 'Tidak Lulus',
                            'sedang_berjalan' => 'Sedang Berjalan',
                            'belum_ikut'      => 'Belum Ikut',
                        } }}
                    </span>
                    @if($p->is_expired)
                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:#fff5f5;color:#c53030;border:1px solid #fed7d7;">
                            <i class="fas fa-exclamation-triangle"></i> EXPIRED
                        </span>
                    @elseif($p->is_near_expired)
                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;background:#fffaf0;color:#c05621;border:1px solid #fbd38d;">
                            <i class="fas fa-clock"></i> Hampir Expired ({{ $p->sisa_hari }} hari)
                        </span>
                    @endif
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:#718096;">
                    <span><i class="fas fa-tag" style="width:14px;"></i> {{ $jenisLabel[$p->jenis] ?? $p->jenis }}</span>
                    @if($p->penyelenggara)
                        <span><i class="fas fa-building" style="width:14px;"></i> {{ $p->penyelenggara }}</span>
                    @endif
                    <span><i class="fas fa-calendar" style="width:14px;"></i>
                        {{ $p->tanggal_mulai->format('d M Y') }}
                        @if($p->tanggal_selesai) — {{ $p->tanggal_selesai->format('d M Y') }} @endif
                    </span>
                    @if($p->tanggal_expired)
                        <span style="color:{{ $p->is_expired ? '#c53030' : ($p->is_near_expired ? '#c05621' : '#718096') }};">
                            <i class="fas fa-calendar-times" style="width:14px;"></i>
                            Berlaku s/d {{ $p->tanggal_expired->format('d M Y') }}
                        </span>
                    @endif
                    @if($p->nomor_sertifikat)
                        <span><i class="fas fa-hashtag" style="width:14px;"></i> {{ $p->nomor_sertifikat }}</span>
                    @endif
                </div>

                @if($p->catatan)
                <div style="margin-top:6px;font-size:12px;color:#4a5568;background:#f7fafc;padding:6px 10px;border-radius:6px;border-left:3px solid #cbd5e0;">
                    {{ $p->catatan }}
                </div>
                @endif
            </div>

            {{-- Lihat sertifikat jika ada --}}
            @if($p->lampiran)
            <a href="{{ asset('storage/'.$p->lampiran) }}" target="_blank"
               style="flex-shrink:0;background:#ebf8ff;color:#2b6cb0;border:1px solid #90cdf4;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-file-pdf"></i> Lihat Sertifikat
            </a>
            @endif
        </div>
        @empty
        <div style="text-align:center;padding:48px 20px;color:#a0aec0;">
            <i class="fas fa-certificate" style="font-size:40px;display:block;margin-bottom:12px;opacity:0.3;"></i>
            <div style="font-size:14px;font-weight:600;">Belum ada data pelatihan</div>
            <div style="font-size:12px;margin-top:6px;">Data pelatihan Anda akan ditampilkan di sini setelah diinputkan oleh HRD.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection
