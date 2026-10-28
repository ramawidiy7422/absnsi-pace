@extends('layouts.admin')
@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
{{-- Rekap stat --}}
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:20px;">
    @php
        $statList = [
            'hadir'     => ['Hadir',    'green',  'fa-check-circle'],
            'terlambat' => ['Terlambat','yellow', 'fa-clock'],
            'izin'      => ['Izin',     'blue',   'fa-calendar-minus'],
            'sakit'     => ['Sakit',    'red',    'fa-heartbeat'],
            'alpha'     => ['Alpha',    'red',    'fa-times-circle'],
        ];
    @endphp
    @foreach($statList as $key => [$label, $color, $icon])
    <div class="stat-card {{ $color }}" style="border-radius:12px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="stat-number" style="font-size:28px;">{{ $rekap[$key] ?? 0 }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
            <div class="stat-icon" style="font-size:28px;"><i class="fas {{ $icon }}"></i></div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list-alt"></i> Laporan Bulan {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</h6>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.laporan.rekap-bulanan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm" style="background:#6b46c1;color:white;">
                <i class="fas fa-calendar-check"></i> Rekap Bulanan
            </a>
            <a href="{{ route('admin.laporan.rekap-departemen', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm" style="background:#276749;color:white;">
                <i class="fas fa-building"></i> Rekap Departemen
            </a>
            <a href="{{ route('admin.laporan.rekap-karyawan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm" style="background:#553c9a;color:white;">
                <i class="fas fa-user-check"></i> Rekap Karyawan
            </a>
            <a href="{{ route('admin.laporan.export-csv', request()->query()) }}" class="btn btn-sm" style="background:#2f855a;color:white;">
                <i class="fas fa-file-csv"></i> CSV
            </a>
            <a href="{{ route('admin.laporan.export-pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.laporan.export-excel', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.laporan.keterlambatan', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:#c53030;color:white;">
                <i class="fas fa-user-clock"></i> Keterlambatan
            </a>
            <a href="{{ route('admin.laporan.trend-kehadiran') }}" class="btn btn-sm" style="background:#2b6cb0;color:white;">
                <i class="fas fa-chart-line"></i> Trend
            </a>
            @if(auth()->user()->hasPermission('laporan.rekap-cuti'))
            <a href="{{ route('admin.laporan.rekap-cuti-izin', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:#805ad5;color:white;">
                <i class="fas fa-calendar-times"></i> Rekap Cuti & Izin
            </a>
            @endif
            @if(auth()->user()->hasPermission('laporan.lihat'))
            <a href="{{ route('admin.laporan.pelanggaran', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:#dd6b20;color:white;">
                <i class="fas fa-exclamation-circle"></i> Alpha & Pelanggaran
            </a>
            <a href="{{ route('admin.laporan.rekap-lembur', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:#c05621;color:white;">
                <i class="fas fa-business-time"></i> Rekap Lembur
            </a>
            <a href="{{ route('admin.laporan.rekap-potongan-tunjangan', ['bulan'=>$bulan,'tahun'=>$tahun]) }}" class="btn btn-sm" style="background:#4a5568;color:white;">
                <i class="fas fa-calculator"></i> Potongan & Tunjangan
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        {{-- Filter --}}
        <form method="GET" style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="min-width:130px;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="min-width:100px;">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:#4a5568;display:block;margin-bottom:4px;">Karyawan</label>
                <select name="karyawan_id" class="form-select" style="min-width:200px;">
                    <option value="">Semua Karyawan</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="align-self:flex-end; display:flex; gap:6px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Shift</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Akurasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $a->karyawan?->nama ?? '-' }}</strong><br>
                            <small style="color:#a0aec0;">{{ $a->karyawan?->nik ?? '' }}</small>
                        </td>
                        <td>{{ $a->karyawan?->department?->name ?? $a->karyawan?->departemen ?? '-' }}</td>
                        <td style="font-size:12px;">{{ $a->shift->nama_shift ?? '-' }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td>{{ $a->jam_keluar ?? '-' }}</td>
                        <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td>
                            @php $met = $a->metode ?? 'wajah'; @endphp
                            @if($met === 'qrcode')
                                <span style="font-size:11px;background:#f3e8ff;color:#805ad5;padding:2px 8px;border-radius:10px;white-space:nowrap;"><i class="fas fa-qrcode"></i> QR</span>
                            @elseif($met === 'manual')
                                <span style="font-size:11px;background:#e2e8f0;color:#4a5568;padding:2px 8px;border-radius:10px;white-space:nowrap;"><i class="fas fa-keyboard"></i> Manual</span>
                            @else
                                <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 8px;border-radius:10px;white-space:nowrap;"><i class="fas fa-smile"></i> Wajah</span>
                            @endif
                        </td>
                        <td>{{ $a->confidence_masuk ? $a->confidence_masuk . '%' : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center; padding:30px; color:#718096;">
                        <i class="fas fa-inbox" style="font-size:30px;display:block;margin-bottom:8px;"></i>
                        Tidak ada data untuk periode ini
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:10px; font-size:12px; color:#718096;">
            Menampilkan {{ $absensi->firstItem() ?? 0 }}–{{ $absensi->lastItem() ?? 0 }} dari {{ $absensi->total() }} data
        </div>
        <div class="pagination">{{ $absensi->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
