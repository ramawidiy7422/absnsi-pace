@extends('layouts.admin')
@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-history"></i> Riwayat Semua Pengajuan</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">{{ $karyawan->nama }} &mdash; Tahun {{ $tahun }}</p>
    </div>
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <select name="jenis" class="form-select" style="padding:7px 12px;font-size:13px;background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;" onchange="this.form.submit()">
            <option value="semua"  {{ $jenis=='semua'  ?'selected':'' }} style="color:#2d3748;">Semua Jenis</option>
            <option value="cuti"   {{ $jenis=='cuti'   ?'selected':'' }} style="color:#2d3748;">Cuti</option>
            <option value="izin"   {{ $jenis=='izin'   ?'selected':'' }} style="color:#2d3748;">Izin / Sakit</option>
            <option value="lembur" {{ $jenis=='lembur' ?'selected':'' }} style="color:#2d3748;">Lembur</option>
            <option value="fmk"    {{ $jenis=='fmk'    ?'selected':'' }} style="color:#2d3748;">Izin Keluar (FMK)</option>
        </select>
        <select name="tahun" class="form-select" style="padding:7px 12px;font-size:13px;background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;" onchange="this.form.submit()">
            @foreach($tahunList as $y)
            <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }} style="color:#2d3748;">{{ $y }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Ringkasan status --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px;">
    <div style="background:white;border-radius:12px;padding:14px 18px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#2d3748;">{{ $ringkasan['total'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;text-transform:uppercase;letter-spacing:0.5px;">Total</div>
    </div>
    <div style="background:#fffbeb;border-radius:12px;padding:14px 18px;border:1px solid #fde68a;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c05621;">{{ $ringkasan['pending'] }}</div>
        <div style="font-size:11px;color:#c05621;margin-top:3px;text-transform:uppercase;letter-spacing:0.5px;">Menunggu</div>
    </div>
    <div style="background:#f0fff4;border-radius:12px;padding:14px 18px;border:1px solid #9ae6b4;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#276749;">{{ $ringkasan['disetujui'] }}</div>
        <div style="font-size:11px;color:#276749;margin-top:3px;text-transform:uppercase;letter-spacing:0.5px;">Disetujui</div>
    </div>
    <div style="background:#fff5f5;border-radius:12px;padding:14px 18px;border:1px solid #feb2b2;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c53030;">{{ $ringkasan['ditolak'] }}</div>
        <div style="font-size:11px;color:#c53030;margin-top:3px;text-transform:uppercase;letter-spacing:0.5px;">Ditolak</div>
    </div>
</div>

{{-- Timeline / List --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list-alt" style="color:#2b6cb0;"></i> Daftar Pengajuan
            <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 10px;border-radius:10px;margin-left:8px;font-weight:700;">{{ $semua->count() }} record</span>
        </h6>
    </div>
    <div class="card-body" style="padding:0;">
        @forelse($semua as $item)
        @php
            $statusColor = match($item['status']) {
                'disetujui' => ['#f0fff4','#276749','Disetujui'],
                'ditolak'   => ['#fff5f5','#c53030','Ditolak'],
                default     => ['#fffbeb','#c05621','Menunggu'],
            };
        @endphp
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;gap:14px;align-items:center;"
             onmouseover="this.style.background='#f7fafc'" onmouseout="this.style.background=''">

            {{-- Icon jenis --}}
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $item['warna'][1] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas {{ $item['icon'] }}" style="font-size:15px;color:{{ $item['warna'][0] }};"></i>
            </div>

            {{-- Info --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:11px;font-weight:700;background:{{ $item['warna'][1] }};color:{{ $item['warna'][0] }};padding:2px 9px;border-radius:10px;">
                        {{ $item['jenis'] }}
                    </span>
                    @if($item['nomor'])
                    <span style="font-size:11px;color:#718096;font-family:monospace;">{{ $item['nomor'] }}</span>
                    @endif
                </div>
                <div style="font-size:13px;color:#2d3748;margin-top:4px;font-weight:500;">{{ $item['keterangan'] }}</div>
                <div style="font-size:11px;color:#a0aec0;margin-top:2px;">
                    Diajukan {{ $item['created_at']?->diffForHumans() ?? '-' }}
                </div>
            </div>

            {{-- Tanggal --}}
            <div style="text-align:right;flex-shrink:0;display:none;" class="hide-sm">
                <div style="font-size:12px;font-weight:700;color:#4a5568;">
                    {{ $item['tanggal']?->translatedFormat('d M Y') ?? '-' }}
                </div>
            </div>

            {{-- Status badge --}}
            <div style="flex-shrink:0;">
                <span style="background:{{ $statusColor[0] }};color:{{ $statusColor[1] }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                    {{ $statusColor[2] }}
                </span>
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center;color:#a0aec0;">
            <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:12px;opacity:.4;"></i>
            <div style="font-size:14px;">Tidak ada pengajuan ditemukan untuk filter yang dipilih.</div>
            <div style="font-size:12px;margin-top:6px;">Coba ubah filter tahun atau jenis pengajuan.</div>
        </div>
        @endforelse
    </div>
</div>

@endsection
