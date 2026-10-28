@extends('layouts.admin')
@section('title','Absensi Saya')
@section('page-title','Absensi Saya')

@push('styles')
<style>
.tab-nav {
    display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px; overflow-x: auto; flex-wrap: nowrap;
}
.tab-btn {
    padding: 10px 16px; border: none; background: none; cursor: pointer;
    font-size: 13px; font-weight: 500; color: #718096;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    white-space: nowrap; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px 6px 0 0; text-decoration: none;
}
.tab-btn:hover  { color: #4299e1; background: #f7fafc; }
.tab-btn.active { color: #4299e1; border-bottom-color: #4299e1; background: #ebf8ff; font-weight: 600; }
.tab-content    { display: none; }
.tab-content.active { display: block; }
</style>
@endpush

@section('content')
@php
    $wajibAlasan = \App\Models\Pengaturan::get('alasan_terlambat_wajib', '1') === '1';
    $absensiTerlambatTanpaAlasan = \App\Models\Absensi::where('karyawan_id', $karyawan->id)
        ->where('status', 'terlambat')
        ->whereNull('keterangan_terlambat')
        ->whereMonth('tanggal', request('bulan', now()->month))
        ->whereYear('tanggal', request('tahun', now()->year))
        ->get();
@endphp

{{-- Banner reminder alasan terlambat --}}
@if($wajibAlasan && $absensiTerlambatTanpaAlasan->count() > 0)
<div style="background:#fff7ed;border:1px solid #fb923c;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:flex-start;gap:14px;">
    <div style="font-size:22px;line-height:1;">⚠️</div>
    <div style="flex:1;">
        <div style="font-weight:700;color:#c2410c;font-size:14px;">Anda memiliki {{ $absensiTerlambatTanpaAlasan->count() }} keterlambatan yang belum diisi alasannya</div>
        <div style="font-size:12px;color:#9a3412;margin-top:4px;">Sistem mengharuskan setiap keterlambatan memiliki alasan. Silakan isi melalui tombol di bawah.</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
            @foreach($absensiTerlambatTanpaAlasan as $atl)
            <button onclick="bukaBuatAlasan({{ $atl->id }}, '{{ $atl->tanggal->format('d M Y') }}', '{{ substr($atl->jam_masuk,0,5) }}')"
                style="background:#fed7aa;border:1px solid #fb923c;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;color:#7c2d12;cursor:pointer;">
                <i class="fas fa-clock"></i> {{ $atl->tanggal->format('d M') }} ({{ substr($atl->jam_masuk,0,5) }})
            </button>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Modal isi alasan terlambat --}}
<div id="modal-alasan" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:24px;width:90%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="font-weight:700;font-size:16px;color:#2d3748;margin-bottom:4px;"><i class="fas fa-clock" style="color:#e53e3e;"></i> Isi Alasan Keterlambatan</div>
        <div id="modal-alasan-info" style="font-size:12px;color:#718096;margin-bottom:16px;"></div>
        <form method="POST" action="{{ route('portal.absensi.alasan-terlambat') }}">
            @csrf
            <input type="hidden" name="absensi_id" id="modal-absensi-id">
            <div class="form-group">
                <label style="font-size:13px;font-weight:600;">Alasan Keterlambatan <span style="color:#e53e3e;">*</span></label>
                <textarea name="keterangan_terlambat" class="form-control" rows="3" required maxlength="500"
                    placeholder="Contoh: Macet di jalan tol, ban bocor, keluarga sakit..."></textarea>
                <div style="font-size:11px;color:#a0aec0;margin-top:4px;">Maks. 500 karakter. Alasan ini akan dilihat oleh HRD/supervisor Anda.</div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#e53e3e;border-color:#e53e3e;">
                    <i class="fas fa-save"></i> Simpan Alasan
                </button>
                <button type="button" onclick="tutupModal()" style="flex:1;background:#f7fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;cursor:pointer;font-weight:600;color:#4a5568;">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn" data-tab="tab-harian" onclick="switchTab('tab-harian')">
        <i class="fas fa-calendar-check"></i> Absensi Harian
    </button>
    <button class="tab-btn" data-tab="tab-rekap" onclick="switchTab('tab-rekap')">
        <i class="fas fa-chart-bar"></i> Rekap Kehadiran
    </button>
</div>

{{-- ══ TAB: ABSENSI HARIAN (kalender + tabel per bulan) ══ --}}
<div class="tab-content" id="tab-harian">
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-calendar-check" style="color:#4299e1;"></i> Riwayat Absensi — {{ $karyawan->nama }}</h6>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="bulan" class="form-select" style="width:auto;" onchange="this.form.submit()">
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-select" style="width:auto;" onchange="this.form.submit()">
                    @foreach(range(now()->year, now()->year-2) as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('portal.slip-pdf', ['bulan'=>$bulan,'tahun'=>$tahun]) }}"
               target="_blank"
               class="btn btn-sm" style="background:#c53030;color:white;white-space:nowrap;">
                <i class="fas fa-file-pdf"></i> Download Slip PDF
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        {{-- Stat --}}
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border-bottom:1px solid #e2e8f0;">
            @foreach([['hadir','Hadir','#38a169'],['terlambat','Terlambat','#e53e3e'],['izin','Izin','#3182ce'],['sakit','Sakit','#805ad5'],['alpha','Alpha','#718096'],['cuti','Cuti','#553c9a']] as [$key,$label,$color])
            <div style="padding:14px;text-align:center;border-right:1px solid #e2e8f0;">
                <div style="font-size:22px;font-weight:800;color:{{ $color }};">{{ $stat[$key] }}</div>
                <div style="font-size:11px;color:#718096;">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        {{-- Kalender Visual --}}
        @php
            $statusWarna = [
                'hadir'     => '#c6f6d5',
                'terlambat' => '#fef3c7',
                'izin'      => '#bee3f8',
                'sakit'     => '#fed7e2',
                'alpha'     => '#fed7d7',
                'cuti'      => '#e9d8fd',
            ];
            $statusTeks = [
                'hadir'     => '#276749',
                'terlambat' => '#92400e',
                'izin'      => '#2c5282',
                'sakit'     => '#97266d',
                'alpha'     => '#c53030',
                'cuti'      => '#553c9a',
            ];
            $namaHari = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        @endphp
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;">
            <div style="font-size:12px;font-weight:700;color:#4a5568;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">
                <i class="fas fa-calendar-alt" style="color:#4299e1;"></i>
                Kalender — {{ \Carbon\Carbon::createFromDate($tahun,$bulan,1)->translatedFormat('F Y') }}
            </div>
            {{-- Legenda (dipindah ke atas kalender, teks dibesarkan supaya lebih terlihat) --}}
            <div style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:14px;padding:10px 14px;background:#f7fafc;border-radius:10px;">
                @foreach(['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha','cuti'=>'Cuti'] as $k=>$v)
                <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#4a5568;">
                    <div style="width:16px;height:16px;border-radius:4px;background:{{ $statusWarna[$k] }};border:1px solid {{ $statusTeks[$k] }}55;flex-shrink:0;"></div>
                    {{ $v }}
                </div>
                @endforeach
                <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#4a5568;">
                    <div style="width:16px;height:16px;border-radius:4px;background:#ebf8ff;flex-shrink:0;"></div> Libur
                </div>
                <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#4a5568;">
                    <div style="width:16px;height:16px;border-radius:4px;border:2px solid #4299e1;flex-shrink:0;"></div> Hari ini
                </div>
            </div>
            {{-- Header hari --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:4px;">
                @foreach($namaHari as $idx => $nh)
                <div style="text-align:center;font-size:10px;font-weight:700;color:{{ $idx==0 ? '#e53e3e' : '#718096' }};padding:4px 0;">{{ $nh }}</div>
                @endforeach
            </div>
            {{-- Grid hari --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;">
                {{-- Offset hari pertama --}}
                @for($off = 0; $off < $hariPertama; $off++)
                <div></div>
                @endfor

                @for($d = 1; $d <= $jumlahHari; $d++)
                @php
                    $tglStr    = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
                    $absenItem = $kalenderData->get($tglStr);
                    $isHariIni = $tglStr === now()->format('Y-m-d');
                    $isLibur   = isset($hariLibur[$tglStr]);
                    $isMinggu  = \Carbon\Carbon::parse($tglStr)->dayOfWeek === 0;
                    $bgColor   = $absenItem ? ($statusWarna[$absenItem->status] ?? '#f7fafc') : ($isLibur ? '#ebf8ff' : ($isMinggu ? '#fff5f5' : '#f7fafc'));
                    $txtColor  = $absenItem ? ($statusTeks[$absenItem->status] ?? '#4a5568') : ($isLibur ? '#2b6cb0' : ($isMinggu ? '#e53e3e' : '#718096'));
                    $inisial   = $absenItem ? strtoupper(substr($absenItem->status, 0, 1)) : ($isLibur ? 'L' : '');
                    $border    = $isHariIni ? '2px solid #4299e1' : '2px solid transparent';
                @endphp
                <div title="{{ $absenItem ? $absenItem->badge_label . ($absenItem->jam_masuk ? ' — ' . substr($absenItem->jam_masuk,0,5) : '') : ($isLibur ? 'Hari Libur' : ($isMinggu ? 'Minggu' : '')) }}"
                     style="aspect-ratio:1;border-radius:8px;background:{{ $bgColor }};border:{{ $border }};display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:default;position:relative;">
                    <span style="font-size:11px;font-weight:{{ $isHariIni ? '800' : '600' }};color:{{ $isHariIni ? '#2b6cb0' : $txtColor }};">{{ $d }}</span>
                    @if($inisial)
                    <span style="font-size:8px;font-weight:700;color:{{ $txtColor }};line-height:1;margin-top:1px;">{{ $inisial }}</span>
                    @endif
                </div>
                @endfor
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Tanggal</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Jam Masuk</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Jam Keluar</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Durasi</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Metode</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 16px;font-weight:600;">{{ $a->tanggal->translatedFormat('D, d M Y') }}</td>
                        <td style="padding:10px 16px;">{{ substr($a->jam_masuk,0,5) }}</td>
                        <td style="padding:10px 16px;color:#718096;">{{ $a->jam_keluar ? substr($a->jam_keluar,0,5) : '—' }}</td>
                        <td style="padding:10px 16px;color:#718096;">
                            @if($a->durasi_menit) {{ intdiv($a->durasi_menit,60) }}j {{ $a->durasi_menit%60 }}m
                            @else — @endif
                        </td>
                        <td style="padding:10px 16px;">
                            <span style="font-size:11px;background:#f7fafc;padding:2px 8px;border-radius:8px;color:#718096;">{{ $a->metode ?? 'manual' }}</span>
                        </td>
                        <td style="padding:10px 16px;">
                            <span class="badge {{ $a->badge_class }}" title="{{ $a->keterangan_terlambat ?? '' }}">
                                @if($a->status === 'terlambat')<i class="fas fa-clock"></i> @endif
                                {{ $a->badge_label }}
                                @if($a->has_alasan_terlambat)<i class="fas fa-comment-alt" style="font-size:9px;margin-left:2px;"></i>@endif
                            </span>
                            @if($a->status === 'terlambat')
                                @if($a->has_alasan_terlambat)
                                <div style="font-size:10px;color:#92400e;margin-top:3px;max-width:200px;font-style:italic;">
                                    <i class="fas fa-comment-alt"></i> {{ $a->keterangan_terlambat }}
                                </div>
                                @elseif($wajibAlasan)
                                <button onclick="bukaBuatAlasan({{ $a->id }}, '{{ $a->tanggal->format('d M Y') }}', '{{ substr($a->jam_masuk,0,5) }}')"
                                    style="margin-top:4px;background:#fff5f5;border:1px dashed #fc8181;border-radius:12px;padding:2px 8px;font-size:10px;color:#c53030;cursor:pointer;display:block;">
                                    <i class="fas fa-edit"></i> Isi Alasan
                                </button>
                                @endif
                            @endif
                            @if($a->pulang_cepat)
                            <div style="font-size:10px;color:#744210;margin-top:3px;">
                                <i class="fas fa-sign-out-alt"></i> Pulang lebih awal
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:30px;text-align:center;color:#a0aec0;">Tidak ada data absensi bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">{{ $absensi->withQueryString()->links() }}</div>
    </div>
</div>
</div>

{{-- ══ TAB: REKAP KEHADIRAN (ringkasan tahunan) ══ --}}
<div class="tab-content" id="tab-rekap">

{{-- Header --}}
<div style="background:linear-gradient(135deg,#1a202c,#2d3748);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-chart-bar"></i> Rekap Kehadiran Saya</h4>
        <p style="margin:4px 0 0;opacity:.8;font-size:13px;">{{ $karyawan->nama }} &mdash; Tahun {{ $tahunRekap }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <select name="tahun_rekap" class="form-select" style="padding:7px 12px;font-size:13px;background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:8px;" onchange="this.form.submit()">
                @foreach($tahunList as $y)
                <option value="{{ $y }}" {{ $tahunRekap == $y ? 'selected' : '' }} style="color:#2d3748;">{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('portal.slip-pdf', ['bulan' => now()->month, 'tahun' => $tahunRekap]) }}"
           style="background:rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-file-pdf"></i> Slip Absensi
        </a>
    </div>
</div>

{{-- Total Tahunan --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:linear-gradient(135deg,#276749,#38a169);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['hadir'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Hadir</div>
    </div>
    <div style="background:linear-gradient(135deg,#c05621,#ed8936);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['terlambat'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Terlambat</div>
    </div>
    <div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['izin'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Izin</div>
    </div>
    <div style="background:linear-gradient(135deg,#97266d,#d53f8c);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['sakit'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Sakit</div>
    </div>
    <div style="background:linear-gradient(135deg,#553c9a,#805ad5);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['cuti'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Cuti</div>
    </div>
    <div style="background:linear-gradient(135deg,#c53030,#e53e3e);border-radius:12px;padding:16px 18px;color:white;text-align:center;">
        <div style="font-size:28px;font-weight:800;">{{ $totalRekap['alpha'] }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Alpha</div>
    </div>
</div>

{{-- Tabel Rekap Per Bulan --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-table" style="color:#2b6cb0;"></i> Rekap Per Bulan &mdash; Tahun {{ $tahunRekap }}</h6>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">Bulan</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#4a5568;">Hari Kerja</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#276749;">Hadir</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#c05621;">Terlambat</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#2b6cb0;">Izin</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#97266d;">Sakit</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#553c9a;">Cuti</th>
                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#c53030;">Alpha</th>
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapBulanan as $r)
                @php
                    $isFuture = \Carbon\Carbon::createFromDate($tahunRekap, $r['bulan'], 1)->startOfMonth()->gt(now());
                    $pct      = $r['persen'];
                    $barColor = $pct >= 90 ? '#38a169' : ($pct >= 70 ? '#ed8936' : '#e53e3e');
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;{{ $isFuture ? 'opacity:0.4;' : '' }}"
                    onmouseover="{{ !$isFuture ? 'this.style.background=\"#f7fafc\"' : '' }}" onmouseout="this.style.background=''">
                    <td style="padding:10px 16px;font-weight:700;color:#2d3748;">
                        {{ \Carbon\Carbon::createFromDate($tahunRekap, $r['bulan'], 1)->translatedFormat('F') }}
                        @if($r['bulan'] == now()->month && $tahunRekap == now()->year)
                        <span style="font-size:10px;background:#ebf8ff;color:#2b6cb0;padding:1px 7px;border-radius:10px;margin-left:6px;font-weight:700;">Ini</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;color:#718096;">{{ $r['hari_kerja'] }}</td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['hadir'] > 0)
                        <span style="font-weight:700;color:#276749;">{{ $r['hadir'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['terlambat'] > 0)
                        <span style="font-weight:700;color:#c05621;">{{ $r['terlambat'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['izin'] > 0)
                        <span style="font-weight:700;color:#2b6cb0;">{{ $r['izin'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['sakit'] > 0)
                        <span style="font-weight:700;color:#97266d;">{{ $r['sakit'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['cuti'] > 0)
                        <span style="font-weight:700;color:#553c9a;">{{ $r['cuti'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($r['alpha'] > 0)
                        <span style="font-weight:800;color:#c53030;">{{ $r['alpha'] }}</span>
                        @else
                        <span style="color:#a0aec0;">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 16px;min-width:120px;">
                        @if(!$isFuture && $r['hari_kerja'] > 0)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#e2e8f0;border-radius:99px;height:7px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:{{ $barColor }};height:100%;border-radius:99px;transition:width 0.4s;"></div>
                            </div>
                            <span style="font-size:11px;font-weight:700;color:{{ $barColor }};white-space:nowrap;">{{ $pct }}%</span>
                        </div>
                        @else
                        <span style="color:#a0aec0;font-size:12px;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f7fafc;border-top:2px solid #e2e8f0;">
                    <td style="padding:10px 16px;font-weight:800;color:#2d3748;">Total {{ $tahunRekap }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:700;color:#718096;">{{ $rekapBulanan->sum('hari_kerja') }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#276749;">{{ $totalRekap['hadir'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#c05621;">{{ $totalRekap['terlambat'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#2b6cb0;">{{ $totalRekap['izin'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#97266d;">{{ $totalRekap['sakit'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#553c9a;">{{ $totalRekap['cuti'] }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:800;color:#c53030;">{{ $totalRekap['alpha'] }}</td>
                    <td style="padding:10px 16px;">
                        @php
                            $totalHk = $rekapBulanan->sum('hari_kerja');
                            $totalPct = $totalHk > 0 ? round($totalRekap['hadir'] / $totalHk * 100) : 0;
                            $totalBarColor = $totalPct >= 90 ? '#38a169' : ($totalPct >= 70 ? '#ed8936' : '#e53e3e');
                        @endphp
                        <span style="font-weight:800;color:{{ $totalBarColor }};">{{ $totalPct }}%</span>
                    </td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

{{-- Chart Bar Kehadiran --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-chart-bar" style="color:#4299e1;"></i> Grafik Kehadiran {{ $tahunRekap }}</h6>
    </div>
    <div class="card-body">
        <canvas id="chartKehadiran" height="90"></canvas>
    </div>
</div>

</div>

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
function bukaBuatAlasan(absensiId, tanggal, jam) {
    document.getElementById('modal-absensi-id').value = absensiId;
    document.getElementById('modal-alasan-info').textContent = 'Tanggal: ' + tanggal + ' — Jam masuk: ' + jam;
    var m = document.getElementById('modal-alasan');
    m.style.display = 'flex';
}
function tutupModal() {
    document.getElementById('modal-alasan').style.display = 'none';
}
document.getElementById('modal-alasan').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    document.querySelectorAll('.tab-btn[data-tab="' + tabId + '"]').forEach(btn => btn.classList.add('active'));
    history.replaceState(null, null, '#' + tabId);
}

(function () {
    const labels = @json($rekapBulanan->pluck('nama_bulan'));
    const hadir     = @json($rekapBulanan->pluck('hadir'));
    const terlambat = @json($rekapBulanan->pluck('terlambat'));
    const alpha     = @json($rekapBulanan->pluck('alpha'));
    const izin      = @json($rekapBulanan->pluck('izin'));
    const sakit     = @json($rekapBulanan->pluck('sakit'));

    const ctx = document.getElementById('chartKehadiran');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Hadir', data: hadir, backgroundColor: 'rgba(56,161,105,0.8)', borderRadius: 4 },
                    { label: 'Terlambat', data: terlambat, backgroundColor: 'rgba(237,137,54,0.8)', borderRadius: 4 },
                    { label: 'Izin', data: izin, backgroundColor: 'rgba(66,153,225,0.7)', borderRadius: 4 },
                    { label: 'Sakit', data: sakit, backgroundColor: 'rgba(213,63,140,0.7)', borderRadius: 4 },
                    { label: 'Alpha', data: alpha, backgroundColor: 'rgba(229,62,62,0.8)', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { stacked: false, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    const validTabs = ['tab-harian', 'tab-rekap'];
    const hash = window.location.hash.replace('#', '');
    const tabParam = new URLSearchParams(window.location.search).get('_tab');
    const initialTab = validTabs.includes(hash) ? hash : (validTabs.includes(tabParam) ? tabParam : 'tab-harian');
    switchTab(initialTab);
})();
</script>
@endpush
@endsection
