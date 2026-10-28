@extends('layouts.admin')
@section('title','Rekap Lembur')
@section('page-title','Rekap Lembur')

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

{{-- Filter (dipakai bersama kedua tab) --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="form-group" style="margin:0;min-width:120px;">
                <label style="font-size:12px;font-weight:600;color:#4a5568;">Bulan</label>
                <select name="bulan" class="form-control form-select" style="height:36px;">
                    @foreach($bulanList as $num => $nama)
                    <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:90px;">
                <label style="font-size:12px;font-weight:600;color:#4a5568;">Tahun</label>
                <select name="tahun" class="form-control form-select" style="height:36px;">
                    @foreach(range(now()->year, now()->year-2) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:160px;">
                <label style="font-size:12px;font-weight:600;color:#4a5568;">Departemen</label>
                <select name="departemen_id" class="form-control form-select" style="height:36px;">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ $deptId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:130px;">
                <label style="font-size:12px;font-weight:600;color:#4a5568;">Jenis</label>
                <select name="jenis" class="form-control form-select" style="height:36px;">
                    <option value="">Semua Jenis</option>
                    <option value="spk" {{ $jenis==='spk' ? 'selected' : '' }}>SPK</option>
                    <option value="mandiri" {{ $jenis==='mandiri' ? 'selected' : '' }}>Mandiri</option>
                    <option value="auto" {{ $jenis==='auto' ? 'selected' : '' }}>Auto (Kiosk)</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:130px;">
                <label style="font-size:12px;font-weight:600;color:#4a5568;">Status</label>
                <select name="status" class="form-control form-select" style="height:36px;">
                    <option value="approved" {{ $status==='approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="pending" {{ $status==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="semua" {{ $status==='semua' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:36px;white-space:nowrap;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.laporan.rekap-lembur.export-excel', request()->query()) }}"
               class="btn" style="height:36px;background:#276749;color:white;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </form>
    </div>
</div>

{{-- Ringkasan (dipakai bersama kedua tab) --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
    @foreach([
        ['label'=>'Total Jam Lembur','value'=>$ringkasan['total_jam'].' jam','color'=>'#c05621','bg'=>'#fff7ed'],
        ['label'=>'Karyawan Lembur','value'=>$ringkasan['total_karyawan'].' orang','color'=>'#2b6cb0','bg'=>'#ebf8ff'],
        ['label'=>'Total Record','value'=>$ringkasan['total_record'],'color'=>'#553c9a','bg'=>'#faf5ff'],
        ['label'=>'Disetujui','value'=>$ringkasan['approved'],'color'=>'#276749','bg'=>'#f0fff4'],
        ['label'=>'Pending','value'=>$ringkasan['pending'],'color'=>'#b7791f','bg'=>'#fffff0'],
        ['label'=>'Estimasi Biaya','value'=>'Rp '.number_format($ringkasan['total_biaya'],0,',','.'),'color'=>'#c53030','bg'=>'#fff5f5'],
    ] as $s)
    <div style="background:{{ $s['bg'] }};border-radius:12px;padding:14px 16px;border:1px solid {{ $s['color'] }}22;">
        <div style="font-size:18px;font-weight:800;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:2px;">{{ $s['label'] }}</div>
    </div>
    @endforeach
</div>

<div class="tab-nav">
    <button class="tab-btn" data-tab="tab-per-karyawan" onclick="switchTab('tab-per-karyawan')">
        <i class="fas fa-user-clock"></i> Per Karyawan
    </button>
    <button class="tab-btn" data-tab="tab-per-departemen" onclick="switchTab('tab-per-departemen')">
        <i class="fas fa-building"></i> Per Departemen
    </button>
</div>

{{-- ══ TAB: PER KARYAWAN (ranking siapa paling banyak lembur) ══ --}}
<div class="tab-content" id="tab-per-karyawan">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-user-clock" style="color:#c05621;"></i> Ranking Lembur per Karyawan</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Karyawan</th>
                            <th>Departemen</th>
                            <th style="text-align:center;">Sesi</th>
                            <th style="text-align:center;">Total Jam</th>
                            <th style="text-align:center;">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perKaryawan as $i => $r)
                        @php
                            $pct = $ringkasan['total_jam'] > 0 ? min(100, round($r['total_jam']/$ringkasan['total_jam']*100)) : 0;
                        @endphp
                        <tr>
                            <td style="color:#a0aec0;font-size:12px;">{{ $i + 1 }}</td>
                            <td>
                                <div style="font-weight:700;color:#2d3748;">{{ $r['karyawan']?->nama ?? '-' }}</div>
                                <small style="color:#a0aec0;">{{ $r['karyawan']?->nik ?? '' }}</small>
                            </td>
                            <td style="font-size:12px;color:#718096;">{{ $r['karyawan']?->department?->name ?? $r['karyawan']?->departemen ?? '-' }}</td>
                            <td style="text-align:center;font-weight:700;color:#2d3748;">{{ $r['total_hari'] }}</td>
                            <td style="text-align:center;">
                                <div style="font-weight:800;color:#c05621;font-size:15px;">{{ number_format($r['total_jam'],1) }}</div>
                                <div style="background:#e2e8f0;border-radius:99px;height:5px;margin-top:4px;overflow:hidden;">
                                    <div style="width:{{ $pct }}%;background:#ed8936;height:100%;border-radius:99px;"></div>
                                </div>
                            </td>
                            <td style="text-align:center;color:#718096;font-size:13px;">
                                {{ $r['total_hari'] > 0 ? number_format($r['total_jam']/$r['total_hari'],1) : 0 }} j/sesi
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#a0aec0;">
                                <i class="fas fa-business-time" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                Tidak ada data lembur untuk filter ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ══ TAB: PER DEPARTEMEN (detail transaksi + estimasi biaya) ══ --}}
<div class="tab-content" id="tab-per-departemen">
    @foreach($perDept as $dept)
    @php $estimasi = $dept['tarif'] > 0 ? $dept['total_jam'] * $dept['tarif'] : 0; @endphp
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <h6 style="margin:0;font-weight:700;">
                <i class="fas fa-building" style="color:#4299e1;"></i>
                {{ $dept['departemen'] }}
                <span style="background:#bee3f8;color:#2b6cb0;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px;">
                    {{ $dept['total_karyawan'] }} karyawan
                </span>
            </h6>
            <div style="display:flex;gap:16px;font-size:13px;">
                <span style="font-weight:700;color:#c05621;"><i class="fas fa-clock"></i> {{ $dept['total_jam'] }} jam</span>
                <span style="color:#276749;"><i class="fas fa-check"></i> {{ $dept['approved'] }} disetujui</span>
                <span style="color:#b7791f;"><i class="fas fa-hourglass-half"></i> {{ $dept['pending'] }} pending</span>
                @if($estimasi > 0)
                <span style="color:#c53030;font-weight:700;">Rp {{ number_format($estimasi, 0, ',', '.') }}</span>
                @endif
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:#f7fafc;">
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Tanggal</th>
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Karyawan</th>
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Jam</th>
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Total</th>
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Jenis</th>
                        <th style="padding:8px 12px;text-align:left;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Status</th>
                        @if($dept['tarif'] > 0)
                        <th style="padding:8px 12px;text-align:right;font-weight:700;color:#4a5568;border-bottom:1px solid #e2e8f0;">Upah</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($dept['items'] as $l)
                    @php
                        $sb = $l->status_badge;
                        $jb = $l->jenis_badge;
                    @endphp
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:8px 12px;color:#4a5568;">{{ $l->tanggal->format('d M Y') }}</td>
                        <td style="padding:8px 12px;">
                            <div style="font-weight:600;">{{ $l->karyawan?->nama }}</div>
                            <div style="font-size:10px;color:#a0aec0;">{{ $l->karyawan?->nik }}</div>
                        </td>
                        <td style="padding:8px 12px;color:#718096;">{{ substr($l->jam_mulai,0,5) }} – {{ substr($l->jam_selesai,0,5) }}</td>
                        <td style="padding:8px 12px;font-weight:700;color:#c05621;">{{ $l->total_jam }} jam</td>
                        <td style="padding:8px 12px;">
                            <span style="background:{{ $jb['bg'] }};color:{{ $jb['color'] }};padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;">{{ $jb['label'] }}</span>
                        </td>
                        <td style="padding:8px 12px;">
                            <span style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;">{{ $sb['label'] }}</span>
                        </td>
                        @if($dept['tarif'] > 0)
                        <td style="padding:8px 12px;text-align:right;font-weight:600;color:#276749;">
                            {{ $l->status === 'approved' ? 'Rp '.number_format($l->total_jam * $dept['tarif'],0,',','.') : '—' }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($perDept->isEmpty())
    <div class="card" style="padding:40px;text-align:center;color:#a0aec0;">
        <i class="fas fa-business-time" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
        <div style="font-size:16px;font-weight:700;color:#4a5568;">Tidak ada data lembur</div>
        <div style="font-size:13px;margin-top:4px;">Coba ubah filter bulan/tahun atau status.</div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    document.querySelectorAll('.tab-btn[data-tab="' + tabId + '"]').forEach(btn => btn.classList.add('active'));
    history.replaceState(null, null, '#' + tabId);
}

(function() {
    const validTabs = ['tab-per-karyawan', 'tab-per-departemen'];
    const hash = window.location.hash.replace('#', '');
    const tabParam = new URLSearchParams(window.location.search).get('_tab');
    const initialTab = validTabs.includes(hash) ? hash : (validTabs.includes(tabParam) ? tabParam : 'tab-per-karyawan');
    switchTab(initialTab);
})();
</script>
@endpush
