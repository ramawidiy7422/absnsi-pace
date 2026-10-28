@extends('layouts.admin')
@section('title', 'Surat & Formulir')
@section('page-title', 'Surat & Formulir')

@section('content')

{{-- STATS MINI --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:24px;">
    @php
        $cards = collect([
            ['icon'=>'fa-briefcase',       'label'=>'Surat Dinas',         'val'=>$totalSpd,  'color'=>'#2b6cb0', 'bg'=>'#ebf8ff', 'route'=>route('admin.surat-dinas.index')],
            $totalFmk !== null  ? ['icon'=>'fa-walking',        'label'=>'Meninggalkan Kerja', 'val'=>$totalFmk,  'color'=>'#c05621', 'bg'=>'#fffbeb', 'route'=>route('admin.formulir.meninggalkan-kerja.index')] : null,
            $totalCuti !== null ? ['icon'=>'fa-umbrella-beach', 'label'=>'Cuti Diajukan',      'val'=>$totalCuti, 'color'=>'#276749', 'bg'=>'#f0fff4', 'route'=>route('admin.leaverequest.index')] : null,
            $totalIzin !== null ? ['icon'=>'fa-file-medical',   'label'=>'Izin / Sakit',       'val'=>$totalIzin, 'color'=>'#6b46c1', 'bg'=>'#faf5ff', 'route'=>route('admin.izin.index')] : null,
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

{{-- 3 KARTU FORMULIR --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">

    {{-- SPD --}}
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-briefcase"></i></div>
            <div style="font-weight:800;font-size:16px;">Surat Perintah Dinas</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Penugasan karyawan ke luar kantor</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                @foreach($spdTerbaru as $spd)
                <div style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:3px 8px;border-radius:6px;font-weight:600;font-family:monospace;">
                    {{ $spd->nomor_spd }}
                </div>
                @endforeach
                @if($totalSpd === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada SPD</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Multi staf per surat<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Nomor otomatis (SPD/MM/YYYY)<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Alur approve SPV → HRD<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Cetak PDF resmi
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.surat-dinas.create') }}" style="flex:1;text-align:center;background:#2b6cb0;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-plus"></i> Buat SPD
            </a>
            <a href="{{ route('admin.surat-dinas.index') }}" style="padding:9px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i>
            </a>
        </div>
    </div>

    {{-- FMK --}}
    @if($totalFmk !== null)
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#c05621,#ed8936);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-walking"></i></div>
            <div style="font-weight:800;font-size:16px;">Formulir Meninggalkan Kerja</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Izin keluar sementara saat jam kerja</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($fmkTerbaru as $fmk)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $fmk->karyawan?->nama }}</span>
                    <span style="font-size:11px;background:{{ $fmk->status==='disetujui'?'#f0fff4':($fmk->status==='ditolak'?'#fff5f5':'#fffbeb') }};color:{{ $fmk->status==='disetujui'?'#276749':($fmk->status==='ditolak'?'#c53030':'#c05621') }};padding:2px 8px;border-radius:8px;font-weight:700;">
                        {{ $fmk->label_status }}
                    </span>
                </div>
                @endforeach
                @if($totalFmk === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada formulir</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Alasan: sakit, keluarga, pribadi, dinas<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Pencatatan jam keluar & kembali<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Approval SPV/Manager<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Cetak PDF
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.formulir.meninggalkan-kerja.create') }}" style="flex:1;text-align:center;background:#c05621;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-plus"></i> Buat FMK
            </a>
            <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}" style="padding:9px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i>
            </a>
        </div>
    </div>
    @endif

    {{-- Cuti & Izin --}}
    @if($cutiIzinTerbaru !== null)
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#276749,#48bb78);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-file-medical-alt"></i></div>
            <div style="font-weight:800;font-size:16px;">Formulir Cuti & Izin</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Cuti tahunan, izin sakit, dan lainnya</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @forelse($cutiIzinTerbaru as $item)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <div>
                        <span style="font-size:12px;font-weight:600;">{{ $item->karyawan?->nama }}</span>
                        <span style="font-size:10px;color:#a0aec0;margin-left:6px;">{{ $item->label }}</span>
                    </div>
                    <span style="font-size:11px;background:{{ $item->badgeBg }};color:{{ $item->badgeColor }};padding:2px 8px;border-radius:8px;font-weight:700;">
                        {{ $item->statusLabel }}
                    </span>
                </div>
                @empty
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada pengajuan</div>
                @endforelse
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Cuti tahunan, sakit, keperluan<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Tracking saldo cuti otomatis<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Bulk approve / reject<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Export PDF & CSV
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            @if($totalCuti !== null)
            <a href="{{ route('admin.leaverequest.index') }}" style="flex:1;text-align:center;background:#276749;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-umbrella-beach"></i> Cuti
            </a>
            @endif
            @if($totalIzin !== null)
            <a href="{{ route('admin.izin.index') }}" style="flex:1;text-align:center;background:#edf2f7;color:#4a5568;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-file-medical"></i> Izin/Sakit
            </a>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- PENDING PERSETUJUAN --}}
@if(($pendingFmk ?? 0) > 0 || ($pendingCuti ?? 0) > 0 || ($pendingIzin ?? 0) > 0)
<div style="margin-top:24px;background:white;border-radius:14px;border:1px solid #fed7aa;overflow:hidden;">
    <div style="padding:14px 20px;background:#fffbeb;border-bottom:1px solid #fed7aa;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-bell" style="color:#c05621;"></i>
        <span style="font-weight:700;font-size:14px;color:#c05621;">Menunggu Persetujuan</span>
    </div>
    <div style="padding:16px 20px;display:flex;gap:16px;flex-wrap:wrap;">
        @if(($pendingFmk ?? 0) > 0)
        <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}?status=menunggu" style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:#fffbeb;border-radius:10px;text-decoration:none;border:1px solid #f6ad55;">
            <span style="font-size:20px;font-weight:800;color:#c05621;">{{ $pendingFmk }}</span>
            <span style="font-size:13px;color:#c05621;font-weight:600;">FMK menunggu</span>
        </a>
        @endif
        @if(($pendingCuti ?? 0) > 0)
        <a href="{{ route('admin.leaverequest.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:#f0fff4;border-radius:10px;text-decoration:none;border:1px solid #9ae6b4;">
            <span style="font-size:20px;font-weight:800;color:#276749;">{{ $pendingCuti }}</span>
            <span style="font-size:13px;color:#276749;font-weight:600;">Cuti menunggu</span>
        </a>
        @endif
        @if(($pendingIzin ?? 0) > 0)
        <a href="{{ route('admin.izin.index') }}?status=pending" style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:#f0fff4;border-radius:10px;text-decoration:none;border:1px solid #9ae6b4;">
            <span style="font-size:20px;font-weight:800;color:#276749;">{{ $pendingIzin }}</span>
            <span style="font-size:13px;color:#276749;font-weight:600;">Izin/Sakit menunggu</span>
        </a>
        @endif
    </div>
</div>
@endif

@endsection
