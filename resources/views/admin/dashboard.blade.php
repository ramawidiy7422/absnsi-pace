@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stat-card-v2 {
        background: white; border-radius: 14px; padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        display: flex; align-items: center; gap: 16px;
        transition: transform 0.2s;
    }
    .stat-card-v2:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
    .stat-icon-box {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .stat-icon-box.blue   { background: #ebf8ff; color: #2b6cb0; }
    .stat-icon-box.green  { background: #f0fff4; color: #276749; }
    .stat-icon-box.yellow { background: #fffaf0; color: #c05621; }
    .stat-icon-box.red    { background: #fff5f5; color: #c53030; }
    .stat-icon-box.purple { background: #faf5ff; color: #6b46c1; }
    .stat-val  { font-size: 30px; font-weight: 800; color: #2d3748; line-height: 1; }
    .stat-lbl  { font-size: 12px; color: #718096; margin-top: 4px; }
    .stat-sub  { font-size: 11px; margin-top: 4px; }

    /* Alert banner */
    .alert-banner {
        border-radius: 12px; padding: 14px 18px;
        display: flex; align-items: flex-start; gap: 12px;
        margin-bottom: 0;
    }
    .alert-banner.warning { background: #fffaf0; border: 1px solid #f6ad55; }
    .alert-banner.danger  { background: #fff5f5; border: 1px solid #fc8181; }
    .alert-banner.info    { background: #ebf8ff; border: 1px solid #63b3ed; }
    .alert-banner-icon { font-size: 20px; flex-shrink: 0; padding-top: 1px; }
    .alert-banner.warning .alert-banner-icon { color: #dd6b20; }
    .alert-banner.danger  .alert-banner-icon { color: #c53030; }
    .alert-banner.info    .alert-banner-icon { color: #2b6cb0; }
    .alert-banner-title { font-size: 13px; font-weight: 700; margin-bottom: 6px; }
    .alert-banner.warning .alert-banner-title { color: #c05621; }
    .alert-banner.danger  .alert-banner-title { color: #c53030; }
    .alert-banner.info    .alert-banner-title { color: #2b6cb0; }

    /* Chip list */
    .chip-list { display: flex; flex-wrap: wrap; gap: 6px; }
    .chip {
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500;
        background: #f7fafc; border: 1px solid #e2e8f0; color: #4a5568;
    }
    .chip.red    { background: #fff5f5; border-color: #feb2b2; color: #c53030; }
    .chip.yellow { background: #fffaf0; border-color: #fbd38d; color: #c05621; }
    .chip.blue   { background: #ebf8ff; border-color: #90cdf4; color: #2b6cb0; }

    /* Chart wrapper */
    .chart-box { position: relative; width: 100%; }

    /* Progress bar kehadiran */
    .attend-bar-wrap { background: #f0f2f5; border-radius: 10px; height: 10px; overflow: hidden; }
    .attend-bar-fill { height: 100%; border-radius: 10px; transition: width 1s ease; }
</style>
@endpush

@section('content')

{{-- ══════════════ STAT CARDS ══════════════ --}}
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:20px;">

    <div class="stat-card-v2">
        <div class="stat-icon-box blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-val">{{ $totalKaryawan }}</div>
            <div class="stat-lbl">Total Aktif</div>
        </div>
    </div>

    <div class="stat-card-v2">
        <div class="stat-icon-box green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-val" id="stat-hadir">{{ $hadirHariIni }}</div>
            <div class="stat-lbl">Hadir Hari Ini</div>
            @if($totalKaryawan > 0)
                <div class="stat-sub" style="color:#48bb78;">{{ round($hadirHariIni/$totalKaryawan*100) }}% kehadiran</div>
            @endif
        </div>
    </div>

    <div class="stat-card-v2">
        <div class="stat-icon-box yellow"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-val" id="stat-terlambat">{{ $terlambatHariIni }}</div>
            <div class="stat-lbl">Terlambat</div>
        </div>
    </div>

    <div class="stat-card-v2">
        <div class="stat-icon-box red"><i class="fas fa-times-circle"></i></div>
        <div>
            <div class="stat-val">{{ $absenHariIni }}</div>
            <div class="stat-lbl">Izin/Sakit/Alpha</div>
        </div>
    </div>

    <div class="stat-card-v2">
        <div class="stat-icon-box purple"><i class="fas fa-user-clock"></i></div>
        <div>
            <div class="stat-val" id="stat-belum-absen" style="color:{{ $belumAbsen > 0 ? '#6b46c1' : '#48bb78' }}">{{ $belumAbsen }}</div>
            <div class="stat-lbl">
                Belum Absen
                <span id="notif-badge" style="display:{{ $belumAbsen > 0 ? 'inline-block' : 'none' }};background:#fc8181;color:white;font-size:9px;padding:1px 5px;border-radius:8px;margin-left:3px;font-weight:700;">{{ $belumAbsen }}</span>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════ PROGRESS KEHADIRAN ══════════════ --}}
@if($totalKaryawan > 0)
<div class="card" style="margin-bottom:20px; padding:16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <span style="font-size:13px; font-weight:600; color:#2d3748;"><i class="fas fa-chart-line" style="color:#4299e1;"></i> Kehadiran Hari Ini</span>
        <span style="font-size:12px; color:#718096;">{{ $hadirHariIni }} / {{ $totalKaryawan }} karyawan</span>
    </div>
    <div class="attend-bar-wrap">
        <div class="attend-bar-fill" id="attendBar"
             style="width:0%; background:linear-gradient(90deg,#48bb78,#38a169);"
             data-target="{{ round($hadirHariIni/$totalKaryawan*100) }}">
        </div>
    </div>
    <div style="display:flex; gap:20px; margin-top:10px;">
        <span style="font-size:11px; color:#48bb78;"><span style="display:inline-block;width:10px;height:10px;background:#48bb78;border-radius:2px;margin-right:4px;"></span>Hadir {{ $hadirHariIni - $terlambatHariIni }}</span>
        <span style="font-size:11px; color:#ed8936;"><span style="display:inline-block;width:10px;height:10px;background:#ed8936;border-radius:2px;margin-right:4px;"></span>Terlambat {{ $terlambatHariIni }}</span>
        <span style="font-size:11px; color:#fc8181;"><span style="display:inline-block;width:10px;height:10px;background:#fc8181;border-radius:2px;margin-right:4px;"></span>Tidak Hadir {{ $absenHariIni }}</span>
        <span style="font-size:11px; color:#a0aec0;"><span style="display:inline-block;width:10px;height:10px;background:#e2e8f0;border-radius:2px;margin-right:4px;"></span>Belum Absen {{ $belumAbsen }}</span>
    </div>
</div>
@endif

{{-- ══════════════ ALERT SECTION ══════════════ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

    {{-- Alert terlambat --}}
    @if($karyawanTerlambat->count() > 0)
    <div class="card">
        <div class="card-header">
            <h6 style="color:#c05621;"><i class="fas fa-clock"></i> Terlambat Hari Ini <span style="background:#fbd38d;color:#c05621;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px;">{{ $karyawanTerlambat->count() }}</span></h6>
        </div>
        <div class="card-body" style="padding:0; max-height:200px; overflow-y:auto;">
            @foreach($karyawanTerlambat as $a)
            <div style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f0f0f0;">
                <div style="width:36px;height:36px;border-radius:50%;background:#fffaf0;border:2px solid #fbd38d;display:flex;align-items:center;justify-content:center;font-weight:700;color:#c05621;font-size:13px;flex-shrink:0;">
                    {{ strtoupper(substr($a->karyawan->nama, 0, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:#2d3748;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $a->karyawan->nama }}</div>
                    <div style="font-size:11px;color:#718096;">{{ $a->karyawan->departemen }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;font-weight:600;color:#c05621;">{{ $a->jam_masuk }}</div>
                    <div style="font-size:10px;color:#a0aec0;">jam masuk</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header"><h6 style="color:#48bb78;"><i class="fas fa-check-circle"></i> Keterlambatan</h6></div>
        <div class="card-body" style="text-align:center; padding:30px 20px;">
            <i class="fas fa-thumbs-up" style="font-size:36px;color:#48bb78;margin-bottom:10px;"></i>
            <p style="color:#48bb78;font-weight:600;margin:0;">Tidak ada keterlambatan hari ini!</p>
        </div>
    </div>
    @endif

    {{-- Alert belum absen --}}
    <div class="card">
        <div class="card-header">
            <h6 style="color:#6b46c1;"><i class="fas fa-user-clock"></i> Belum Absen <span style="background:#e9d8fd;color:#6b46c1;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px;">{{ $belumAbsenList->count() }}</span></h6>
            <a href="{{ route('admin.karyawan.index') }}" style="font-size:11px;color:#4299e1;text-decoration:none;">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding:0; max-height:200px; overflow-y:auto;">
            @forelse($belumAbsenList as $k)
            <div style="display:flex; align-items:center; gap:10px; padding:9px 16px; border-bottom:1px solid #f0f0f0;">
                <div style="width:32px;height:32px;border-radius:50%;background:#faf5ff;border:2px solid #d6bcfa;display:flex;align-items:center;justify-content:center;font-weight:700;color:#6b46c1;font-size:12px;flex-shrink:0;">
                    {{ strtoupper(substr($k->nama, 0, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#2d3748;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $k->nama }}</div>
                    <div style="font-size:11px;color:#a0aec0;">{{ $k->departemen }} · {{ $k->shift->nama_shift ?? '-' }}</div>
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;">
                <i class="fas fa-check-double" style="font-size:30px;color:#48bb78;margin-bottom:8px;"></i>
                <p style="color:#48bb78;font-weight:600;margin:0;font-size:13px;">Semua karyawan sudah absen!</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ══════════════ GRAFIK ══════════════ --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- Bar Chart 7 Hari --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-bar" style="color:#4299e1;"></i> Absensi 7 Hari Terakhir</h6>
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-sm btn-primary">Detail</a>
        </div>
        <div class="card-body">
            <div class="chart-box"><canvas id="chartBar" height="120"></canvas></div>
        </div>
    </div>

    {{-- Pie Chart Hari Ini --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-pie" style="color:#805ad5;"></i> Status Hari Ini</h6>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;align-items:center;">
            <div style="width:160px;height:160px;"><canvas id="chartPie"></canvas></div>
            <div style="margin-top:14px;width:100%;">
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#48bb78;border-radius:2px;margin-right:5px;"></span>Hadir</span><strong>{{ $pieData['hadir'] }}</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#ed8936;border-radius:2px;margin-right:5px;"></span>Terlambat</span><strong>{{ $pieData['terlambat'] }}</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#63b3ed;border-radius:2px;margin-right:5px;"></span>Izin</span><strong>{{ $pieData['izin'] }}</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#f6ad55;border-radius:2px;margin-right:5px;"></span>Sakit</span><strong>{{ $pieData['sakit'] }}</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#fc8181;border-radius:2px;margin-right:5px;"></span>Alpha</span><strong>{{ $pieData['alpha'] }}</strong></div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;"><span><span style="display:inline-block;width:10px;height:10px;background:#e2e8f0;border-radius:2px;margin-right:5px;"></span>Belum</span><strong>{{ $pieData['belum'] }}</strong></div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════ BAWAH: ABSENSI TERBARU + QUICK ACCESS ══════════════ --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;">

    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-history"></i> Absensi Terbaru Hari Ini</h6>
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding:0;">
            @if($absensiTerbaru->isEmpty())
                <div style="padding:30px; text-align:center; color:#718096;">
                    <i class="fas fa-inbox" style="font-size:36px; margin-bottom:10px; display:block;"></i>
                    <p>Belum ada absensi hari ini</p>
                </div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jam Masuk</th>
                        <th>Status</th>
                        <th>Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absensiTerbaru as $a)
                    <tr>
                        <td>
                            <strong>{{ $a->karyawan->nama }}</strong><br>
                            <small style="color:#718096;">{{ $a->karyawan->departemen }}</small>
                        </td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td>
                            @if(($a->metode ?? 'wajah') === 'qrcode')
                                <span style="font-size:11px;background:#f3e8ff;color:#805ad5;padding:2px 8px;border-radius:10px;"><i class="fas fa-qrcode"></i> QR</span>
                            @elseif(($a->metode ?? 'wajah') === 'manual')
                                <span style="font-size:11px;background:#e2e8f0;color:#4a5568;padding:2px 8px;border-radius:10px;"><i class="fas fa-keyboard"></i> Manual</span>
                            @else
                                <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 8px;border-radius:10px;"><i class="fas fa-face-smile"></i> Wajah</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        {{-- Quick Access --}}
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-bolt" style="color:#ed8936;"></i> Akses Cepat</h6></div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('kiosk.index') }}" target="_blank" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-camera"></i> Buka Terminal Absensi
                </a>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-success" style="justify-content:center;">
                    <i class="fas fa-users"></i> Kelola Karyawan
                </a>
                <a href="{{ route('admin.absensi.create') }}" class="btn btn-warning" style="justify-content:center;">
                    <i class="fas fa-plus"></i> Input Absensi Manual
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="fas fa-chart-bar"></i> Lihat Laporan
                </a>
                <a href="{{ route('admin.karyawan.print-qr-semua') }}" target="_blank" class="btn" style="background:#805ad5;color:white;justify-content:center;">
                    <i class="fas fa-qrcode"></i> Print Semua QR
                </a>
            </div>
        </div>

        {{-- Info Sistem --}}
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-info-circle"></i> Info Sistem</h6></div>
            <div class="card-body" style="font-size:12px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex;justify-content:space-between;padding:8px;background:#f7fafc;border-radius:6px;">
                    <span style="color:#718096;"><i class="fas fa-calendar"></i> Tanggal</span>
                    <strong>{{ now()->translatedFormat('d M Y') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px;background:#f7fafc;border-radius:6px;">
                    <span style="color:#718096;"><i class="fas fa-clock"></i> Jam</span>
                    <strong id="jam-sekarang">{{ now()->format('H:i:s') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px;background:#f7fafc;border-radius:6px;">
                    <span style="color:#718096;"><i class="fas fa-user-shield"></i> Login</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>

                {{-- Status Python Service --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-radius:6px;" id="pythonStatusBox" style="background:#f7fafc;">
                    <span style="color:#718096;"><i class="fas fa-brain"></i> AI Service</span>
                    <span id="pythonStatus" style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;background:#e2e8f0;color:#718096;">
                        <i class="fas fa-circle-notch fa-spin"></i> Cek...
                    </span>
                </div>

                <a href="{{ route('admin.laporan.rekap-bulanan') }}?bulan={{ now()->month }}&tahun={{ now()->year }}" style="display:block;text-align:center;margin-top:4px;padding:7px;background:#f3e8ff;color:#6b46c1;border-radius:6px;font-weight:600;text-decoration:none;font-size:12px;">
                    <i class="fas fa-calendar-check"></i> Rekap Bulan {{ now()->translatedFormat('F Y') }}
                </a>
                <a href="{{ route('admin.laporan.index') }}?bulan={{ now()->month }}&tahun={{ now()->year }}" style="display:block;text-align:center;padding:7px;background:#ebf8ff;color:#2b6cb0;border-radius:6px;font-weight:600;text-decoration:none;font-size:12px;">
                    <i class="fas fa-file-alt"></i> Laporan Detail
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
// Jam real-time di info sistem
setInterval(() => {
    document.getElementById('jam-sekarang').textContent =
        new Date().toLocaleTimeString('id-ID');
}, 1000);

// ── Polling notifikasi realtime (tiap 30 detik) ──────────────────────────
function pollDashboard() {
    fetch('{{ route("admin.dashboard-realtime") }}')
        .then(r => r.json())
        .then(d => {
            // Update stat cards
            const elBelum = document.getElementById('stat-belum-absen');
            const elHadir = document.getElementById('stat-hadir');
            const elTerlambat = document.getElementById('stat-terlambat');
            if (elBelum)    elBelum.textContent    = d.belumAbsen;
            if (elHadir)    elHadir.textContent    = d.hadir;
            if (elTerlambat) elTerlambat.textContent = d.terlambat;

            // Toast notifikasi jika ada karyawan baru absen
            const prevHadir = parseInt(sessionStorage.getItem('lastHadir') || 0);
            if (d.hadir > prevHadir && prevHadir > 0) {
                showToast(`${d.hadir - prevHadir} karyawan baru absen`, 'success');
            }
            sessionStorage.setItem('lastHadir', d.hadir);

            // Badge notif belum absen
            const badge = document.getElementById('notif-badge');
            if (badge) {
                badge.textContent = d.belumAbsen;
                badge.style.display = d.belumAbsen > 0 ? 'inline-block' : 'none';
            }
        })
        .catch(() => {});
}
pollDashboard();
setInterval(pollDashboard, 30000);

// Toast notification
function showToast(msg, type) {
    const toast = document.createElement('div');
    const colors = { success: '#276749', warning: '#c05621', error: '#c53030' };
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background:${colors[type]||colors.success}; color:white;
        padding:12px 20px; border-radius:10px; font-size:13px; font-weight:600;
        box-shadow:0 8px 24px rgba(0,0,0,0.2); animation:slideInToast 0.3s ease;
        display:flex; align-items:center; gap:8px; max-width:320px;
    `;
    toast.innerHTML = `<i class="fas fa-bell"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity='0'; toast.style.transition='opacity 0.4s'; setTimeout(()=>toast.remove(),400); }, 4000);
}

// Cek status Python service
fetch('{{ route("admin.python-status") }}')
    .then(r => r.json())
    .then(d => {
        const el  = document.getElementById('pythonStatus');
        const box = document.getElementById('pythonStatusBox');
        if (d.online) {
            el.innerHTML  = `<i class="fas fa-circle" style="color:#48bb78;font-size:8px;vertical-align:middle;"></i> Online`;
            el.style.cssText = 'font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;background:#f0fff4;color:#276749;';
            box.style.background = '#f0fff4';
        } else {
            el.innerHTML  = `<i class="fas fa-circle" style="color:#fc8181;font-size:8px;vertical-align:middle;"></i> Offline`;
            el.style.cssText = 'font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;background:#fff5f5;color:#c53030;';
            box.style.background = '#fff5f5';
        }
    })
    .catch(() => {
        const el = document.getElementById('pythonStatus');
        el.innerHTML = `<i class="fas fa-circle" style="color:#fc8181;font-size:8px;vertical-align:middle;"></i> Offline`;
        el.style.cssText = 'font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;background:#fff5f5;color:#c53030;';
    });

// Animasi progress bar kehadiran
window.addEventListener('load', () => {
    const bar = document.getElementById('attendBar');
    if (bar) bar.style.width = bar.dataset.target + '%';
});

// ── Bar Chart 7 Hari ─────────────────────────────────────────────────────
const grafikData = @json($grafik7Hari);
new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
        labels: grafikData.map(d => d.label),
        datasets: [
            {
                label: 'Hadir',
                data: grafikData.map(d => d.hadir),
                backgroundColor: 'rgba(72,187,120,0.8)',
                borderRadius: 4,
            },
            {
                label: 'Terlambat',
                data: grafikData.map(d => d.terlambat),
                backgroundColor: 'rgba(237,137,54,0.8)',
                borderRadius: 4,
            },
            {
                label: 'Tidak Hadir',
                data: grafikData.map(d => d.alpha),
                backgroundColor: 'rgba(252,129,129,0.8)',
                borderRadius: 4,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
        },
    },
});

// ── Pie Chart Hari Ini ───────────────────────────────────────────────────
const pieData = @json($pieData);
const pieValues = [pieData.hadir, pieData.terlambat, pieData.izin, pieData.sakit, pieData.alpha, pieData.belum];
const pieTotal  = pieValues.reduce((a,b) => a+b, 0);

if (pieTotal > 0) {
    new Chart(document.getElementById('chartPie'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir','Terlambat','Izin','Sakit','Alpha','Belum Absen'],
            datasets: [{
                data: pieValues,
                backgroundColor: ['#48bb78','#ed8936','#63b3ed','#f6ad55','#fc8181','#e2e8f0'],
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display: false } },
        },
    });
} else {
    const ctx = document.getElementById('chartPie').getContext('2d');
    ctx.fillStyle = '#e2e8f0';
    ctx.beginPath();
    ctx.arc(80, 80, 70, 0, 2 * Math.PI);
    ctx.fill();
}
</script>
@endpush
