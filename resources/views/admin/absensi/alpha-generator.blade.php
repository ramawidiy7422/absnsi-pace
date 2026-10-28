@extends('layouts.admin')
@section('title', 'Alpha Generator')
@section('page-title', 'Generate Alpha Otomatis')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#742a2a,#c53030);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-user-slash"></i> Alpha Generator</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Otomatis tandai karyawan tidak hadir tanpa keterangan sebagai Alpha</p>
    </div>
    <div style="background:rgba(255,255,255,0.15);border-radius:10px;padding:10px 16px;font-size:12px;text-align:right;">
        <div style="font-weight:700;opacity:0.9;margin-bottom:2px;"><i class="fas fa-clock"></i> Jadwal Otomatis</div>
        <div style="opacity:0.8;">Setiap hari kerja pukul 23:59</div>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
    <div class="stat-card red" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $alphaBulanIni }}</div>
                <div class="stat-label">Alpha Bulan Ini</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>
    <div class="stat-card yellow" style="border-radius:12px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $alphaKaryawan }}</div>
                <div class="stat-label">Karyawan Beralpha</div>
            </div>
            <div class="stat-icon" style="font-size:26px;"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;">
        <div style="font-size:11px;font-weight:700;color:#a0aec0;margin-bottom:8px;text-transform:uppercase;">Preview Kemarin</div>
        @if(count($preview) > 0)
        <div style="font-size:22px;font-weight:800;color:#c53030;">{{ count($preview) }}</div>
        <div style="font-size:11px;color:#718096;">karyawan belum tercatat</div>
        @else
        <div style="font-size:14px;font-weight:700;color:#38a169;"><i class="fas fa-check-circle"></i> Semua tercatat</div>
        <div style="font-size:11px;color:#718096;">{{ $kemarin->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:16px;align-items:start;">

    {{-- Panel Kiri: Run Manual + Preview --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Form Run Manual --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-play-circle" style="color:#c53030;"></i> Jalankan Manual</h6>
            </div>
            <div class="card-body">
                <div style="background:#fffbeb;border:1px solid #f6e05e;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:#744210;">
                    <i class="fas fa-info-circle"></i>
                    Logika yang dijalankan:
                    <ul style="margin:6px 0 0 0;padding-left:20px;line-height:2;">
                        <li>Karyawan aktif (kontrak/tetap/magang)</li>
                        <li>Tidak punya record absensi hari itu</li>
                        <li>Tidak ada izin/sakit yang disetujui</li>
                        <li>Tidak sedang cuti yang disetujui</li>
                        <li>Bukan hari libur nasional/internal</li>
                    </ul>
                </div>
                <form method="POST" action="{{ route('admin.absensi.alpha-generator.run') }}"
                      onsubmit="return confirm('Generate alpha untuk tanggal yang dipilih? Pastikan sudah lewat pukul shift selesai.')">
                    @csrf
                    <div class="form-group">
                        <label style="font-weight:700;">Tanggal Target</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="{{ $kemarin->format('Y-m-d') }}"
                               max="{{ today()->format('Y-m-d') }}" required>
                        <small style="color:#718096;">Hanya bisa tanggal hari ini atau sebelumnya</small>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%;">
                        <i class="fas fa-play"></i> Generate Alpha Sekarang
                    </button>
                </form>
            </div>
        </div>

        {{-- Preview kemarin --}}
        @if(count($preview) > 0)
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-eye" style="color:#d69e2e;"></i> Preview Kemarin ({{ $kemarin->format('d/m/Y') }})</h6>
                <span style="font-size:11px;color:#a0aec0;">{{ count($preview) }} karyawan belum tercatat</span>
            </div>
            <div class="card-body" style="padding:0;max-height:280px;overflow-y:auto;">
                @foreach($preview as $k)
                <div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:600;color:#2d3748;font-size:13px;">{{ $k['nama'] ?? $k->nama }}</div>
                        <div style="font-size:11px;color:#a0aec0;">{{ $k['departemen'] ?? $k->departemen ?? '-' }}</div>
                    </div>
                    <span style="background:#fff5f5;color:#c53030;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">ALPHA</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Cara aktifkan cron --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-terminal" style="color:#805ad5;"></i> Aktifkan Scheduler (Cron)</h6>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:#718096;margin-bottom:12px;">
                    Agar generate alpha berjalan otomatis setiap hari pukul 23:59, tambahkan baris ini ke crontab server:
                </p>
                <pre style="background:#1a202c;color:#68d391;border-radius:8px;padding:12px 14px;font-size:11px;overflow-x:auto;margin:0;">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</pre>
                <div style="margin-top:12px;font-size:12px;color:#718096;">
                    Jalankan <code>crontab -e</code> di server dan paste baris di atas.
                </div>
                <div style="margin-top:10px;padding:10px;background:#f7fafc;border-radius:6px;font-size:12px;color:#4a5568;">
                    <strong>Test manual via terminal:</strong><br>
                    <code>php artisan absensi:generate-alpha --dry-run</code><br>
                    <code>php artisan absensi:generate-alpha --tanggal=2026-04-12</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan: 7 Hari + Riwayat Log --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Grafik 7 hari --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-bar" style="color:#3182ce;"></i> Alpha 7 Hari Terakhir</h6>
            </div>
            <div class="card-body" style="padding:14px;">
                @php
                    $maxAlpha = $alpha7Hari->max('jumlah') ?: 1;
                @endphp
                <div style="display:flex;gap:6px;align-items:flex-end;height:120px;margin-bottom:8px;">
                    @for($i = 6; $i >= 0; $i--)
                    @php
                        $tgl    = now()->subDays($i)->format('Y-m-d');
                        $jml    = $alpha7Hari[$tgl]?->jumlah ?? 0;
                        $height = $maxAlpha > 0 ? max(4, round($jml / $maxAlpha * 100)) : 4;
                        $isToday= $i === 0;
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                        <div style="font-size:11px;font-weight:700;color:{{ $jml > 0 ? '#c53030' : '#a0aec0' }};">
                            {{ $jml > 0 ? $jml : '' }}
                        </div>
                        <div style="width:100%;height:{{ $height }}px;background:{{ $jml > 0 ? '#fc8181' : '#e2e8f0' }};border-radius:4px 4px 0 0;{{ $isToday ? 'border:2px solid #c53030;' : '' }}"></div>
                        <div style="font-size:9px;color:#718096;text-align:center;">
                            {{ \Carbon\Carbon::parse($tgl)->format('d/M') }}
                        </div>
                    </div>
                    @endfor
                </div>
                <div style="font-size:11px;color:#a0aec0;text-align:center;">Jumlah record alpha per hari</div>
            </div>
        </div>

        {{-- Riwayat log --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-history" style="color:#38a169;"></i> Riwayat Generate</h6>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($riwayatLog as $log)
                <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                        <div style="font-size:13px;color:#2d3748;flex:1;">{{ $log->deskripsi }}</div>
                        <div style="font-size:11px;color:#a0aec0;white-space:nowrap;">{{ $log->created_at->format('d/m H:i') }}</div>
                    </div>
                    @if($log->user)
                    <div style="font-size:11px;color:#718096;margin-top:2px;">
                        <i class="fas fa-user" style="font-size:9px;"></i> {{ $log->user->name }}
                    </div>
                    @else
                    <div style="font-size:11px;color:#a0aec0;margin-top:2px;">
                        <i class="fas fa-robot" style="font-size:9px;"></i> Scheduler otomatis
                    </div>
                    @endif
                </div>
                @empty
                <div style="padding:30px;text-align:center;color:#a0aec0;">
                    <i class="fas fa-history" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    Belum ada riwayat generate alpha
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
