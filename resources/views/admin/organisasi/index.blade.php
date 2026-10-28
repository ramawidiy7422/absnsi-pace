@extends('layouts.admin')
@section('title', 'Organisasi')
@section('page-title', 'Organisasi')

@section('content')

{{-- STATS MINI --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:24px;">
    @php
        $cards = collect([
            $totalDepartment !== null ? ['icon'=>'fa-building', 'label'=>'Departemen', 'val'=>$totalDepartment, 'color'=>'#2b6cb0', 'bg'=>'#ebf8ff', 'route'=>route('admin.department.index')] : null,
            $totalDevision !== null   ? ['icon'=>'fa-sitemap',   'label'=>'Divisi',     'val'=>$totalDevision,   'color'=>'#276749', 'bg'=>'#f0fff4', 'route'=>route('admin.devision.index')] : null,
            $totalPosition !== null   ? ['icon'=>'fa-id-badge',  'label'=>'Jabatan',    'val'=>$totalPosition,   'color'=>'#805ad5', 'bg'=>'#faf5ff', 'route'=>route('admin.position.index')] : null,
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

{{-- 3 KARTU ORGANISASI --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">

    {{-- Departemen --}}
    @if($totalDepartment !== null)
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-building"></i></div>
            <div style="font-weight:800;font-size:16px;">Departemen</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Unit organisasi & approval cuti/izin</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($departmentTerbaru as $dept)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $dept->name }}</span>
                    <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 8px;border-radius:8px;font-weight:700;">
                        {{ $dept->devisions_count }} divisi
                    </span>
                </div>
                @endforeach
                @if($totalDepartment === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada departemen</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Unit besar organisasi<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Atur SPV & Manajer approval<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Menaungi beberapa Divisi
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.department.index') }}" style="flex:1;text-align:center;background:#2b6cb0;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i> Kelola Departemen
            </a>
        </div>
    </div>
    @endif

    {{-- Divisi --}}
    @if($totalDevision !== null)
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#276749,#48bb78);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-sitemap"></i></div>
            <div style="font-weight:800;font-size:16px;">Divisi</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Sub-unit di dalam Departemen</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($devisionTerbaru as $dev)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $dev->name }}</span>
                    <span style="font-size:10px;color:#a0aec0;">{{ $dev->department?->name ?? '-' }}</span>
                </div>
                @endforeach
                @if($totalDevision === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada divisi</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Sub-bagian dari Departemen<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Untuk pengelompokan lebih detail
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.devision.index') }}" style="flex:1;text-align:center;background:#276749;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i> Kelola Divisi
            </a>
        </div>
    </div>
    @endif

    {{-- Jabatan --}}
    @if($totalPosition !== null)
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#6b46c1,#9f7aea);padding:20px 22px;color:white;">
            <div style="font-size:28px;margin-bottom:6px;"><i class="fas fa-id-badge"></i></div>
            <div style="font-weight:800;font-size:16px;">Jabatan</div>
            <div style="font-size:12px;opacity:0.8;margin-top:4px;">Posisi kerja & salary grade</div>
        </div>
        <div style="padding:16px 22px;flex:1;">
            <div style="margin-bottom:12px;">
                @foreach($positionTerbaru as $pos)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f7fafc;">
                    <span style="font-size:12px;font-weight:600;">{{ $pos->name }}</span>
                    <span style="font-size:10px;color:#a0aec0;">{{ $pos->level ?? '-' }}</span>
                </div>
                @endforeach
                @if($totalPosition === 0)
                <div style="font-size:12px;color:#a0aec0;font-style:italic;">Belum ada jabatan</div>
                @endif
            </div>
            <div style="font-size:12px;color:#718096;line-height:1.7;">
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Berdiri sendiri (lintas departemen)<br>
                <i class="fas fa-check-circle" style="color:#48bb78;"></i> Level & salary grade
            </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;gap:8px;">
            <a href="{{ route('admin.position.index') }}" style="flex:1;text-align:center;background:#6b46c1;color:white;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-list"></i> Kelola Jabatan
            </a>
        </div>
    </div>
    @endif

    @if($totalDepartment === null && $totalDevision === null && $totalPosition === null)
    <div class="card" style="padding:40px;text-align:center;color:#a0aec0;">
        Anda tidak memiliki akses untuk melihat data organisasi.
    </div>
    @endif

</div>

@endsection
