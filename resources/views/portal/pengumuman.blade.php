@extends('layouts.admin')
@section('title', 'Pengumuman')
@section('page-title', 'Pengumuman Perusahaan')

@section('content')
<div style="max-width:860px;margin:0 auto;">

{{-- Header --}}
<div style="background:linear-gradient(135deg,#1e40af,#3b82f6);border-radius:16px;padding:24px 28px;margin-bottom:20px;color:white;">
    <div style="font-size:13px;font-weight:600;opacity:.8;letter-spacing:.5px;">PORTAL KARYAWAN</div>
    <div style="font-size:22px;font-weight:800;margin-top:4px;"><i class="fas fa-bullhorn"></i> Pengumuman Perusahaan</div>
    <div style="font-size:13px;opacity:.8;margin-top:4px;">Informasi & memo resmi dari manajemen</div>
</div>

@if($pengumuman->isEmpty())
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:48px 28px;text-align:center;color:#94a3b8;">
    <i class="fas fa-bullhorn" style="font-size:40px;margin-bottom:14px;display:block;opacity:.4;"></i>
    <div style="font-size:15px;font-weight:600;">Belum ada pengumuman aktif</div>
    <div style="font-size:13px;margin-top:6px;">Pengumuman dari HRD/manajemen akan tampil di sini.</div>
</div>
@else

{{-- Daftar pengumuman --}}
<div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px;">
@foreach($pengumuman as $p)
@php
    $prioritas = $p->badge_prioritas;
    $jenis     = $p->badge_jenis;
    $borderColor = match($p->prioritas) {
        'mendesak' => '#dc2626',
        'penting'  => '#d97706',
        default    => '#3b82f6',
    };
    $isExpiring = $p->tanggal_selesai && $p->tanggal_selesai->diffInDays(now()) <= 3;
@endphp
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;border-left:4px solid {{ $borderColor }};overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
    <div style="padding:16px 20px;">
        {{-- Header baris --}}
        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;flex-wrap:wrap;">
            <span style="background:{{ $jenis['bg'] }};color:{{ $jenis['text'] }};border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;white-space:nowrap;">
                <i class="{{ $jenis['icon'] }}"></i> {{ $jenis['label'] }}
            </span>
            <span style="background:{{ $prioritas['bg'] }};color:{{ $prioritas['text'] }};border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;white-space:nowrap;">
                {{ $prioritas['label'] }}
            </span>
            @if($isExpiring)
            <span style="background:#fef9c3;color:#854d0e;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;">
                <i class="fas fa-clock"></i> Berakhir {{ $p->tanggal_selesai->diffForHumans() }}
            </span>
            @endif
            <span style="margin-left:auto;font-size:11px;color:#94a3b8;white-space:nowrap;">
                {{ $p->tanggal_mulai->translatedFormat('d F Y') }}
            </span>
        </div>

        {{-- Judul --}}
        <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:8px;line-height:1.4;">
            {{ $p->judul }}
        </div>

        {{-- Isi --}}
        <div style="font-size:13px;color:#475569;line-height:1.7;white-space:pre-wrap;">{{ $p->isi }}</div>

        {{-- Lampiran --}}
        @if($p->lampiran)
        <div style="margin-top:12px;padding-top:12px;border-top:1px dashed #e2e8f0;">
            <a href="{{ asset('storage/'.$p->lampiran) }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:#eff6ff;color:#1d4ed8;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #bfdbfe;">
                <i class="fas fa-paperclip"></i> Lihat Lampiran
            </a>
        </div>
        @endif

        {{-- Footer --}}
        <div style="display:flex;align-items:center;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8;">
            <i class="fas fa-user-edit"></i>
            <span>Diterbitkan oleh {{ $p->penulis?->name ?? 'HRD' }}</span>
            @if($p->tanggal_selesai)
            <span style="margin-left:auto;">Berlaku s/d {{ $p->tanggal_selesai->translatedFormat('d F Y') }}</span>
            @endif
        </div>
    </div>
</div>
@endforeach
</div>

{{-- Pagination --}}
@if($pengumuman->hasPages())
<div style="display:flex;justify-content:center;margin-bottom:20px;">
    {{ $pengumuman->links() }}
</div>
@endif

@endif

</div>
@endsection
