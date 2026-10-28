@extends('layouts.admin')
@section('title', 'Approval Saya')
@section('page-title', 'Approval Saya')

@section('content')

@if(session('success'))
<div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#276749;font-weight:600;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fff5f5;border:1px solid #fed7d7;border-radius:10px;padding:12px 18px;margin-bottom:16px;color:#c53030;font-weight:600;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;">
    <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-inbox"></i> Kotak Masuk Approval Saya</h4>
    <p style="margin:4px 0 0;opacity:.85;font-size:13px;">
        {{ $total }} pengajuan menunggu persetujuan Anda saat ini.
    </p>
</div>

@if($total === 0)
<div class="card" style="padding:60px 20px;text-align:center;color:#a0aec0;">
    <i class="fas fa-check-circle" style="font-size:44px;color:#48bb78;display:block;margin-bottom:14px;"></i>
    <div style="font-size:15px;font-weight:600;color:#276749;">Semua sudah diproses</div>
    <div style="font-size:13px;margin-top:4px;">Tidak ada pengajuan yang menunggu persetujuan Anda.</div>
</div>
@endif

{{-- Cuti --}}
@if($cuti->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#276749,#48bb78);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-umbrella-beach"></i> Pengajuan Cuti</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $cuti->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.leaverequest.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $cuti->count() }} pengajuan cuti ini?')">
                @csrf
                @foreach($cuti as $lr)<input type="hidden" name="ids[]" value="{{ $lr->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.leaverequest.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $cuti->count() }} pengajuan cuti ini?')">
                @csrf
                @foreach($cuti as $lr)<input type="hidden" name="ids[]" value="{{ $lr->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($cuti as $lr)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $lr->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $lr->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $lr->start_date?->translatedFormat('d M Y') }} s/d {{ $lr->end_date?->translatedFormat('d M Y') }}
                    &bull; {{ $lr->type ?? 'Cuti' }}
                </div>
                <div style="font-size:11px;color:#c05621;margin-top:4px;font-weight:600;">
                    {{ $lr->status === 'pending_spv' ? 'Menunggu Anda (SPV)' : 'Menunggu Anda (Manajer)' }}
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.leaverequest.approve', $lr) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.leaverequest.reject', $lr) }}" onsubmit="return confirm('Tolak pengajuan cuti ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
                <a href="{{ route('admin.karyawan.show', $lr->karyawan_id) }}#tab-ketidakhadiran" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- FMK --}}
@if($fmk->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#c05621,#ed8936);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-door-open"></i> Izin Keluar (FMK)</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $fmk->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $fmk->count() }} FMK ini?')">
                @csrf
                @foreach($fmk as $f)<input type="hidden" name="ids[]" value="{{ $f->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $fmk->count() }} FMK ini?')">
                @csrf
                @foreach($fmk as $f)<input type="hidden" name="ids[]" value="{{ $f->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($fmk as $f)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $f->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $f->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $f->tanggal?->translatedFormat('d M Y') }}
                    &bull; Rencana pukul {{ substr($f->jam_keluar,0,5) }}
                </div>
                <div style="font-size:11px;color:#c05621;margin-top:4px;font-weight:600;">
                    {{ $f->status === 'pending_spv' ? 'Menunggu Anda (SPV)' : 'Menunggu Anda (Manajer)' }}
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.approve', $f) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.tolak', $f) }}" onsubmit="return confirm('Tolak pengajuan FMK ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
                <a href="{{ route('admin.formulir.meninggalkan-kerja.show', $f) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Izin Terlambat --}}
@if($izinTerlambat->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#805ad5,#b794f4);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-clock"></i> Izin Terlambat (via WA)</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $izinTerlambat->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.izin-terlambat.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $izinTerlambat->count() }} izin terlambat ini?')">
                @csrf
                @foreach($izinTerlambat as $it)<input type="hidden" name="ids[]" value="{{ $it->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.izin-terlambat.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $izinTerlambat->count() }} izin terlambat ini?')">
                @csrf
                @foreach($izinTerlambat as $it)<input type="hidden" name="ids[]" value="{{ $it->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($izinTerlambat as $it)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $it->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $it->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $it->tanggal?->translatedFormat('d M Y') }}
                </div>
                <div style="font-size:11.5px;color:#718096;margin-top:2px;">{{ $it->keterangan }}</div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.izin-terlambat.approve', $it) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.izin-terlambat.reject', $it) }}" onsubmit="return confirm('Tolak izin terlambat ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Izin Pulang Cepat --}}
@if($izinPulangCepat->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#b7791f,#d69e2e);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-sign-out-alt"></i> Izin Pulang Cepat</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $izinPulangCepat->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.izin-pulang-cepat.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $izinPulangCepat->count() }} izin pulang cepat ini?')">
                @csrf
                @foreach($izinPulangCepat as $it)<input type="hidden" name="ids[]" value="{{ $it->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.izin-pulang-cepat.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $izinPulangCepat->count() }} izin pulang cepat ini?')">
                @csrf
                @foreach($izinPulangCepat as $it)<input type="hidden" name="ids[]" value="{{ $it->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($izinPulangCepat as $it)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $it->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $it->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $it->tanggal?->translatedFormat('d M Y') }}
                </div>
                <div style="font-size:11.5px;color:#718096;margin-top:2px;">{{ $it->keterangan }}</div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.izin-pulang-cepat.approve', $it) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.izin-pulang-cepat.reject', $it) }}" onsubmit="return confirm('Tolak izin pulang cepat ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Lembur --}}
@if($lembur->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#b7791f,#ecc94b);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-business-time"></i> Pengajuan Lembur</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $lembur->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.lemburan.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $lembur->count() }} pengajuan lembur ini?')">
                @csrf
                @foreach($lembur as $l)<input type="hidden" name="ids[]" value="{{ $l->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.lemburan.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $lembur->count() }} pengajuan lembur ini?')">
                @csrf
                @foreach($lembur as $l)<input type="hidden" name="ids[]" value="{{ $l->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($lembur as $l)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $l->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $l->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $l->tanggal?->translatedFormat('d M Y') }}
                    &bull; {{ substr($l->jam_mulai,0,5) }}&ndash;{{ substr($l->jam_selesai,0,5) }}
                    ({{ $l->total_jam }} jam)
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.lemburan.approve', $l) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.lemburan.reject', $l) }}" onsubmit="return confirm('Tolak pengajuan lembur ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Izin / Sakit --}}
@if($izin->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="background:linear-gradient(135deg,#2b6cb0,#63b3ed);color:white;flex-wrap:wrap;gap:8px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-file-medical"></i> Izin / Sakit</h6>
        <span style="font-size:11px;background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">{{ $izin->count() }}</span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <form method="POST" action="{{ route('admin.izin.bulk-approve') }}" onsubmit="return confirm('Setujui semua {{ $izin->count() }} izin/sakit ini?')">
                @csrf
                @foreach($izin as $i)<input type="hidden" name="ids[]" value="{{ $i->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);"><i class="fas fa-check-double"></i> Setujui Semua</button>
            </form>
            <form method="POST" action="{{ route('admin.izin.bulk-reject') }}" onsubmit="return confirm('Tolak semua {{ $izin->count() }} izin/sakit ini?')">
                @csrf
                @foreach($izin as $i)<input type="hidden" name="ids[]" value="{{ $i->id }}">@endforeach
                <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.15);color:white;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-ban"></i> Tolak Semua</button>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach($izin as $i)
        <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:13.5px;color:#2d3748;">{{ $i->karyawan?->nama }}</div>
                <div style="font-size:11.5px;color:#a0aec0;margin-top:2px;">
                    {{ $i->karyawan?->department?->name ?? '-' }} &bull;
                    {{ $i->tanggal?->translatedFormat('d M Y') }}
                    &bull; {{ $i->label_jenis }}
                </div>
                @if($i->keterangan)
                <div style="font-size:11.5px;color:#718096;margin-top:2px;">{{ $i->keterangan }}</div>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin.izin.approve', $i) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.izin.reject', $i) }}" onsubmit="return confirm('Tolak izin ini?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
