@extends('layouts.admin')
@section('title', 'Kontrak Jatuh Tempo')
@section('page-title', 'Monitoring Kontrak')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#744210,#c05621);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-file-contract"></i> Monitoring Kontrak Karyawan</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Pantau kontrak yang sudah habis dan akan berakhir dalam waktu dekat</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="fas fa-calendar-day"></i> Hari ini: {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>
</div>

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:linear-gradient(135deg,#c53030,#e53e3e);border-radius:12px;padding:16px 18px;color:white;cursor:pointer;" onclick="filterStatus('sudah_habis')">
        <div style="font-size:28px;font-weight:800;">{{ $sudahHabis->count() }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Sudah Habis</div>
        <div style="font-size:10px;opacity:.7;margin-top:2px;">Kontrak berakhir</div>
    </div>
    <div style="background:linear-gradient(135deg,#c05621,#ed8936);border-radius:12px;padding:16px 18px;color:white;cursor:pointer;" onclick="filterStatus('akan_habis')">
        <div style="font-size:28px;font-weight:800;">{{ $akanHabis30->count() }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">≤ 30 Hari</div>
        <div style="font-size:10px;opacity:.7;margin-top:2px;">Segera berakhir</div>
    </div>
    <div style="background:linear-gradient(135deg,#b7791f,#d69e2e);border-radius:12px;padding:16px 18px;color:white;">
        <div style="font-size:28px;font-weight:800;">{{ $akanHabis60->count() }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">31-60 Hari</div>
        <div style="font-size:10px;opacity:.7;margin-top:2px;">Perlu diperhatikan</div>
    </div>
    <div style="background:linear-gradient(135deg,#276749,#38a169);border-radius:12px;padding:16px 18px;color:white;">
        <div style="font-size:28px;font-weight:800;">{{ $akanHabis90->count() }}</div>
        <div style="font-size:11px;opacity:.9;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">61-90 Hari</div>
        <div style="font-size:10px;opacity:.7;margin-top:2px;">Dalam pantauan</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px;">
        <form method="GET" id="filterForm" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="status" id="statusInput" value="{{ $status }}">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tampilkan</label>
                <select name="status" class="form-select" style="padding:8px 12px;font-size:13px;" onchange="document.getElementById('statusInput').value=this.value">
                    <option value="semua"       {{ $status=='semua'       ?'selected':'' }}>Semua (dalam 90 hari)</option>
                    <option value="sudah_habis" {{ $status=='sudah_habis' ?'selected':'' }}>Sudah Habis</option>
                    <option value="akan_habis"  {{ $status=='akan_habis'  ?'selected':'' }}>Akan Habis</option>
                </select>
            </div>
            @if($status === 'akan_habis')
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Rentang (hari)</label>
                <select name="rentang" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="7"  {{ $rentang==7  ?'selected':'' }}>7 hari</option>
                    <option value="14" {{ $rentang==14 ?'selected':'' }}>14 hari</option>
                    <option value="30" {{ $rentang==30 ?'selected':'' }}>30 hari</option>
                    <option value="60" {{ $rentang==60 ?'selected':'' }}>60 hari</option>
                    <option value="90" {{ $rentang==90 ?'selected':'' }}>90 hari</option>
                </select>
            </div>
            @endif
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($departemenList as $d)
                    <option value="{{ $d->id }}" {{ $departemen==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.kontrak.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list" style="color:#c05621;"></i>
            Daftar Karyawan
            <span style="font-size:11px;background:#fff3e0;color:#c05621;padding:2px 10px;border-radius:10px;margin-left:8px;font-weight:700;">{{ $tampil->count() }} karyawan</span>
        </h6>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">#</th>
                    <th style="padding:10px 16px;font-weight:700;color:#4a5568;">Karyawan</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Departemen</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Status</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Tgl Masuk</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Tgl Kontrak Berakhir</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Sisa / Lewat</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tampil as $k)
                @php
                    $berakhir = $k->tanggal_kontrak_berakhir;
                    $sisaHari = (int) $today->diffInDays($berakhir, false);
                    $sudahHabisItem = $berakhir->lt($today);
                    $isKritis = $sisaHari >= 0 && $sisaHari <= 7;
                    $isWarning = $sisaHari > 7 && $sisaHari <= 30;

                    $rowBg = $sudahHabisItem ? 'background:#fff5f5;' : ($isKritis ? 'background:#fffbeb;' : '');
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;{{ $rowBg }}"
                    onmouseover="this.style.filter='brightness(0.97)'" onmouseout="this.style.filter=''">
                    <td style="padding:10px 16px;color:#a0aec0;font-size:12px;">{{ $loop->iteration }}</td>
                    <td style="padding:10px 16px;">
                        <a href="{{ route('admin.karyawan.show', $k) }}" style="font-weight:700;color:#2d3748;text-decoration:none;">
                            {{ $k->nama }}
                        </a>
                        <div style="font-size:11px;color:#a0aec0;">NIK: {{ $k->nik ?? '-' }}</div>
                    </td>
                    <td style="padding:10px 12px;font-size:12px;color:#4a5568;">
                        {{ $k->department?->name ?? '-' }}
                        @if($k->jabatan)
                        <div style="font-size:11px;color:#a0aec0;">{{ $k->jabatan }}</div>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @php
                            $sBadge = [
                                'kontrak'     => ['#bee3f8','#2c5282'],
                                'magang'      => ['#fef3c7','#92400e'],
                                'training'    => ['#e0f2fe','#0369a1'],
                                'outsourcing' => ['#ede9fe','#6d28d9'],
                            ][$k->status] ?? ['#e2e8f0','#4a5568'];
                        @endphp
                        <span style="background:{{ $sBadge[0] }};color:{{ $sBadge[1] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                            {{ ucfirst($k->status) }}
                        </span>
                    </td>
                    <td style="padding:10px 12px;font-size:12px;color:#4a5568;text-align:center;">
                        {{ $k->tanggal_masuk ? $k->tanggal_masuk->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        <div style="font-weight:700;color:{{ $sudahHabisItem ? '#c53030' : ($isKritis ? '#c05621' : '#2d3748') }};">
                            {{ $berakhir->translatedFormat('d M Y') }}
                        </div>
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        @if($sudahHabisItem)
                        <span style="background:#fed7e2;color:#c53030;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;">
                            <i class="fas fa-exclamation-circle"></i> Lewat {{ abs($sisaHari) }} hari
                        </span>
                        @elseif($isKritis)
                        <span style="background:#fef3c7;color:#c05621;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;">
                            <i class="fas fa-fire"></i> {{ $sisaHari }} hari lagi
                        </span>
                        @elseif($isWarning)
                        <span style="background:#fef9c3;color:#854d0e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                            <i class="fas fa-clock"></i> {{ $sisaHari }} hari lagi
                        </span>
                        @else
                        <span style="color:#276749;font-weight:700;font-size:12px;">{{ $sisaHari }} hari lagi</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        <a href="{{ route('admin.karyawan.edit', $k) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:50px;color:#a0aec0;">
                        <i class="fas fa-file-contract" style="font-size:36px;display:block;margin-bottom:12px;"></i>
                        Tidak ada data kontrak ditemukan untuk filter yang dipilih.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterStatus(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('status', val);
    window.location.href = url.toString();
}
</script>
@endpush
@endsection
