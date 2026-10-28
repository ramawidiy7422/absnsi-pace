@extends('layouts.admin')
@section('title', 'Operasional Absensi')
@section('page-title', 'Operasional Absensi')

@section('content')

{{-- STATS MINI --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:24px;">
    @php
        $cards = collect([
            $totalAbsensiHariIni !== null ? ['icon'=>'fa-clipboard-list', 'label'=>'Absensi Hari Ini', 'val'=>$totalAbsensiHariIni, 'color'=>'#2b6cb0', 'bg'=>'#ebf8ff', 'route'=>route('admin.absensi.index')] : null,
            $totalBelumAbsen !== null ? ['icon'=>'fa-user-times', 'label'=>'Belum Absen', 'val'=>$totalBelumAbsen, 'color'=>'#c05621', 'bg'=>'#fffbeb', 'route'=>route('admin.absensi.belum-absen')] : null,
            $alphaBulanIni !== null ? ['icon'=>'fa-user-slash', 'label'=>'Alpha Bulan Ini', 'val'=>$alphaBulanIni, 'color'=>'#c53030', 'bg'=>'#fff5f5', 'route'=>route('admin.absensi.alpha-generator')] : null,
        ])->filter();
    @endphp
    @foreach($cards as $c)
    <a href="{{ $c['route'] }}" style="background:{{ $c['bg'] }};border-radius:14px;padding:18px 16px;text-decoration:none;display:flex;align-items:center;gap:12px;border:1px solid transparent;transition:box-shadow 0.2s;"
        onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.10)'" onmouseout="this.style.boxShadow=''">
        <div style="width:42px;height:42px;border-radius:10px;background:{{ $c['color'] }};display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0;">
            <i class="fas {{ $c['icon'] }}" style="font-size:16px;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['val'] }}</div>
            <div style="font-size:11px;color:#718096;margin-top:1px;line-height:1.3;">{{ $c['label'] }}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- KARTU OPERASIONAL --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">

    @if($totalAbsensiHariIni !== null)
    {{-- Data Absensi --}}
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-clipboard-list"></i></div>
            <div style="font-weight:800;font-size:16px;">Data Absensi</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Riwayat absensi harian karyawan</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($absensiTerbaru as $a)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $a->karyawan?->nama }}</span>
                    <span style="font-size:11px;background:{{ $a->status==='hadir'?'#f0fff4':($a->status==='terlambat'?'#fffbeb':'#f7fafc') }};color:{{ $a->status==='hadir'?'#276749':($a->status==='terlambat'?'#c05621':'#718096') }};padding:2px 8px;border-radius:8px;font-weight:700;">
                        {{ ucfirst($a->status) }}
                    </span>
                </div>
                @endforeach
                @if($totalAbsensiHariIni === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada absensi hari ini</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Filter tanggal, status, departemen<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Input absensi manual
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.absensi.index') }}" style="flex:1;text-align:center;background:#2b6cb0;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i> Lihat Semua
            </a>
            @if(auth()->user()->hasPermission('absensi.tambah'))
            <a href="{{ route('admin.absensi.create') }}" style="padding:9px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-plus"></i>
            </a>
            @endif
        </div>
    </div>
    @endif

    @if($totalBelumAbsen !== null)
    {{-- Belum Absen --}}
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#c05621,#ed8936);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-user-times"></i></div>
            <div style="font-weight:800;font-size:16px;">Belum Absen</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Karyawan yang belum tercatat hari ini</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($belumAbsenTerbaru as $k)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $k->nama }}</span>
                    <span style="font-size:10px;color:#a0aec0;">{{ $k->department?->name ?? $k->departemen ?? '-' }}</span>
                </div>
                @endforeach
                @if($totalBelumAbsen === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Semua karyawan sudah absen</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Filter departemen & pencarian<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Absen massal langsung dari sini
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.absensi.belum-absen') }}" style="flex:1;text-align:center;background:#c05621;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i> Lihat Semua
            </a>
        </div>
    </div>
    @endif

    @if($alphaBulanIni !== null)
    {{-- Alpha Generator --}}
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#c53030,#e53e3e);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-user-slash"></i></div>
            <div style="font-weight:800;font-size:16px;">Alpha Generator</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Tandai otomatis karyawan tidak hadir</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                <div style="font-size:12px;color:#4a5568;">
                    <strong>{{ $alphaBulanIni }}</strong> karyawan alpha bulan ini
                </div>
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Preview sebelum generate<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Riwayat log generate
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.absensi.alpha-generator') }}" style="flex:1;text-align:center;background:#c53030;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-cog"></i> Buka Alpha Generator
            </a>
        </div>
    </div>
    @endif

</div>

@endsection
