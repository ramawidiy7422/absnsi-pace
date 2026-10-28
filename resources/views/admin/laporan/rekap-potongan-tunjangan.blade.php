@extends('layouts.admin')
@section('title', 'Rekap Potongan & Tunjangan')
@section('page-title', 'Rekap Potongan & Tunjangan Kehadiran')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#276749,#38a169);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-calculator"></i> Rekap Potongan & Tunjangan</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Kalkulasi otomatis berdasarkan data kehadiran</p>
    </div>
</div>

{{-- Filter + Parameter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="font-size:13px;">
                    @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" {{ $bulan==$m?'selected':'' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="font-size:13px;">
                    @for($y=date('Y');$y>=date('Y')-3;$y--)
                        <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($departemenList as $d)
                        <option value="{{ $d->id }}" {{ $dept==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Parameter Nilai --}}
            <div style="background:#f7fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;display:flex;gap:10px;flex-wrap:wrap;">
                <div>
                    <label style="font-size:10px;font-weight:700;color:#e53e3e;display:block;margin-bottom:2px;">Potongan Alpha (Rp/hari)</label>
                    <input type="number" name="nilai_alpha" value="{{ $nilaiAlpha }}" class="form-control"
                           style="font-size:12px;width:140px;" min="0" step="1000" placeholder="0">
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#c05621;display:block;margin-bottom:2px;">Potongan Terlambat (Rp/hari)</label>
                    <input type="number" name="nilai_terlambat" value="{{ $nilaiTerlambat }}" class="form-control"
                           style="font-size:12px;width:140px;" min="0" step="1000" placeholder="0">
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#276749;display:block;margin-bottom:2px;">Tunjangan Hadir (Rp/hari)</label>
                    <input type="number" name="nilai_hadir" value="{{ $nilaiHadir }}" class="form-control"
                           style="font-size:12px;width:140px;" min="0" step="1000" placeholder="0">
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#2b6cb0;display:block;margin-bottom:2px;">Tunjangan Lembur (Rp/jam)</label>
                    <input type="number" name="nilai_lembur" value="{{ $nilaiLembur }}" class="form-control"
                           style="font-size:12px;width:140px;" min="0" step="1000" placeholder="0">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 16px;align-self:flex-end;">
                <i class="fas fa-calculator"></i> Hitung
            </button>
            <a href="{{ route('admin.laporan.rekap-potongan-tunjangan.export-csv', request()->all()) }}"
               class="btn btn-success" style="font-size:13px;padding:8px 16px;align-self:flex-end;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </form>
    </div>
</div>

@php
$totalHadir     = $rekap->sum('jml_hadir');
$totalAlpha     = $rekap->sum('jml_alpha');
$totalTerlambat = $rekap->sum('jml_terlambat');
$totalPotongan  = $rekap->sum('total_potongan');
$totalTunjangan = $rekap->sum('total_tunjangan');
$totalNetto     = $rekap->sum('netto');
@endphp

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px;">
    <div style="background:white;border-radius:12px;padding:16px;border-left:4px solid #38a169;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Total Hadir</div>
        <div style="font-size:22px;font-weight:800;color:#276749;">{{ number_format($totalHadir) }}</div>
        <div style="font-size:11px;color:#a0aec0;">hari</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border-left:4px solid #e53e3e;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Total Alpha</div>
        <div style="font-size:22px;font-weight:800;color:#c53030;">{{ number_format($totalAlpha) }}</div>
        <div style="font-size:11px;color:#a0aec0;">hari</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border-left:4px solid #e53e3e;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Total Potongan</div>
        <div style="font-size:18px;font-weight:800;color:#c53030;">Rp {{ number_format($totalPotongan,0,',','.') }}</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border-left:4px solid #276749;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Total Tunjangan</div>
        <div style="font-size:18px;font-weight:800;color:#276749;">Rp {{ number_format($totalTunjangan,0,',','.') }}</div>
    </div>
    <div style="background:white;border-radius:12px;padding:16px;border-left:4px solid #2b6cb0;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Netto</div>
        <div style="font-size:18px;font-weight:800;color:{{ $totalNetto>=0?'#276749':'#c53030' }};">
            Rp {{ number_format($totalNetto,0,',','.') }}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="table" style="margin:0;font-size:12px;">
            <thead style="background:#f7fafc;">
                <tr>
                    <th style="padding:10px 12px;" rowspan="2">Karyawan</th>
                    <th style="padding:10px 8px;text-align:center;" colspan="4">Kehadiran</th>
                    <th style="padding:10px 8px;text-align:center;background:#fff5f5;" colspan="2">Potongan</th>
                    <th style="padding:10px 8px;text-align:center;background:#f0fff4;" colspan="2">Tunjangan</th>
                    <th style="padding:10px 8px;text-align:center;" colspan="2">Total</th>
                </tr>
                <tr style="font-size:11px;color:#718096;">
                    <th style="padding:6px 8px;text-align:center;">Hadir</th>
                    <th style="padding:6px 8px;text-align:center;">Terlambat</th>
                    <th style="padding:6px 8px;text-align:center;">Alpha</th>
                    <th style="padding:6px 8px;text-align:center;">Lembur</th>
                    <th style="padding:6px 8px;text-align:center;background:#fff5f5;">Alpha</th>
                    <th style="padding:6px 8px;text-align:center;background:#fff5f5;">Terlambat</th>
                    <th style="padding:6px 8px;text-align:center;background:#f0fff4;">Hadir</th>
                    <th style="padding:6px 8px;text-align:center;background:#f0fff4;">Lembur</th>
                    <th style="padding:6px 8px;text-align:center;">Potongan</th>
                    <th style="padding:6px 8px;text-align:center;">Netto</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rekap as $r)
            <tr style="{{ $r['netto'] < 0 ? 'background:#fff5f5;' : '' }}">
                <td style="padding:10px 12px;">
                    <div style="font-weight:700;">{{ $r['karyawan']->nama }}</div>
                    <div style="font-size:11px;color:#718096;">{{ $r['karyawan']->department?->name ?? '-' }}</div>
                </td>
                <td style="padding:10px 8px;text-align:center;font-weight:700;color:#276749;">{{ $r['jml_hadir'] }}</td>
                <td style="padding:10px 8px;text-align:center;color:#c05621;">{{ $r['jml_terlambat'] }}</td>
                <td style="padding:10px 8px;text-align:center;color:#c53030;font-weight:700;">{{ $r['jml_alpha'] }}</td>
                <td style="padding:10px 8px;text-align:center;color:#2b6cb0;">{{ $r['jml_lembur_jam'] > 0 ? $r['jml_lembur_jam'].'j' : '-' }}</td>
                <td style="padding:10px 8px;text-align:right;background:#fff5f5;color:#c53030;">
                    {{ $r['potongan_alpha'] > 0 ? 'Rp '.number_format($r['potongan_alpha'],0,',','.') : '-' }}
                </td>
                <td style="padding:10px 8px;text-align:right;background:#fff5f5;color:#c05621;">
                    {{ $r['potongan_terlambat'] > 0 ? 'Rp '.number_format($r['potongan_terlambat'],0,',','.') : '-' }}
                </td>
                <td style="padding:10px 8px;text-align:right;background:#f0fff4;color:#276749;">
                    {{ $r['tunjangan_hadir'] > 0 ? 'Rp '.number_format($r['tunjangan_hadir'],0,',','.') : '-' }}
                </td>
                <td style="padding:10px 8px;text-align:right;background:#f0fff4;color:#2b6cb0;">
                    {{ $r['tunjangan_lembur'] > 0 ? 'Rp '.number_format($r['tunjangan_lembur'],0,',','.') : '-' }}
                </td>
                <td style="padding:10px 8px;text-align:right;color:#c53030;font-weight:700;">
                    {{ $r['total_potongan'] > 0 ? 'Rp '.number_format($r['total_potongan'],0,',','.') : '-' }}
                </td>
                <td style="padding:10px 8px;text-align:right;font-weight:800;color:{{ $r['netto']>=0?'#276749':'#c53030' }};">
                    Rp {{ number_format($r['netto'],0,',','.') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="11" style="padding:40px;text-align:center;color:#a0aec0;">
                <i class="fas fa-inbox" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                Tidak ada data untuk periode ini.
            </td></tr>
            @endforelse
            </tbody>
            <tfoot style="background:#f7fafc;font-weight:700;border-top:2px solid #e2e8f0;">
                <tr>
                    <td style="padding:10px 12px;">TOTAL ({{ $rekap->count() }} karyawan)</td>
                    <td style="padding:10px 8px;text-align:center;">{{ $totalHadir }}</td>
                    <td style="padding:10px 8px;text-align:center;">{{ $totalTerlambat }}</td>
                    <td style="padding:10px 8px;text-align:center;">{{ $totalAlpha }}</td>
                    <td></td>
                    <td style="padding:10px 8px;text-align:right;background:#fff5f5;color:#c53030;">Rp {{ number_format($rekap->sum('potongan_alpha'),0,',','.') }}</td>
                    <td style="padding:10px 8px;text-align:right;background:#fff5f5;color:#c05621;">Rp {{ number_format($rekap->sum('potongan_terlambat'),0,',','.') }}</td>
                    <td style="padding:10px 8px;text-align:right;background:#f0fff4;color:#276749;">Rp {{ number_format($rekap->sum('tunjangan_hadir'),0,',','.') }}</td>
                    <td style="padding:10px 8px;text-align:right;background:#f0fff4;color:#2b6cb0;">Rp {{ number_format($rekap->sum('tunjangan_lembur'),0,',','.') }}</td>
                    <td style="padding:10px 8px;text-align:right;color:#c53030;">Rp {{ number_format($totalPotongan,0,',','.') }}</td>
                    <td style="padding:10px 8px;text-align:right;color:{{ $totalNetto>=0?'#276749':'#c53030' }};">Rp {{ number_format($totalNetto,0,',','.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-top:10px;font-size:11px;color:#a0aec0;text-align:right;">
    <i class="fas fa-info-circle"></i>
    Netto = Total Tunjangan − Total Potongan. Nilai 0 jika parameter belum diisi.
</div>
@endsection
