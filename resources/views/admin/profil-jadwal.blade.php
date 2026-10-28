@extends('layouts.admin')
@section('title', 'Jadwal Saya')
@section('page-title', 'Jadwal Shift Saya')

@section('content')

@php
    $roleColor = ['superadmin'=>'#e53e3e','admin'=>'#dd6b20','hrd'=>'#3182ce','supervisor'=>'#805ad5'];
    $roleLabel = ['superadmin'=>'Super Admin','admin'=>'Admin','hrd'=>'HRD','supervisor'=>'SPV/Manager'];
    $role      = auth()->user()->role;
@endphp

@if(!$karyawan)
{{-- Belum terhubung ke data karyawan --}}
<div style="text-align:center;padding:60px 20px;">
    <div style="width:80px;height:80px;border-radius:50%;background:#fef9c3;border:2px solid #f6e05e;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;">
        <i class="fas fa-link" style="color:#d69e2e;"></i>
    </div>
    <h5 style="color:#2d3748;margin-bottom:8px;">Akun Belum Terhubung ke Data Karyawan</h5>
    <p style="color:#718096;font-size:14px;max-width:420px;margin:0 auto 20px;line-height:1.7;">
        Untuk melihat jadwal shift Anda, akun ini perlu dihubungkan ke data karyawan.<br>
        Hubungi <strong>Admin</strong> atau <strong>Super Admin</strong> untuk menghubungkan akun Anda.
    </p>
    @if(auth()->user()->hasPermission('users.kelola'))
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
        <i class="fas fa-users-cog"></i> Kelola User
    </a>
    @endif
</div>
@else

{{-- Info Karyawan --}}
<div style="background:linear-gradient(135deg,#1a202c,#2d3748);border-radius:16px;padding:24px;margin-bottom:20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#4299e1,#667eea);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:white;flex-shrink:0;">
        {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:18px;font-weight:800;color:white;">{{ $karyawan->nama }}</div>
        <div style="color:#a0aec0;font-size:13px;margin-top:2px;">
            {{ $karyawan->nik }} &nbsp;·&nbsp; {{ $karyawan->jabatan ?? '-' }} &nbsp;·&nbsp; {{ $karyawan->departemen ?? '-' }}
        </div>
        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
            <span style="background:rgba(66,153,225,0.2);color:#63b3ed;border:1px solid rgba(66,153,225,0.3);padding:3px 12px;border-radius:12px;font-size:11px;font-weight:600;">
                <i class="fas fa-clock"></i> Shift Default: {{ $karyawan->shift?->nama_shift ?? '-' }}
            </span>
            @if($karyawan->shift)
            <span style="background:rgba(72,187,120,0.2);color:#68d391;border:1px solid rgba(72,187,120,0.3);padding:3px 12px;border-radius:12px;font-size:11px;font-weight:600;">
                <i class="fas fa-sign-in-alt"></i> {{ $karyawan->shift->jam_masuk ?? '-' }} — {{ $karyawan->shift->jam_keluar ?? '-' }}
            </span>
            @endif
        </div>
    </div>
    <div style="text-align:right;">
        <div style="color:#a0aec0;font-size:11px;margin-bottom:4px;">Bulan</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <select name="bulan" class="form-select" style="width:auto;font-size:13px;padding:6px 10px;" onchange="this.form.submit()">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $bulan==$m ? 'selected':'' }}>
                        {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="tahun" class="form-select" style="width:auto;font-size:13px;padding:6px 10px;" onchange="this.form.submit()">
                @foreach(range(now()->year-1, now()->year+1) as $y)
                    <option value="{{ $y }}" {{ $tahun==$y ? 'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Stat ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
    @php
        $totalJadwalKhusus = $jadwal->count();
        $totalHariKerja    = collect(range(1, $jumlahHari))->filter(function($d) use($bulan,$tahun) {
            return !\Carbon\Carbon::createFromDate($tahun,$bulan,$d)->isSunday();
        })->count();
    @endphp
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#4299e1;">{{ $jumlahHari }}</div>
        <div style="font-size:12px;color:#718096;margin-top:4px;">Hari dalam Bulan</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#48bb78;">{{ $totalHariKerja }}</div>
        <div style="font-size:12px;color:#718096;margin-top:4px;">Hari Kerja</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#ed8936;">{{ $totalJadwalKhusus }}</div>
        <div style="font-size:12px;color:#718096;margin-top:4px;">Jadwal Khusus</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#805ad5;">{{ $totalHariKerja - $totalJadwalKhusus }}</div>
        <div style="font-size:12px;color:#718096;margin-top:4px;">Shift Default</div>
    </div>
</div>

{{-- Kalender Jadwal --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-calendar-week" style="color:#4299e1;"></i>
            Jadwal Bulan {{ \Carbon\Carbon::create($tahun,$bulan)->translatedFormat('F Y') }}
        </h6>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;">
            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h)
            <div style="text-align:center;font-size:11px;font-weight:700;color:#718096;padding:6px 0;text-transform:uppercase;letter-spacing:0.5px;">
                {{ $h }}
            </div>
            @endforeach

            @php
                // Padding hari pertama
                $firstDay = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->dayOfWeek; // 0=Sun
            @endphp
            @for($pad = 0; $pad < $firstDay; $pad++)
                <div></div>
            @endfor

            @for($d = 1; $d <= $jumlahHari; $d++)
                @php
                    $tgl       = \Carbon\Carbon::createFromDate($tahun, $bulan, $d);
                    $tglKey    = $tgl->format('Y-m-d');
                    $isToday   = $tgl->isToday();
                    $isWeekend = $tgl->isWeekend();
                    $isPast    = $tgl->isPast() && !$isToday;
                    $isFuture  = $tgl->isFuture();

                    $jadwalHari = $jadwal->get($tglKey);
                    $shiftObj   = $jadwalHari ? $jadwalHari->shift : ($isWeekend ? null : $karyawan->shift);
                    $isKhusus   = (bool)$jadwalHari;
                    $namaShift  = $shiftObj ? strtolower($shiftObj->nama_shift) : '';

                    if ($isWeekend && !$jadwalHari) {
                        $bg='#f7fafc'; $border='#e2e8f0'; $textColor='#cbd5e0'; $badge=''; $badgeColor='';
                    } elseif (!$shiftObj) {
                        $bg='#f7fafc'; $border='#e2e8f0'; $textColor='#cbd5e0'; $badge=''; $badgeColor='';
                    } elseif (str_contains($namaShift,'pagi')) {
                        $bg='#ebf8ff'; $border='#bee3f8'; $textColor='#2c5282'; $badge='Pagi'; $badgeColor='#3182ce';
                    } elseif (str_contains($namaShift,'siang')) {
                        $bg='#fffbeb'; $border='#fbd38d'; $textColor='#744210'; $badge='Siang'; $badgeColor='#d69e2e';
                    } elseif (str_contains($namaShift,'malam')) {
                        $bg='#f3e8ff'; $border='#e9d8fd'; $textColor='#44337a'; $badge='Malam'; $badgeColor='#6b46c1';
                    } else {
                        $bg='#f0fff4'; $border='#9ae6b4'; $textColor='#276749'; $badge='Normal'; $badgeColor='#38a169';
                    }
                    if ($isToday) { $border='#4299e1'; $bg='#ebf8ff'; }
                @endphp
                <div style="
                    background:{{ $bg }};
                    border:{{ $isToday ? '2px' : '1px' }} solid {{ $border }};
                    border-radius:10px;
                    padding:8px 6px;
                    text-align:center;
                    {{ $isKhusus ? 'outline:2px solid #ed8936;outline-offset:1px;' : '' }}
                    {{ $isPast ? 'opacity:0.6;' : '' }}
                    min-height:72px;
                    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
                " title="{{ $shiftObj ? $shiftObj->nama_shift.($shiftObj->jam_masuk ? ' ('.$shiftObj->jam_masuk.' - '.$shiftObj->jam_keluar.')' : '') : ($isWeekend ? 'Libur' : '-') }}{{ $isKhusus ? ' [Jadwal Khusus]' : '' }}">
                    <div style="font-size:13px;font-weight:{{ $isToday ? '800':'600' }};color:{{ $isToday ? '#2b6cb0' : ($isWeekend ? '#a0aec0' : '#2d3748') }};">
                        {{ $d }}
                    </div>
                    @if($isToday)
                        <div style="font-size:8px;font-weight:700;color:#4299e1;text-transform:uppercase;letter-spacing:0.5px;">Hari Ini</div>
                    @endif
                    @if($shiftObj && $badge)
                        <div style="font-size:9px;font-weight:700;color:{{ $badgeColor }};background:{{ $bg }};border:1px solid {{ $border }};border-radius:6px;padding:1px 6px;line-height:1.4;">
                            {{ $badge }}
                        </div>
                        <div style="font-size:9px;color:#718096;">
                            {{ $shiftObj->jam_masuk ? substr($shiftObj->jam_masuk,0,5) : '' }}
                        </div>
                    @elseif($isWeekend && !$jadwalHari)
                        <div style="font-size:9px;color:#cbd5e0;">Libur</div>
                    @endif
                    @if($isKhusus)
                        <div style="font-size:8px;color:#c05621;font-weight:700;">★ Khusus</div>
                    @endif
                </div>
            @endfor
        </div>

        {{-- Legenda --}}
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0;display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <span style="font-size:11px;color:#718096;font-weight:600;">Keterangan:</span>
            <span style="font-size:11px;background:#ebf8ff;color:#3182ce;border:1px solid #bee3f8;border-radius:6px;padding:2px 10px;">Pagi</span>
            <span style="font-size:11px;background:#fffbeb;color:#d69e2e;border:1px solid #fbd38d;border-radius:6px;padding:2px 10px;">Siang</span>
            <span style="font-size:11px;background:#f3e8ff;color:#6b46c1;border:1px solid #e9d8fd;border-radius:6px;padding:2px 10px;">Malam</span>
            <span style="font-size:11px;background:#f0fff4;color:#38a169;border:1px solid #9ae6b4;border-radius:6px;padding:2px 10px;">Normal</span>
            <span style="font-size:11px;outline:2px solid #ed8936;outline-offset:1px;border-radius:6px;padding:2px 10px;color:#c05621;">★ Jadwal Khusus</span>
            <span style="font-size:11px;border:2px solid #4299e1;border-radius:6px;padding:2px 10px;color:#2b6cb0;">Hari Ini</span>
        </div>
    </div>
</div>

{{-- List Jadwal Khusus bulan ini --}}
@if($jadwal->count() > 0)
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-star" style="color:#ed8936;"></i> Jadwal Khusus Bulan Ini</h6>
    </div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwal->sortBy('date') as $j)
                <tr>
                    <td style="font-weight:600;">{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
                    <td style="color:#718096;">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('l') }}</td>
                    <td>
                        <span style="background:#fffbeb;color:#c05621;border:1px solid #fbd38d;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">
                            {{ $j->shift?->nama_shift ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $j->shift?->jam_masuk ?? '-' }}</td>
                    <td>{{ $j->shift?->jam_keluar ?? '-' }}</td>
                    <td style="color:#718096;font-size:13px;">{{ $j->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif
@endsection
