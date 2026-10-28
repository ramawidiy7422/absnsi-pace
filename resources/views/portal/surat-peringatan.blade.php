@extends('layouts.admin')
@section('title', 'Surat Peringatan Saya')
@section('page-title', 'Surat Peringatan Saya')

@section('content')
<div class="card" style="border-radius:14px;overflow:hidden;">
    <div class="card-header" style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;font-size:15px;">
            <i class="fas fa-exclamation-triangle" style="color:#e53e3e;margin-right:8px;"></i>
            Surat Peringatan — {{ $karyawan->nama }}
        </h6>
        <a href="{{ route('portal.profil') }}" style="padding:6px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;">
            <i class="fas fa-arrow-left"></i> Kembali ke Profil
        </a>
    </div>

    @if($sp->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:#a0aec0;">
        <i class="fas fa-check-circle" style="font-size:44px;color:#48bb78;display:block;margin-bottom:14px;"></i>
        <div style="font-size:15px;font-weight:600;color:#276749;">Tidak ada Surat Peringatan</div>
        <div style="font-size:13px;margin-top:4px;">Pertahankan kinerja dan kedisiplinan Anda!</div>
    </div>
    @else
    <div style="padding:0;">
        @foreach($sp as $s)
        @php
            $warna = match($s->type) {
                'SP1' => ['bg'=>'#fffbeb','border'=>'#f6ad55','text'=>'#c05621','label'=>'SP-1'],
                'SP2' => ['bg'=>'#fff5f5','border'=>'#fc8181','text'=>'#c53030','label'=>'SP-2'],
                'SP3' => ['bg'=>'#1a202c','border'=>'#e53e3e','text'=>'#fff5f5','label'=>'SP-3'],
                default=>['bg'=>'#f7fafc','border'=>'#e2e8f0','text'=>'#4a5568','label'=>'SP'],
            };
            $aktif = $s->status === 'aktif';
        @endphp
        <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;border-left:4px solid {{ $warna['border'] }};background:{{ $aktif ? $warna['bg'] : '#f9fafb' }};">
            <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;">
                <span style="background:{{ $warna['border'] }};color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:6px;flex-shrink:0;">
                    {{ $warna['label'] }}
                </span>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $s->description ?: 'Surat Peringatan' }}</div>
                    <div style="font-size:11.5px;color:#a0aec0;margin-top:6px;display:flex;gap:16px;flex-wrap:wrap;">
                        <span><i class="fas fa-calendar"></i> Diterbitkan: {{ $s->date ? \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') : '-' }}</span>
                        @if($s->valid_until)
                        <span><i class="fas fa-clock"></i> Berlaku s/d: {{ \Carbon\Carbon::parse($s->valid_until)->translatedFormat('d F Y') }}</span>
                        @endif
                    </div>
                </div>
                <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:10px;
                    background:{{ $aktif ? '#fed7d7' : '#e2e8f0' }};
                    color:{{ $aktif ? '#c53030' : '#718096' }};flex-shrink:0;">
                    {{ $aktif ? 'Aktif' : 'Selesai' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @if($sp->hasPages())
    <div style="padding:16px 20px;">{{ $sp->links() }}</div>
    @endif
    @endif
</div>
@endsection
