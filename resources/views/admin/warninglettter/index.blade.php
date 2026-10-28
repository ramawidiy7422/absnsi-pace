@extends('layouts.admin')
@section('title', 'Surat Peringatan')
@section('page-title', 'Surat Peringatan')

@section('content')


{{-- Header --}}
<div style="background:linear-gradient(135deg,#7b341e,#c05621);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-exclamation-triangle"></i> Surat Peringatan</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">SP1, SP2, SP3 seluruh karyawan</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($stats['aktif'] > 0)
        <span style="background:rgba(255,100,50,0.35);border:1px solid rgba(255,150,100,0.5);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="fas fa-clock"></i> {{ $stats['aktif'] }} aktif tahun {{ $tahun }}
        </span>
        @endif
        @if(auth()->user()->hasPermission('laporan.export'))
        <a href="{{ route('admin.warninglettter.export-csv', request()->query()) }}"
           style="background:rgba(255,255,255,0.2);color:white;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        @endif
    </div>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
    <div style="background:white;border-radius:10px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#4a5568;">{{ $stats['total'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Total {{ $tahun }}</div>
    </div>
    <div style="background:#fff5f5;border-radius:10px;padding:16px;border:1px solid #fed7d7;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c53030;">{{ $stats['sp1'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">SP-1</div>
    </div>
    <div style="background:#fffaf0;border-radius:10px;padding:16px;border:1px solid #fbd38d;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c05621;">{{ $stats['sp2'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">SP-2</div>
    </div>
    <div style="background:#fff5f5;border-radius:10px;padding:16px;border:1px solid #fc8181;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#9b2c2c;">{{ $stats['sp3'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">SP-3</div>
    </div>
    <div style="background:#fff5f5;border-radius:10px;padding:16px;border:1px solid #fbb6ce;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#97266d;">{{ $stats['aktif'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Aktif</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Jenis SP</label>
                <select name="type" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="SP1" {{ $type=='SP1'?'selected':'' }}>SP-1</option>
                    <option value="SP2" {{ $type=='SP2'?'selected':'' }}>SP-2</option>
                    <option value="SP3" {{ $type=='SP3'?'selected':'' }}>SP-3</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="aktif"   {{ $status=='aktif'   ?'selected':'' }}>Aktif</option>
                    <option value="selesai" {{ $status=='selesai' ?'selected':'' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($deptList as $d)
                    <option value="{{ $d->id }}" {{ $department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="padding:8px 12px;font-size:13px;">
                    @for($y=now()->year;$y>=now()->year-4;$y--)
                    <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.warninglettter.index') }}" class="btn btn-secondary" style="padding:8px 16px;">Reset</a>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="table" style="margin:0;">
                <thead>
                    <tr style="background:#f7fafc;">
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Karyawan</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Jenis</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Tanggal</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Berlaku s.d.</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Keterangan</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Status</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spList as $sp)
                    @php
                        $typeColor = match($sp->type) {
                            'SP1' => ['bg'=>'#fff5f5','color'=>'#c53030','border'=>'#fed7d7'],
                            'SP2' => ['bg'=>'#fffaf0','color'=>'#c05621','border'=>'#fbd38d'],
                            'SP3' => ['bg'=>'#fff5f5','color'=>'#9b2c2c','border'=>'#fc8181'],
                            default => ['bg'=>'#f7fafc','color'=>'#4a5568','border'=>'#e2e8f0'],
                        };
                        $isAktif = $sp->status === 'aktif';
                    @endphp
                    <tr>
                        <td style="padding:10px 14px;">
                            <div style="font-weight:600;font-size:13px;">{{ $sp->karyawan?->nama ?? '-' }}</div>
                            <div style="font-size:11px;color:#718096;">
                                {{ $sp->karyawan?->nik ?? '' }}
                                @if($sp->karyawan?->department)&middot; {{ $sp->karyawan->department->name }}@endif
                            </div>
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="background:{{ $typeColor['bg'] }};color:{{ $typeColor['color'] }};border:1px solid {{ $typeColor['border'] }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:800;">
                                {{ $sp->type }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;font-size:12px;white-space:nowrap;">{{ $sp->date?->format('d/m/Y') ?? '-' }}</td>
                        <td style="padding:10px 14px;font-size:12px;white-space:nowrap;">
                            @if($sp->valid_until)
                                @if($sp->valid_until->isPast() && $isAktif)
                                <span style="color:#c53030;">{{ $sp->valid_until->format('d/m/Y') }} <small>(lewat)</small></span>
                                @else
                                {{ $sp->valid_until->format('d/m/Y') }}
                                @endif
                            @else
                                <span style="color:#a0aec0;">-</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:12px;color:#4a5568;max-width:220px;">
                            {{ $sp->description ? \Str::limit($sp->description, 80) : '-' }}
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="background:{{ $isAktif ? '#fff5f5' : '#f0fff4' }};color:{{ $isAktif ? '#c53030' : '#276749' }};border:1px solid {{ $isAktif ? '#fed7d7' : '#9ae6b4' }};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ $isAktif ? 'Aktif' : 'Selesai' }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;">
                            @if($sp->karyawan_id)
                            <a href="{{ route('admin.karyawan.show', $sp->karyawan_id) }}#tab-warninglettter"
                               class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @if(auth()->user()->hasPermission('laporan.export'))
                            <a href="{{ route('admin.warninglettter.export-pdf', $sp->karyawan_id) }}"
                               class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-top:3px;" title="Export semua SP karyawan ini ke PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-exclamation-triangle" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                            Tidak ada surat peringatan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($spList->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #f0f0f0;">
            {{ $spList->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
