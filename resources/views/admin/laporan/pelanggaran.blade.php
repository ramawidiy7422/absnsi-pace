@extends('layouts.admin')
@section('title','Laporan Pelanggaran & Ketidakhadiran')
@section('page-title','Laporan Pelanggaran & Ketidakhadiran')

@push('styles')
<style>
.pill-filter { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.pill {
    padding:8px 18px; border-radius:999px; font-size:12px; font-weight:700;
    border:2px solid transparent; cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;
}
.pill.active-semua        { background:#2d3748; color:#fff; border-color:#2d3748; }
.pill.active-alpha        { background:#c53030; color:#fff; border-color:#c53030; }
.pill.active-terlambat_tanpa { background:#fc8181; color:#fff; border-color:#fc8181; }
.pill.active-terlambat_izin  { background:#c05621; color:#fff; border-color:#c05621; }
.pill:not(.active-semua):not(.active-alpha):not(.active-terlambat_tanpa):not(.active-terlambat_izin) {
    background:#f7fafc; color:#4a5568; border-color:#e2e8f0;
}
.pill:hover { opacity:0.85; transform:translateY(-1px); }

.summary-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
@media(max-width:700px){ .summary-strip { grid-template-columns:repeat(2,1fr); } }
.scard { border-radius:12px; padding:16px 18px; border:1.5px solid; cursor:pointer; text-decoration:none; display:block; transition:transform 0.15s; }
.scard:hover { transform:translateY(-2px); }
.scard .sv  { font-size:32px; font-weight:900; line-height:1; }
.scard .sl  { font-size:11px; font-weight:600; margin-top:4px; text-transform:uppercase; letter-spacing:0.4px; }
.scard.semua        { background:#f7fafc; border-color:#e2e8f0; } .scard.semua .sv { color:#2d3748; } .scard.semua .sl { color:#718096; }
.scard.alpha        { background:#fff5f5; border-color:#feb2b2; } .scard.alpha .sv { color:#c53030; } .scard.alpha .sl { color:#c53030; }
.scard.tanpa        { background:#fff5f5; border-color:#fc8181; } .scard.tanpa .sv { color:#e53e3e; } .scard.tanpa .sl { color:#e53e3e; }
.scard.izin         { background:#fffaf0; border-color:#fbd38d; } .scard.izin  .sv { color:#c05621; } .scard.izin  .sl { color:#c05621; }

.rank-bar-wrap { background:#f0f2f5; border-radius:4px; height:6px; overflow:hidden; flex:1; }
.rank-bar-fill { height:100%; border-radius:4px; }
</style>
@endpush

@section('content')

{{-- ── Filter ─────────────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#718096;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="min-width:120px;">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $bulan==$m?'selected':''}}>
                            {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#718096;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select">
                    @foreach(range(now()->year, now()->year-3) as $y)
                        <option value="{{ $y }}" {{ $tahun==$y?'selected':''}}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#718096;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select">
                    <option value="">Semua Departemen</option>
                    @foreach($deptList as $d)
                        <option value="{{ $d->id }}" {{ $department_id==$d->id?'selected':''}}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.laporan.pelanggaran') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
            <div style="margin-left:auto;display:flex;gap:8px;">
                @if(auth()->user()->hasPermission('laporan.export'))
                <a href="{{ route('admin.laporan.pelanggaran.pdf', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$department_id,'jenis'=>$jenis]) }}"
                   class="btn" style="background:#c53030;color:white;">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                @endif
                <a href="{{ route('admin.laporan.pelanggaran.csv', ['bulan'=>$bulan,'tahun'=>$tahun,'department_id'=>$department_id,'jenis'=>$jenis]) }}"
                   class="btn" style="background:#276749;color:white;">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                @if(auth()->user()->hasPermission('laporan.export'))
                <a href="{{ route('admin.laporan.pelanggaran.export-excel', ['bulan'=>$bulan,'tahun'=>$tahun,'departemen'=>$department_id,'jenis'=>$jenis]) }}"
                   class="btn" style="background:#1a5c36;color:white;">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Summary Cards (klik untuk filter) ─────────────────────────────── --}}
<div class="summary-strip">
    <a href="{{ route('admin.laporan.pelanggaran', array_merge(request()->query(), ['jenis'=>'semua'])) }}" class="scard semua">
        <div class="sv">{{ $totalSemua }}</div>
        <div class="sl"><i class="fas fa-list"></i> Semua Pelanggaran</div>
        <div style="font-size:10px;color:#a0aec0;margin-top:6px;">{{ $namaBulan }}</div>
    </a>
    <a href="{{ route('admin.laporan.pelanggaran', array_merge(request()->query(), ['jenis'=>'alpha'])) }}" class="scard alpha">
        <div class="sv">{{ $totalAlpha }}</div>
        <div class="sl"><i class="fas fa-times-circle"></i> Alpha / Tanpa Keterangan</div>
        <div style="font-size:10px;color:#fc8181;margin-top:6px;">Tidak hadir & tidak ada izin</div>
    </a>
    <a href="{{ route('admin.laporan.pelanggaran', array_merge(request()->query(), ['jenis'=>'terlambat_tanpa'])) }}" class="scard tanpa">
        <div class="sv">{{ $totalTerlambatTanpa }}</div>
        <div class="sl"><i class="fas fa-clock"></i> Terlambat Tanpa Keterangan</div>
        <div style="font-size:10px;color:#fc8181;margin-top:6px;">Belum isi alasan di portal</div>
    </a>
    <a href="{{ route('admin.laporan.pelanggaran', array_merge(request()->query(), ['jenis'=>'terlambat_izin'])) }}" class="scard izin">
        <div class="sv">{{ $totalTerlambatIzin }}</div>
        <div class="sl"><i class="fas fa-comment-alt"></i> Terlambat Berizin</div>
        <div style="font-size:10px;color:#ed8936;margin-top:6px;">Ada keterangan dari karyawan</div>
    </a>
</div>

{{-- ── Pill Filter aktif ──────────────────────────────────────────────── --}}
<div class="pill-filter">
    @foreach([
        'semua'           => ['Semua','fas fa-list'],
        'alpha'           => ['Alpha','fas fa-times-circle'],
        'terlambat_tanpa' => ['Terlambat Tanpa Keterangan','fas fa-clock'],
        'terlambat_izin'  => ['Terlambat Berizin','fas fa-comment-alt'],
    ] as $key => [$label,$icon])
    <a href="{{ route('admin.laporan.pelanggaran', array_merge(request()->except('jenis','page'), ['jenis'=>$key])) }}"
       class="pill {{ $jenis===$key ? 'active-'.$key : '' }}">
        <i class="{{ $icon }}"></i> {{ $label }}
        @if($key==='semua') <span style="background:rgba(255,255,255,0.25);padding:1px 7px;border-radius:10px;">{{ $totalSemua }}</span>
        @elseif($key==='alpha') <span style="background:rgba(255,255,255,0.25);padding:1px 7px;border-radius:10px;">{{ $totalAlpha }}</span>
        @elseif($key==='terlambat_tanpa') <span style="background:rgba(255,255,255,0.25);padding:1px 7px;border-radius:10px;">{{ $totalTerlambatTanpa }}</span>
        @else <span style="background:rgba(255,255,255,0.25);padding:1px 7px;border-radius:10px;">{{ $totalTerlambatIzin }}</span>
        @endif
    </a>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

    {{-- ── Tabel Detail ──────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-table" style="color:#4299e1;"></i>
                Detail — {{ ['semua'=>'Semua Pelanggaran','alpha'=>'Alpha','terlambat_tanpa'=>'Terlambat Tanpa Keterangan','terlambat_izin'=>'Terlambat Berizin'][$jenis] ?? 'Semua' }}
                <span style="font-size:11px;color:#a0aec0;font-weight:400;margin-left:6px;">{{ $namaBulan }}</span>
            </h6>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Dept.</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Jam Masuk</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detail as $a)
                    <tr>
                        <td style="white-space:nowrap;">
                            <div style="font-weight:600;color:#2d3748;font-size:13px;">{{ $a->tanggal->format('d/m/Y') }}</div>
                            <div style="font-size:11px;color:#a0aec0;">{{ $a->tanggal->translatedFormat('l') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.karyawan.show', $a->karyawan_id) }}"
                               style="font-weight:700;color:#2d3748;text-decoration:none;font-size:13px;">
                                {{ $a->karyawan?->nama ?? '-' }}
                            </a>
                            <div style="font-size:11px;color:#a0aec0;">{{ $a->karyawan?->nik ?? '' }}</div>
                        </td>
                        <td style="font-size:12px;color:#718096;">{{ $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-' }}</td>
                        <td style="text-align:center;">
                            <span class="badge {{ $a->badge_class }}">
                                @if($a->status==='alpha')<i class="fas fa-times-circle"></i> Alpha
                                @elseif($a->has_alasan_terlambat)<i class="fas fa-clock"></i> Terlambat (Berizin)
                                @else<i class="fas fa-clock"></i> Terlambat
                                @endif
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($a->jam_masuk)
                                <span style="font-weight:700;color:#c53030;">{{ substr($a->jam_masuk,0,5) }}</span>
                            @else
                                <span style="color:#a0aec0;">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#718096;max-width:200px;">
                            {{ $a->keterangan_terlambat ?? ($a->keterangan ?? '—') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-check-double" style="font-size:32px;display:block;margin-bottom:10px;color:#48bb78;"></i>
                            <div style="font-weight:600;color:#48bb78;">Tidak ada pelanggaran!</div>
                            <div style="font-size:12px;margin-top:4px;">Periode {{ $namaBulan }}{{ $department_id ? ' — '.($deptList->firstWhere('id', $department_id)?->name ?? '') : '' }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($detail->hasPages())
            <div style="padding:12px 16px;border-top:1px solid #f0f0f0;">
                {{ $detail->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ── Ranking Karyawan ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-trophy" style="color:#c05621;"></i> Ranking Pelanggaran</h6>
            <span style="font-size:11px;color:#a0aec0;">Top 10 bulan ini</span>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($ranking as $i => $r)
            @php $maxTotal = $ranking->first()->total ?: 1; @endphp
            <a href="{{ route('admin.karyawan.show', $r->karyawan) }}#tab-absensi"
               style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;">
                <div style="width:26px;height:26px;border-radius:50%;
                    background:{{ $i===0?'#f6ad55':($i===1?'#cbd5e0':($i===2?'#c6a27a':'#f7fafc')) }};
                    color:{{ $i<3?'#fff':'#718096' }};display:flex;align-items:center;justify-content:center;
                    font-size:11px;font-weight:800;flex-shrink:0;">
                    {{ $i+1 }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#2d3748;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $r->karyawan?->nama ?? '-' }}
                    </div>
                    <div style="font-size:10px;color:#a0aec0;">{{ $r->karyawan?->department?->name ?? $r->karyawan?->departemen ?? '-' }}</div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                        <div class="rank-bar-wrap">
                            <div class="rank-bar-fill" style="width:{{ round($r->total/$maxTotal*100) }}%;background:{{ $r->alpha>0?'#fc8181':'#fbd38d' }};"></div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;">
                        @if($r->alpha > 0)
                        <span style="font-size:10px;background:#fff5f5;color:#c53030;border:1px solid #fed7d7;padding:1px 6px;border-radius:8px;">
                            <i class="fas fa-times-circle"></i> {{ $r->alpha }}x Alpha
                        </span>
                        @endif
                        @if($r->terlambat_tanpa > 0)
                        <span style="font-size:10px;background:#fff5f5;color:#e53e3e;border:1px solid #fed7d7;padding:1px 6px;border-radius:8px;">
                            <i class="fas fa-clock"></i> {{ $r->terlambat_tanpa }}x Terlambat
                        </span>
                        @endif
                        @if($r->terlambat_izin > 0)
                        <span style="font-size:10px;background:#fffaf0;color:#c05621;border:1px solid #fbd38d;padding:1px 6px;border-radius:8px;">
                            <i class="fas fa-comment-alt"></i> {{ $r->terlambat_izin }}x Berizin
                        </span>
                        @endif
                    </div>
                </div>
                <div style="font-size:18px;font-weight:900;color:{{ $r->alpha>0?'#c53030':'#c05621' }};flex-shrink:0;">
                    {{ $r->total }}
                </div>
            </a>
            @empty
            <div style="padding:30px;text-align:center;color:#48bb78;">
                <i class="fas fa-award" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                <div style="font-weight:600;">Tidak ada pelanggaran!</div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
