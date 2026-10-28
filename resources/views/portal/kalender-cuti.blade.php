@extends('layouts.admin')
@section('title', 'Kalender ' . $tahun)
@section('page-title', 'Kalender')

@section('content')
<div style="max-width:960px;margin:0 auto;">

{{-- Header --}}
<div style="background:linear-gradient(135deg,#553c9a,#7c3aed);border-radius:16px;padding:24px 28px;margin-bottom:20px;color:white;">
    <div style="font-size:13px;font-weight:600;opacity:.8;letter-spacing:.5px;">KALENDER PERUSAHAAN</div>
    <div style="font-size:22px;font-weight:800;margin-top:4px;">Cuti &amp; Hari Libur {{ $tahun }}</div>
    <div style="font-size:13px;opacity:.8;margin-top:4px;">PT Global Multi Pharmalab</div>

    {{-- Tahun selector --}}
    <form method="GET" action="{{ route('portal.kalender-cuti') }}" style="margin-top:14px;display:flex;align-items:center;gap:10px;">
        <select name="tahun" onchange="this.form.submit()" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);color:white;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;cursor:pointer;">
            @foreach(range(date('Y')-1, date('Y')+1) as $y)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <span style="font-size:12px;opacity:.7;">Pilih tahun</span>
    </form>
</div>

{{-- Stat boxes --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
    @php
    $totalLibur = $hariLiburTahun->count();
    $totalNasional = $liburNasional->count();
    $totalBersama = $cutiBersama->count();
    $totalInternal = $liburInternal->count();
    $cutiDisetujui = $cutiKaryawan->where('status','disetujui')->count();
    @endphp
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #7c3aed;">
        <div style="font-size:26px;font-weight:800;color:#7c3aed;">{{ $totalLibur }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;font-weight:600;">Total Hari Libur</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #dc2626;">
        <div style="font-size:26px;font-weight:800;color:#dc2626;">{{ $totalNasional }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;font-weight:600;">Libur Nasional</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #7c3aed;">
        <div style="font-size:26px;font-weight:800;color:#7c3aed;">{{ $totalBersama }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;font-weight:600;">Cuti Bersama</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #2563eb;">
        <div style="font-size:26px;font-weight:800;color:#2563eb;">{{ $totalInternal }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;font-weight:600;">Libur Internal</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #059669;">
        <div style="font-size:26px;font-weight:800;color:#059669;">{{ $cutiDisetujui }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;font-weight:600;">Cuti Saya Disetujui</div>
    </div>
</div>

{{-- Legend --}}
<div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:14px 18px;margin-bottom:20px;display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
    <span style="font-size:12px;font-weight:700;color:#4a5568;">KETERANGAN:</span>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;">
        <span style="width:14px;height:14px;border-radius:3px;background:#fee2e2;border:1px solid #dc2626;display:inline-block;"></span> Libur Nasional
    </span>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;">
        <span style="width:14px;height:14px;border-radius:3px;background:#ede9fe;border:1px solid #7c3aed;display:inline-block;"></span> Cuti Bersama
    </span>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;">
        <span style="width:14px;height:14px;border-radius:3px;background:#dbeafe;border:1px solid #2563eb;display:inline-block;"></span> Libur Internal
    </span>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;">
        <span style="width:14px;height:14px;border-radius:3px;background:#d1fae5;border:1px solid #059669;display:inline-block;"></span> Cuti Saya
    </span>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;">
        <span style="width:14px;height:14px;border-radius:3px;background:#f1f5f9;border:1px solid #94a3b8;display:inline-block;"></span> Hari Minggu
    </span>
</div>

{{-- Grid kalender per bulan --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px;margin-bottom:20px;">
@foreach($kalenderBulanan as $bln)
@php
    $carbon = \Carbon\Carbon::create($tahun, $bln['bulan'], 1);
    $jumlahHari = $carbon->daysInMonth;
    $hariPertama = $carbon->dayOfWeek; // 0=Minggu
    // Map hari libur bulan ini: tanggal => jenis
    $liburMap = [];
    foreach ($bln['libur'] as $h) {
        $liburMap[$h->tanggal->day] = $h->jenis;
        $liburMap['nama_'.$h->tanggal->day] = $h->nama;
    }
    // Map cuti sendiri bulan ini
    $cutiMap = [];
    foreach ($cutiKaryawan->where('status','disetujui') as $c) {
        $s = max($c->start_date->startOfDay(), $carbon->copy()->startOfMonth());
        $e = min($c->end_date->endOfDay(), $carbon->copy()->endOfMonth());
        if ($s <= $e) {
            $d = $s->copy();
            while ($d <= $e) {
                $cutiMap[$d->day] = true;
                $d->addDay();
            }
        }
    }
    $today = now();
    $isCurrentMonth = ($today->year == $tahun && $today->month == $bln['bulan']);
@endphp
<div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
    <div style="padding:12px 16px;background:linear-gradient(135deg,#553c9a,#7c3aed);color:white;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;font-size:14px;">{{ $bln['nama_bulan'] }}</span>
        @if($bln['libur']->count() > 0)
        <span style="background:rgba(255,255,255,0.25);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;">{{ $bln['libur']->count() }} libur</span>
        @endif
    </div>
    <div style="padding:8px;">
        {{-- Header hari --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px;">
            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $idx => $h)
            <div style="text-align:center;font-size:10px;font-weight:700;color:{{ $idx==0 ? '#dc2626' : '#94a3b8' }};padding:3px 0;">{{ $h }}</div>
            @endforeach
        </div>
        {{-- Grid hari --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;">
            {{-- Offset hari pertama --}}
            @for($i = 0; $i < $hariPertama; $i++)
            <div></div>
            @endfor
            @for($d = 1; $d <= $jumlahHari; $d++)
            @php
                $isMinggu = (($hariPertama + $d - 1) % 7 === 0);
                $isLibur = isset($liburMap[$d]);
                $isCutiSaya = isset($cutiMap[$d]);
                $isHariIni = $isCurrentMonth && $today->day == $d;
                $jenisLibur = $liburMap[$d] ?? null;
                $namaLibur  = $liburMap['nama_'.$d] ?? null;

                if ($isLibur) {
                    $bgCell = match($jenisLibur) {
                        'nasional'     => '#fee2e2',
                        'cuti_bersama' => '#ede9fe',
                        'internal'     => '#dbeafe',
                        default        => '#f1f5f9',
                    };
                    $txtCell = match($jenisLibur) {
                        'nasional'     => '#dc2626',
                        'cuti_bersama' => '#7c3aed',
                        'internal'     => '#2563eb',
                        default        => '#4a5568',
                    };
                } elseif ($isCutiSaya) {
                    $bgCell = '#d1fae5';
                    $txtCell = '#059669';
                } elseif ($isMinggu) {
                    $bgCell = '#f8fafc';
                    $txtCell = '#94a3b8';
                } else {
                    $bgCell = 'transparent';
                    $txtCell = '#374151';
                }

                $outlineStyle = $isHariIni ? 'outline:2px solid #7c3aed;outline-offset:-2px;font-weight:800;' : '';
                $title = $namaLibur ? 'title="'.$namaLibur.'"' : ($isCutiSaya ? 'title="Cuti Anda"' : '');
            @endphp
            <div {!! $title !!} style="text-align:center;padding:5px 2px;border-radius:6px;font-size:12px;font-weight:{{ $isLibur||$isCutiSaya ? '700' : '500' }};background:{{ $bgCell }};color:{{ $txtCell }};{{ $outlineStyle }}cursor:{{ $namaLibur||$isCutiSaya ? 'help' : 'default' }};">
                {{ $d }}
            </div>
            @endfor
        </div>
    </div>
    {{-- Daftar libur di bulan ini --}}
    @if($bln['libur']->count() > 0)
    <div style="border-top:1px solid #f0f0f0;padding:8px 12px;">
        @foreach($bln['libur'] as $h)
        @php
            $badgeColor = match($h->jenis) {
                'nasional'     => ['#fee2e2','#dc2626'],
                'cuti_bersama' => ['#ede9fe','#7c3aed'],
                'internal'     => ['#dbeafe','#2563eb'],
                default        => ['#f1f5f9','#64748b'],
            };
        @endphp
        <div style="display:flex;align-items:center;gap:8px;padding:3px 0;font-size:11px;">
            <span style="background:{{ $badgeColor[0] }};color:{{ $badgeColor[1] }};border-radius:4px;padding:1px 7px;font-weight:700;min-width:28px;text-align:center;">{{ $h->tanggal->format('d') }}</span>
            <span style="color:#374151;">{{ $h->nama }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endforeach
</div>

{{-- Tabel cuti karyawan sendiri --}}
@if($cutiKaryawan->count() > 0)
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:20px;">
    <div style="padding:14px 20px;background:linear-gradient(135deg,#059669,#34d399);color:white;font-weight:700;font-size:14px;">
        <i class="fas fa-umbrella-beach"></i> Pengajuan Cuti Saya Tahun {{ $tahun }}
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Jenis</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Tanggal</th>
                    <th style="padding:10px 16px;text-align:center;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Hari</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Status</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
            @foreach($cutiKaryawan as $c)
            @php
                $statusColor = match($c->status) {
                    'disetujui' => ['#d1fae5','#059669'],
                    'ditolak'   => ['#fee2e2','#dc2626'],
                    default     => ['#fef9c3','#b45309'],
                };
                $statusLabel = match($c->status) {
                    'pending_spv'     => 'Menunggu SPV',
                    'pending_manajer' => 'Menunggu Manajer',
                    'pending_hrd'     => 'Menunggu HRD',
                    default           => ucfirst($c->status),
                };
                $hariCuti = $c->start_date->diffInWeekdays($c->end_date) + 1;
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 16px;color:#374151;">{{ ucfirst($c->jenis_cuti ?? 'Cuti Tahunan') }}</td>
                <td style="padding:10px 16px;color:#374151;">
                    {{ $c->start_date->format('d/m/Y') }}
                    @if($c->start_date->ne($c->end_date))
                    – {{ $c->end_date->format('d/m/Y') }}
                    @endif
                </td>
                <td style="padding:10px 16px;text-align:center;font-weight:700;color:#374151;">{{ $hariCuti }}</td>
                <td style="padding:10px 16px;">
                    <span style="background:{{ $statusColor[0] }};color:{{ $statusColor[1] }};border-radius:20px;padding:2px 12px;font-size:11px;font-weight:700;">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td style="padding:10px 16px;color:#64748b;font-size:12px;">{{ $c->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Daftar lengkap semua hari libur tahun ini --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:20px;">
    <div style="padding:14px 20px;border-bottom:1px solid #f0f0f0;font-weight:700;font-size:14px;color:#374151;">
        <i class="fas fa-list" style="color:#7c3aed;margin-right:8px;"></i>Daftar Lengkap Hari Libur {{ $tahun }}
    </div>
    @if($hariLiburTahun->isEmpty())
    <div style="padding:28px;text-align:center;color:#94a3b8;font-size:13px;">
        <i class="fas fa-calendar" style="font-size:32px;margin-bottom:10px;display:block;"></i>
        Belum ada data hari libur untuk tahun {{ $tahun }}
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Tanggal</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Hari</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Nama Hari Libur</th>
                    <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;">Jenis</th>
                </tr>
            </thead>
            <tbody>
            @foreach($hariLiburTahun as $hl)
            @php
                $jenisStyle = match($hl->jenis) {
                    'nasional'     => ['#fee2e2','#dc2626','Libur Nasional'],
                    'cuti_bersama' => ['#ede9fe','#7c3aed','Cuti Bersama'],
                    'internal'     => ['#dbeafe','#2563eb','Libur Internal'],
                    default        => ['#f1f5f9','#64748b', ucfirst($hl->jenis)],
                };
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 16px;font-weight:700;color:#374151;">{{ $hl->tanggal->format('d/m/Y') }}</td>
                <td style="padding:10px 16px;color:#64748b;">{{ $hl->tanggal->translatedFormat('l') }}</td>
                <td style="padding:10px 16px;color:#374151;">{{ $hl->nama }}</td>
                <td style="padding:10px 16px;">
                    <span style="background:{{ $jenisStyle[0] }};color:{{ $jenisStyle[1] }};border-radius:20px;padding:2px 12px;font-size:11px;font-weight:700;">
                        {{ $jenisStyle[2] }}
                    </span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

</div>
@endsection
