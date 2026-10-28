@extends('layouts.admin')
@section('title', 'Detail SPD — '.$suratDinas->nomor_spd)
@section('page-title', 'Detail Surat Perintah Dinas')

@section('content')
@php
    $statusColor = match($suratDinas->status) {
        'disetujui' => ['bg'=>'#f0fff4','text'=>'#276749','border'=>'#9ae6b4'],
        'ditolak'   => ['bg'=>'#fff5f5','text'=>'#c53030','border'=>'#fc8181'],
        default     => ['bg'=>'#fffbeb','text'=>'#c05621','border'=>'#f6ad55'],
    };
@endphp

<div style="max-width:860px;margin:0 auto;">

{{-- Top action bar --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
    <a href="{{ route('admin.surat-dinas.index') }}" style="color:#718096;text-decoration:none;font-size:13px;">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar SPD
    </a>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($suratDinas->status === 'draft')
        <form method="POST" action="{{ route('admin.surat-dinas.approve', $suratDinas) }}" style="margin:0;">
            @csrf
            <button class="btn btn-sm" style="background:#f0fff4;color:#276749;border:1px solid #9ae6b4;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-check"></i> Setujui
            </button>
        </form>
        <form method="POST" action="{{ route('admin.surat-dinas.tolak', $suratDinas) }}" style="margin:0;">
            @csrf
            <button class="btn btn-sm" style="background:#fff5f5;color:#c53030;border:1px solid #fc8181;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-times"></i> Tolak
            </button>
        </form>
        @endif
        <a href="{{ route('admin.surat-dinas.edit', $suratDinas) }}" class="btn btn-sm" style="background:#fffbeb;color:#c05621;border:1px solid #f6ad55;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.surat-dinas.pdf', $suratDinas) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>
    </div>
</div>

{{-- Header --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:14px;padding:24px 28px;margin-bottom:16px;color:white;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;background:rgba(255,255,255,0.08);border-radius:50%;"></div>
    <div style="font-size:11px;opacity:0.8;font-weight:600;letter-spacing:1px;">SURAT PERINTAH DINAS</div>
    <div style="font-size:22px;font-weight:800;font-family:monospace;margin:6px 0;">{{ $suratDinas->nomor_spd }}</div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:6px;">
        <span style="opacity:0.8;font-size:13px;">
            <i class="fas fa-calendar"></i> {{ $suratDinas->tanggal_dibuat->translatedFormat('d F Y') }}
        </span>
        <span style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};font-size:12px;font-weight:700;padding:3px 12px;border-radius:12px;">
            {{ $suratDinas->label_status }}
        </span>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- Info Dinas --}}
    <div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:20px;">
        <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.5px;">
            <i class="fas fa-briefcase" style="color:#4299e1;margin-right:6px;"></i>Info Dinas
        </div>
        @foreach([
            ['Departemen',  $suratDinas->department?->name ?? '—'],
            ['Keperluan',   $suratDinas->keperluan],
            ['Kota Tujuan', $suratDinas->kota_tujuan],
            ['Lokasi',      $suratDinas->lokasi_tujuan],
        ] as [$l,$v])
        <div style="margin-bottom:10px;">
            <div style="font-size:11px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.4px;">{{ $l }}</div>
            <div style="font-size:13px;font-weight:600;color:#2d3748;margin-top:2px;">{{ $v }}</div>
        </div>
        @endforeach
    </div>

    {{-- Jadwal & Kendaraan --}}
    <div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:20px;">
        <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.5px;">
            <i class="fas fa-route" style="color:#4299e1;margin-right:6px;"></i>Jadwal & Kendaraan
        </div>
        @foreach([
            ['Berangkat',   $suratDinas->tanggal_berangkat->translatedFormat('d F Y') . ($suratDinas->jam_berangkat ? ' pukul '.substr($suratDinas->jam_berangkat,0,5) : '')],
            ['Kembali',     $suratDinas->tanggal_kembali->translatedFormat('d F Y')],
            ['Durasi',      $suratDinas->tanggal_berangkat->diffInDays($suratDinas->tanggal_kembali)+1 . ' hari'],
            ['Kendaraan',   $suratDinas->label_kendaraan],
            ['No. Kendaraan', $suratDinas->nomor_kendaraan ?: '—'],
        ] as [$l,$v])
        <div style="margin-bottom:10px;">
            <div style="font-size:11px;color:#a0aec0;text-transform:uppercase;letter-spacing:0.4px;">{{ $l }}</div>
            <div style="font-size:13px;font-weight:600;color:#2d3748;margin-top:2px;">{{ $v }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Staf Bertugas --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:20px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-users" style="color:#4299e1;margin-right:6px;"></i>Staf yang Bertugas ({{ $suratDinas->staf->count() }} orang)
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
        @foreach($suratDinas->staf as $s)
        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                {{ strtoupper(substr($s->nama,0,1)) }}
            </div>
            <div>
                <div style="font-size:13px;font-weight:600;color:#2d3748;">{{ $s->nama }}</div>
                <div style="font-size:11px;color:#a0aec0;">{{ $s->department?->name ?? '—' }}</div>
                <div style="font-size:11px;color:#a0aec0;">{{ $s->position?->name ?? $s->jabatan ?? '—' }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Persetujuan --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:20px;margin-bottom:24px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-signature" style="color:#4299e1;margin-right:6px;"></i>Persetujuan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:11px;color:#a0aec0;margin-bottom:10px;">YANG MEMBERI TUGAS</div>
            <div style="height:50px;border-bottom:1px solid #e2e8f0;margin-bottom:10px;"></div>
            <div style="font-size:13px;font-weight:700;color:#2d3748;">{{ $suratDinas->pemberi_tugas_nama ?: '—' }}</div>
            <div style="font-size:11px;color:#718096;">{{ $suratDinas->pemberi_tugas_jabatan ?: 'SPV / Manager' }}</div>
        </div>
        <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:11px;color:#a0aec0;margin-bottom:10px;">MENGETAHUI HRD</div>
            <div style="height:50px;border-bottom:1px solid #e2e8f0;margin-bottom:10px;"></div>
            <div style="font-size:13px;font-weight:700;color:#2d3748;">{{ $suratDinas->hrd_nama ?: '—' }}</div>
            <div style="font-size:11px;color:#718096;">HRD</div>
        </div>
    </div>
    @if($suratDinas->catatan)
    <div style="margin-top:14px;padding:12px 14px;background:#fffbeb;border-radius:8px;border-left:3px solid #f6ad55;">
        <div style="font-size:11px;color:#c05621;font-weight:600;margin-bottom:4px;">CATATAN</div>
        <div style="font-size:13px;color:#2d3748;">{{ $suratDinas->catatan }}</div>
    </div>
    @endif
</div>

</div>
@endsection
