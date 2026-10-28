@extends('layouts.admin')
@section('title','Dashboard Saya')
@section('page-title','Dashboard Saya')

@push('styles')
<style>
.portal-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.jadwal-7col   { display:grid; grid-template-columns:repeat(7,1fr); gap:8px; }
.kalender-7col { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
@media (max-width: 900px) {
    .portal-grid-2 { grid-template-columns:1fr; }
}
@media (max-width: 640px) {
    .jadwal-7col { grid-template-columns:repeat(4,1fr); gap:6px; }
}
@media (max-width: 420px) {
    .jadwal-7col { grid-template-columns:repeat(2,1fr); }
    .kalender-7col { font-size:11px; }
}
</style>
@endpush

@section('content')
{{-- Banner Hari Libur --}}
@if($isLiburHariIni)
<div style="background:linear-gradient(135deg,#c6f6d5,#9ae6b4);border-radius:12px;padding:14px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-umbrella-beach" style="font-size:24px;color:#276749;"></i>
    <div>
        <div style="font-weight:700;color:#276749;font-size:14px;">Hari Libur: {{ $namaLibur }}</div>
        <div style="font-size:12px;color:#48bb78;">Selamat menikmati hari libur. Absensi tidak diperlukan hari ini.</div>
    </div>
</div>
@endif

{{-- Header Profil --}}
<div style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    @if($karyawan->foto_wajah)
        <img src="{{ asset('storage/'.$karyawan->foto_wajah) }}" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.5);">
    @else
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:28px;">
            <i class="fas fa-user"></i>
        </div>
    @endif
    <div style="flex:1;">
        <div style="font-size:20px;font-weight:800;">{{ $karyawan->nama }}</div>
        <div style="opacity:0.85;font-size:13px;margin-top:2px;">{{ $karyawan->jabatan }} — {{ $karyawan->department?->name ?? $karyawan->departemen }}</div>
        <div style="opacity:0.7;font-size:12px;margin-top:4px;">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:11px;opacity:0.7;">Status</div>
        <span style="background:rgba(255,255,255,0.2);padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">{{ ucfirst($karyawan->status) }}</span>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
    @php
        $hadir     = $statBulanIni->whereIn('status',['hadir','terlambat'])->count();
        $terlambat = $statBulanIni->where('status','terlambat')->count();
        $izin      = $statBulanIni->whereIn('status',['izin','sakit'])->count();
    @endphp
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#38a169;">{{ $hadir }}</div>
        <div style="font-size:11px;color:#718096;">Hadir Bulan Ini</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#e53e3e;">{{ $terlambat }}</div>
        <div style="font-size:11px;color:#718096;">Terlambat</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#d69e2e;">{{ $izin }}</div>
        <div style="font-size:11px;color:#718096;">Izin/Sakit</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#3182ce;">{{ $saldoCuti->sisa }}</div>
        <div style="font-size:11px;color:#718096;">Sisa Cuti</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#805ad5;">{{ $cutiPending + $lemburPending + $izinPending }}</div>
        <div style="font-size:11px;color:#718096;">Pengajuan Pending</div>
    </div>
    @if($spAktif > 0)
    <div style="background:#fff5f5;border-radius:12px;padding:16px;border:1.5px solid #feb2b2;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#c53030;">{{ $spAktif }}</div>
        <div style="font-size:11px;color:#c53030;font-weight:600;">SP Aktif</div>
    </div>
    @endif
    @if($karyawan->shift)
    <div style="background:#f3e8ff;border-radius:12px;padding:16px;border:1px solid #e9d8fd;text-align:center;">
        <div style="font-size:15px;font-weight:800;color:#553c9a;">{{ $karyawan->shift->nama_shift }}</div>
        <div style="font-size:11px;color:#805ad5;">{{ substr($karyawan->shift->jam_masuk,0,5) }} – {{ substr($karyawan->shift->jam_keluar,0,5) }}</div>
        <div style="font-size:10px;color:#a0aec0;margin-top:2px;">Shift Aktif</div>
    </div>
    @endif
</div>

{{-- Saldo Cuti & Ringkasan Lembur --}}
<div class="portal-grid-2" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-umbrella-beach" style="color:#276749;"></i> Saldo Cuti {{ now()->year }}</h6></div>
        <div class="card-body">
            @php
                $pctTerpakaiDash = $saldoCuti->jatah > 0 ? round($saldoCuti->terpakai / $saldoCuti->jatah * 100) : 0;
                $barColorDash    = $pctTerpakaiDash >= 90 ? '#c53030' : ($pctTerpakaiDash >= 70 ? '#c05621' : '#276749');
            @endphp
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
                <div style="background:#f0fff4;border:1px solid #9ae6b4;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#276749;">{{ $saldoCuti->jatah }}</div>
                    <div style="font-size:11px;color:#718096;">Jatah</div>
                </div>
                <div style="background:#fff5f5;border:1px solid #feb2b2;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#c53030;">{{ $saldoCuti->terpakai }}</div>
                    <div style="font-size:11px;color:#718096;">Terpakai</div>
                </div>
                <div style="background:#ebf8ff;border:1px solid #90cdf4;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#2b6cb0;">{{ $saldoCuti->sisa }}</div>
                    <div style="font-size:11px;color:#718096;">Sisa</div>
                </div>
            </div>
            <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
                <div style="background:{{ $barColorDash }};height:100%;width:{{ min($pctTerpakaiDash,100) }}%;"></div>
            </div>
            <div style="text-align:right;font-size:11px;color:#a0aec0;margin-top:4px;">{{ $pctTerpakaiDash }}% terpakai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-business-time" style="color:#c05621;"></i> Lembur Bulan Ini</h6></div>
        <div class="card-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;padding:24px;">
            <div style="font-size:32px;font-weight:800;color:#c05621;">{{ $lemburJamBulanIni ?: 0 }}</div>
            <div style="font-size:12px;color:#718096;margin-top:2px;">jam lembur disetujui</div>
            @if($lemburPending > 0)
            <div style="margin-top:8px;font-size:11px;color:#d69e2e;"><i class="fas fa-clock"></i> {{ $lemburPending }} pengajuan menunggu persetujuan</div>
            @endif
        </div>
    </div>
</div>

{{-- Status Pengajuan Terkini --}}
@if($pengajuanTerkini->count())
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-list-check" style="color:#4299e1;"></i> Status Pengajuan Terkini</h6></div>
    <div class="card-body" style="padding:0;">
        @foreach($pengajuanTerkini as $p)
        @php
            $statusColorDash = match($p['status']) {
                'disetujui' => ['#f0fff4','#276749','Disetujui'],
                'ditolak'   => ['#fff5f5','#c53030','Ditolak'],
                default     => ['#fffbeb','#c05621','Menunggu'],
            };
        @endphp
        <div style="padding:12px 18px;border-bottom:1px solid #f0f0f0;display:flex;gap:12px;align-items:center;">
            <div style="width:36px;height:36px;border-radius:9px;background:{{ $p['warna'][1] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas {{ $p['icon'] }}" style="color:{{ $p['warna'][0] }};font-size:14px;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:#a0aec0;">{{ $p['jenis'] }}</div>
                <div style="font-size:13px;font-weight:600;color:#2d3748;">{{ $p['keterangan'] }}</div>
            </div>
            <span style="background:{{ $statusColorDash[0] }};color:{{ $statusColorDash[1] }};padding:3px 10px;border-radius:14px;font-size:11px;font-weight:700;white-space:nowrap;">
                {{ $statusColorDash[2] }}
            </span>
        </div>
        @endforeach
    </div>
    <div style="padding:10px 18px;text-align:right;">
        <a href="{{ route('portal.profil-lengkap') }}#tab-cuti" style="font-size:12px;color:#4299e1;text-decoration:none;font-weight:600;">Lihat semua riwayat pengajuan <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
@endif

<div class="portal-grid-2">
    {{-- Absensi Hari Ini --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-fingerprint" style="color:#4299e1;"></i> Absensi Hari Ini</h6></div>
        <div class="card-body">
            @if($isLiburHariIni)
                <div style="text-align:center;padding:20px;color:#276749;">
                    <i class="fas fa-umbrella-beach" style="font-size:36px;margin-bottom:8px;display:block;"></i>
                    Hari Libur — {{ $namaLibur }}
                </div>
            @elseif($absensiHariIni)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="background:#f0fff4;border-radius:10px;padding:14px;text-align:center;">
                        <i class="fas fa-sign-in-alt" style="color:#38a169;font-size:20px;margin-bottom:6px;display:block;"></i>
                        <div style="font-size:20px;font-weight:800;color:#276749;">{{ substr($absensiHariIni->jam_masuk,0,5) }}</div>
                        <div style="font-size:11px;color:#718096;">Jam Masuk</div>
                    </div>
                    <div style="background:{{ $absensiHariIni->jam_keluar ? '#f0fff4' : '#f7fafc' }};border-radius:10px;padding:14px;text-align:center;">
                        <i class="fas fa-sign-out-alt" style="color:{{ $absensiHariIni->jam_keluar ? '#38a169' : '#a0aec0' }};font-size:20px;margin-bottom:6px;display:block;"></i>
                        <div style="font-size:20px;font-weight:800;color:{{ $absensiHariIni->jam_keluar ? '#276749' : '#a0aec0' }};">
                            {{ $absensiHariIni->jam_keluar ? substr($absensiHariIni->jam_keluar,0,5) : '--:--' }}
                        </div>
                        <div style="font-size:11px;color:#718096;">Jam Keluar</div>
                    </div>
                </div>
                <div style="margin-top:10px;text-align:center;">
                    <span class="badge badge-{{ $absensiHariIni->status }}">{{ ucfirst($absensiHariIni->status) }}</span>
                    @if($absensiHariIni->durasi_menit)
                    <span style="font-size:12px;color:#718096;margin-left:8px;">{{ intdiv($absensiHariIni->durasi_menit,60) }}j {{ $absensiHariIni->durasi_menit%60 }}m</span>
                    @endif
                </div>

                {{-- Banner & Form Alasan Terlambat --}}
                @if($absensiHariIni->status === 'terlambat')
                <div style="margin-top:14px;border-radius:10px;overflow:hidden;border:1.5px solid #fbd38d;">
                    @if($absensiHariIni->keterangan_terlambat)
                        {{-- Sudah ada alasan --}}
                        <div style="background:#fffbeb;padding:12px 14px;display:flex;align-items:flex-start;gap:10px;">
                            <i class="fas fa-check-circle" style="color:#d69e2e;margin-top:2px;flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:12px;font-weight:700;color:#92400e;">Alasan Keterlambatan</div>
                                <div style="font-size:13px;color:#78350f;margin-top:2px;">{{ $absensiHariIni->keterangan_terlambat }}</div>
                                <button onclick="document.getElementById('form-alasan-terlambat').style.display='block';this.style.display='none';"
                                    style="font-size:11px;margin-top:6px;background:none;border:none;color:#c05621;cursor:pointer;padding:0;text-decoration:underline;">
                                    <i class="fas fa-edit"></i> Ubah alasan
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Belum ada alasan - tampil banner peringatan --}}
                        <div style="background:#fff7ed;padding:12px 14px;display:flex;align-items:flex-start;gap:10px;">
                            <i class="fas fa-exclamation-triangle" style="color:#c05621;margin-top:2px;flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:12px;font-weight:700;color:#c05621;">Anda Tercatat Terlambat</div>
                                <div style="font-size:12px;color:#78350f;">Silakan isi alasan keterlambatan jika ada. Keterlambatan tanpa alasan akan dicatat sebagai pelanggaran.</div>
                            </div>
                        </div>
                    @endif
                    <div id="form-alasan-terlambat" style="{{ $absensiHariIni->keterangan_terlambat ? 'display:none;' : '' }}background:#fffef0;padding:12px 14px;border-top:1px solid #fbd38d;">
                        <form method="POST" action="{{ route('portal.absensi.alasan-terlambat') }}">
                            @csrf
                            <input type="hidden" name="absensi_id" value="{{ $absensiHariIni->id }}">
                            <textarea name="keterangan_terlambat" rows="2" class="form-control"
                                style="font-size:12px;margin-bottom:8px;"
                                placeholder="Tuliskan alasan keterlambatan Anda...">{{ old('keterangan_terlambat', $absensiHariIni->keterangan_terlambat) }}</textarea>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                                @foreach(['Kemacetan','Ban bocor','Hujan deras','Urusan keluarga','Kendaraan mogok'] as $al)
                                <button type="button" onclick="this.closest('div').previousElementSibling.value='{{ $al }}'"
                                    style="font-size:11px;padding:3px 9px;border:1px solid #fbd38d;border-radius:5px;background:#fffbeb;cursor:pointer;color:#92400e;">
                                    {{ $al }}
                                </button>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm" style="background:#d97706;color:white;font-size:12px;">
                                <i class="fas fa-paper-plane"></i> Kirim Alasan
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            @else
                <div style="text-align:center;padding:20px;color:#a0aec0;">
                    <i class="fas fa-clock" style="font-size:36px;margin-bottom:8px;display:block;"></i>
                    Belum absen hari ini
                </div>
            @endif
        </div>
    </div>

    {{-- Shortcut Menu --}}
    <div class="card">
        <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-th" style="color:#4299e1;"></i> Menu</h6></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <a href="{{ route('portal.absensi') }}" style="background:#ebf8ff;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #bee3f8;">
                    <i class="fas fa-calendar-check" style="font-size:22px;color:#2b6cb0;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#2b6cb0;">Riwayat Absensi</div>
                </a>
                <a href="{{ route('portal.profil-lengkap') }}#tab-cuti" style="background:#f0fff4;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #9ae6b4;">
                    <i class="fas fa-calendar-alt" style="font-size:22px;color:#276749;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#276749;">Cuti / Izin / Sakit</div>
                    @if($cutiPending + $izinPending > 0)<div style="font-size:10px;color:#276749;">{{ $cutiPending + $izinPending }} pending</div>@endif
                </a>
                <a href="{{ route('portal.profil-lengkap') }}#tab-biodata" style="background:#f5f3ff;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #d6bcfa;">
                    <i class="fas fa-user" style="font-size:22px;color:#6b46c1;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#6b46c1;">Profil Saya</div>
                </a>
                <a href="{{ route('portal.notifikasi') }}" style="background:#ebf8ff;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #bee3f8;">
                    <i class="fas fa-bell" style="font-size:22px;color:#2b6cb0;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#2b6cb0;">Notifikasi</div>
                </a>
                <a href="{{ route('portal.profil-lengkap') }}#tab-pelatihan" style="background:#f0fff4;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #9ae6b4;">
                    <i class="fas fa-certificate" style="font-size:22px;color:#276749;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#276749;">Pelatihan</div>
                    <div style="font-size:10px;color:#48bb78;">& Sertifikasi</div>
                </a>
                <a href="{{ route('portal.profil-lengkap') }}#tab-fmk" style="background:#fffbeb;border-radius:10px;padding:16px;text-align:center;text-decoration:none;border:1px solid #f6ad55;">
                    <i class="fas fa-walking" style="font-size:22px;color:#c05621;display:block;margin-bottom:6px;"></i>
                    <div style="font-size:12px;font-weight:700;color:#c05621;">Izin Keluar</div>
                    <div style="font-size:10px;color:#ed8936;">Meninggalkan Kerja</div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Kalender Absensi Bulan Ini --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h6 style="margin:0;font-weight:700;"><i class="fas fa-calendar-alt" style="color:#3182ce;"></i> Kalender Absensi — {{ $startBulan->translatedFormat('F Y') }}</h6>
        <div style="display:flex;gap:12px;font-size:11px;color:#718096;">
            <span><span style="width:10px;height:10px;background:#48bb78;border-radius:3px;display:inline-block;margin-right:3px;"></span>Hadir</span>
            <span><span style="width:10px;height:10px;background:#ed8936;border-radius:3px;display:inline-block;margin-right:3px;"></span>Terlambat</span>
            <span><span style="width:10px;height:10px;background:#3182ce;border-radius:3px;display:inline-block;margin-right:3px;"></span>Izin/Sakit</span>
            <span><span style="width:10px;height:10px;background:#fc8181;border-radius:3px;display:inline-block;margin-right:3px;"></span>Alpha</span>
            <span><span style="width:10px;height:10px;background:#c6f6d5;border-radius:3px;display:inline-block;margin-right:3px;"></span>Libur</span>
        </div>
    </div>
    <div class="card-body" style="padding:14px;">
        @php
            $firstDow = ($startBulan->dayOfWeek + 6) % 7; // Senin=0
            $daysInMonth = $endBulan->day;
        @endphp
        <div class="kalender-7col" style="margin-bottom:6px;">
            @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h)
            <div style="text-align:center;font-size:10px;font-weight:700;color:#718096;padding:4px 0;">{{ $h }}</div>
            @endforeach
        </div>
        <div class="kalender-7col">
            {{-- Padding awal bulan --}}
            @for($p = 0; $p < $firstDow; $p++)
            <div></div>
            @endfor

            @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $tgl    = \Carbon\Carbon::create($startBulan->year, $startBulan->month, $d);
                $key    = $tgl->format('Y-m-d');
                $abs    = $absensiBulan[$key] ?? null;
                $libur  = $hariLiburBulan[$key] ?? null;
                $isMing = $tgl->dayOfWeek === 0; // Minggu
                $isSab  = $tgl->dayOfWeek === 6;
                $isToday= $tgl->isToday();
                $isFuture= $tgl->isFuture();

                $isKerjaPengganti = \App\Models\HariLibur::isKerjaPengganti($key);
                if ($libur) { $bg = '#c6f6d5'; $fc = '#276749'; }
                elseif ($isKerjaPengganti) { $bg = '#fef3c7'; $fc = '#b45309'; } // Sabtu masuk kerja = kuning
                elseif ($isMing) { $bg = '#fee2e2'; $fc = '#dc2626'; }  // Minggu = merah
                elseif ($isSab)  { $bg = '#ffedd5'; $fc = '#c2410c'; }  // Sabtu  = oranye
                elseif ($abs) {
                    $bg = match($abs->status) {
                        'hadir'     => '#f0fff4', 'terlambat' => '#fffbeb',
                        'izin','sakit' => '#ebf8ff', default => '#fff5f5'
                    };
                    $fc = match($abs->status) {
                        'hadir'     => '#276749', 'terlambat' => '#c05621',
                        'izin','sakit' => '#2b6cb0', default => '#c53030'
                    };
                } elseif ($isFuture) {
                    $bg = 'white'; $fc = '#cbd5e0';
                } else {
                    $bg = '#fff5f5'; $fc = '#c53030'; // Alpha (masa lalu, tidak hadir)
                }
                $dotColor = '';
                if ($abs) {
                    $dotColor = match($abs->status) {
                        'hadir'     => '#48bb78', 'terlambat' => '#ed8936',
                        'izin','sakit' => '#3182ce', default => '#fc8181'
                    };
                }
            @endphp
            <div title="{{ $tgl->translatedFormat('l, d F Y') }}{{ $abs ? ' — '.ucfirst($abs->status) : ($libur ? ' — '.$libur : '') }}"
                 style="background:{{ $bg }};border:{{ $isToday ? '2px solid #553c9a' : '1px solid #e2e8f0' }};border-radius:8px;padding:4px 2px;text-align:center;min-height:40px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div style="font-size:12px;font-weight:{{ $isToday ? '800' : '600' }};color:{{ $isToday ? '#553c9a' : $fc }};">{{ $d }}</div>
                @if($dotColor)
                <div style="width:6px;height:6px;background:{{ $dotColor }};border-radius:50%;margin-top:2px;"></div>
                @elseif($isKerjaPengganti)
                <i class="fas fa-briefcase" style="font-size:8px;color:#b45309;margin-top:2px;" title="Kerja Pengganti"></i>
                @elseif($libur)
                <i class="fas fa-umbrella-beach" style="font-size:8px;color:#276749;margin-top:2px;"></i>
                @endif
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- 7 Hari Terakhir --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h6 style="margin:0;font-weight:700;"><i class="fas fa-chart-bar" style="color:#4299e1;"></i> 7 Hari Terakhir</h6></div>
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Tanggal</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Masuk</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Keluar</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Durasi</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi7Hari as $a)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 16px;font-weight:600;">{{ $a->tanggal->translatedFormat('D, d M') }}</td>
                        <td style="padding:10px 16px;">{{ substr($a->jam_masuk,0,5) }}</td>
                        <td style="padding:10px 16px;color:#718096;">{{ $a->jam_keluar ? substr($a->jam_keluar,0,5) : '—' }}</td>
                        <td style="padding:10px 16px;color:#718096;">
                            @if($a->durasi_menit)
                                {{ intdiv($a->durasi_menit,60) }}j {{ $a->durasi_menit%60 }}m
                            @else — @endif
                        </td>
                        <td style="padding:10px 16px;"><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding:24px;text-align:center;color:#a0aec0;">Belum ada data absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pengumuman & Memo --}}
@if($pengumuman->count())
<div style="margin-top:24px;">
    <h6 style="font-weight:800;color:#2d3748;margin-bottom:12px;font-size:14px;"><i class="fas fa-bullhorn" style="color:#c05621;"></i> Pengumuman & Memo</h6>
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($pengumuman as $p)
        @php $bj = $p->badge_jenis; $bp = $p->badge_prioritas; @endphp
        <div style="background:white;border-radius:12px;padding:16px 18px;border-left:4px solid {{ $bp['text'] }};box-shadow:0 2px 6px rgba(0,0,0,0.05);">
            <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                <span style="background:{{ $bj['bg'] }};color:{{ $bj['text'] }};padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;"><i class="{{ $bj['icon'] }}"></i> {{ $bj['label'] }}</span>
                <span style="background:{{ $bp['bg'] }};color:{{ $bp['text'] }};padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;">{{ $bp['label'] }}</span>
            </div>
            <div style="font-weight:700;color:#2d3748;font-size:14px;margin-bottom:6px;">{{ $p->judul }}</div>
            <div style="font-size:12px;color:#4a5568;line-height:1.6;">{{ Str::limit(strip_tags($p->isi), 250) }}</div>
            <div style="margin-top:8px;font-size:11px;color:#a0aec0;">
                {{ $p->tanggal_mulai->translatedFormat('d F Y') }}
                @if($p->lampiran)
                &nbsp;·&nbsp; <a href="{{ asset('storage/'.$p->lampiran) }}" target="_blank" style="color:#2b6cb0;"><i class="fas fa-paperclip"></i> Lampiran</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Popup Pengumuman --}}
@if($popupPengumuman->count())
<style>
#popup-pengumuman-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
#popup-pengumuman-overlay.aktif {
    display: flex !important;
}
</style>
<div id="popup-pengumuman-overlay">
    <div style="background:white;border-radius:16px;padding:28px 24px 24px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,0.3);position:relative;max-height:90vh;overflow-y:auto;">
        <button onclick="dismissPopup()" style="position:absolute;top:12px;right:14px;background:none;border:none;font-size:26px;color:#a0aec0;cursor:pointer;line-height:1;">&times;</button>
        <div style="margin-bottom:10px;">
            <i class="fas fa-bullhorn" style="color:#c05621;margin-right:6px;"></i>
            <span style="font-size:12px;font-weight:700;color:#718096;text-transform:uppercase;letter-spacing:0.5px;">Pengumuman</span>
        </div>
        <div id="popup-pengumuman-body"></div>
    </div>
</div>
<script>
(function() {
    var popups     = @json($popupPengumuman);
    var today      = new Date().toISOString().slice(0,10);
    var uid        = '{{ auth()->id() }}';
    var loginToken = '{{ session("login_token") }}'; // unik per login — normal popup reset tiap login

    // mendesak  → selalu muncul setiap buka halaman
    // penting   → 1x per hari (localStorage per user per hari)
    // normal    → 1x per login session (sessionStorage per login_token)
    var sessKey  = 'popup_sess_'  + uid + '_' + loginToken;
    var dailyKey = 'popup_daily_' + uid + '_' + today;
    var sessionDismissed = JSON.parse(sessionStorage.getItem(sessKey)  || '[]');
    var dailyDismissed   = JSON.parse(localStorage.getItem(dailyKey)   || '[]');

    var toShow = popups.filter(function(p) {
        if (p.prioritas === 'mendesak') return true;                          // selalu tampil
        if (p.prioritas === 'penting')  return !dailyDismissed.includes(p.id); // 1x per hari
        return !sessionDismissed.includes(p.id);                              // 1x per sesi
    });
    if (!toShow.length) return;

    var current = 0;

    function saveDismiss(p) {
        if (p.prioritas === 'mendesak') return; // tidak disimpan, selalu muncul
        if (p.prioritas === 'penting') {
            if (!dailyDismissed.includes(p.id)) dailyDismissed.push(p.id);
            localStorage.setItem(dailyKey, JSON.stringify(dailyDismissed));
        } else {
            if (!sessionDismissed.includes(p.id)) sessionDismissed.push(p.id);
            sessionStorage.setItem(sessKey, JSON.stringify(sessionDismissed));
        }
    }

    function badgeColor(prioritas) {
        if (prioritas === 'mendesak') return { bg:'#fed7e2', text:'#c53030', label:'Mendesak ⚠' };
        if (prioritas === 'penting')  return { bg:'#fef9c3', text:'#854d0e', label:'Penting' };
        return { bg:'#e2e8f0', text:'#4a5568', label:'Normal' };
    }

    function hintText(prioritas) {
        if (prioritas === 'mendesak') return '<div style="margin-top:6px;font-size:11px;color:#e53e3e;"><i class="fas fa-redo"></i> Pengumuman mendesak — akan muncul setiap kali membuka halaman</div>';
        if (prioritas === 'penting')  return '<div style="margin-top:6px;font-size:11px;color:#b7791f;"><i class="fas fa-clock"></i> Akan muncul kembali besok</div>';
        return '';
    }

    function renderPopup(p) {
        var badge   = badgeColor(p.prioritas);
        var counter = toShow.length > 1
            ? '<span style="font-size:11px;color:#718096;">' + (current+1) + ' / ' + toShow.length + '</span>'
            : '';
        var lampiranBtn = p.lampiran
            ? '<div style="margin-top:8px;"><a href="' + p.lampiran + '" target="_blank" style="font-size:12px;color:#2b6cb0;text-decoration:none;"><i class="fas fa-paperclip"></i> Lihat Lampiran</a></div>'
            : '';
        var nextBtn = (current + 1 < toShow.length)
            ? '<button onclick="nextPopup()" style="padding:8px 18px;background:#667eea;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Berikutnya ›</button>'
            : '';
        var closeLabel = p.prioritas === 'mendesak' ? 'Sudah Dibaca' : 'Tutup';

        document.getElementById('popup-pengumuman-body').innerHTML =
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">'
            + '<span style="background:' + badge.bg + ';color:' + badge.text + ';padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;">' + badge.label + '</span>'
            + counter
            + '</div>'
            + '<div style="font-size:17px;font-weight:800;color:#2d3748;margin-bottom:10px;">' + p.judul + '</div>'
            + '<div style="font-size:13px;color:#4a5568;line-height:1.7;white-space:pre-wrap;max-height:220px;overflow-y:auto;padding-right:4px;">' + p.isi + '</div>'
            + '<div style="margin-top:10px;font-size:11px;color:#a0aec0;">Berlaku mulai: ' + p.tanggal + '</div>'
            + hintText(p.prioritas)
            + lampiranBtn
            + '<div style="margin-top:20px;display:flex;justify-content:flex-end;gap:8px;">'
            + nextBtn
            + '<button onclick="dismissPopup()" style="padding:8px 18px;background:#e2e8f0;color:#4a5568;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">' + closeLabel + '</button>'
            + '</div>';
    }

    var overlay = document.getElementById('popup-pengumuman-overlay');

    // Klik di area gelap sekeliling kartu (bukan di kartu putihnya sendiri) akan
    // menutup popup untuk prioritas normal/penting — supaya klik pertama user
    // tidak "hilang tertangkap overlay" tanpa efek apapun (sebelumnya user harus
    // klik 2x: klik pertama ketutup overlay, klik kedua baru sampai ke menu).
    // Popup "mendesak" sengaja TIDAK bisa ditutup dengan klik luar — harus lewat
    // tombol "Sudah Dibaca" karena memang didesain wajib dibaca eksplisit.
    overlay.addEventListener('click', function (e) {
        if (e.target !== overlay) return; // klik di dalam kartu, biarkan
        if (toShow[current] && toShow[current].prioritas === 'mendesak') return;
        window.dismissPopup();
    });

    window.nextPopup = function() {
        saveDismiss(toShow[current]);
        current++;
        if (current < toShow.length) {
            renderPopup(toShow[current]);
        } else {
            overlay.classList.remove('aktif');
        }
    };

    window.dismissPopup = function() {
        saveDismiss(toShow[current]);
        overlay.classList.remove('aktif');
    };

    function showPopup() {
        renderPopup(toShow[current]);
        overlay.classList.add('aktif');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showPopup);
    } else {
        showPopup();
    }
})();
</script>
@endif

@endsection
