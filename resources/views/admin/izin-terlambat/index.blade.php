@extends('layouts.admin')
@section('title', 'Izin Terlambat')
@section('page-title', 'Izin Terlambat (via WA)')

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-clock"></i> Izin Terlambat (via WA)</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Pengajuan izin terlambat yang diinput HRD dari chat WhatsApp karyawan</p>
    </div>
    <div style="display:flex;gap:8px;">
        @php
            $pending = $izinTerlambatList->total() > 0 ? \App\Models\IzinTerlambat::where('status','pending')->count() : 0;
        @endphp
        @if($pending > 0)
        <span style="background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="fas fa-clock"></i> {{ $pending }} pending
        </span>
        @endif
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:150px;">
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Cari Karyawan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK..."
                       class="form-control" style="padding:8px 12px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="pending"  {{ request('status')=='pending'  ?'selected':'' }}>Pending</option>
                    <option value="disetujui"{{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option>
                    <option value="ditolak"  {{ request('status')=='ditolak'  ?'selected':'' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($departemen as $d)
                    <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Bulan</label>
                <select name="bulan" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('bulan')==$m?'selected':'' }}>
                        {{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tahun</label>
                <select name="tahun" class="form-select" style="padding:8px 12px;font-size:13px;">
                    @foreach(range(now()->year, now()->year-2) as $y)
                    <option value="{{ $y }}" {{ request('tahun')==$y?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.izin-terlambat.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-responsive"><table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:10px 16px;text-align:left;font-weight:700;color:#4a5568;">Karyawan</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Tanggal</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#4a5568;">Keterangan</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Diinput oleh</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Status</th>
                    <th style="padding:10px 12px;font-weight:700;color:#4a5568;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izinTerlambatList as $it)
                @php
                    $statusColor = match($it->status) {
                        'disetujui' => ['#c6f6d5','#276749'],
                        'ditolak'   => ['#fed7e2','#c53030'],
                        default     => ['#fef9c3','#854d0e'],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f7fafc'" onmouseout="this.style.background=''">
                    <td style="padding:10px 16px;">
                        <a href="{{ route('admin.karyawan.show', $it->karyawan_id) }}#tab-ketidakhadiran"
                           style="font-weight:700;color:#2d3748;text-decoration:none;">{{ $it->karyawan?->nama ?? '-' }}</a>
                        <div style="font-size:11px;color:#718096;">{{ $it->karyawan?->department?->name ?? $it->karyawan?->departemen ?? '' }}</div>
                    </td>
                    <td style="padding:10px 12px;text-align:center;font-weight:600;color:#2d3748;">
                        {{ $it->tanggal->translatedFormat('d M Y') }}
                        <div style="font-size:10px;color:#a0aec0;">{{ $it->tanggal->translatedFormat('l') }}</div>
                    </td>
                    <td style="padding:10px 12px;font-size:12px;color:#4a5568;max-width:220px;">
                        {{ $it->keterangan }}
                        @if($it->catatan_approver)
                        <div style="font-size:11px;color:#718096;margin-top:2px;font-style:italic;">Catatan: {{ $it->catatan_approver }}</div>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:center;font-size:12px;color:#4a5568;">
                        {{ $it->inputOleh?->name ?? '-' }}
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        <span style="background:{{ $statusColor[0] }};color:{{ $statusColor[1] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                            {{ ucfirst($it->status) }}
                        </span>
                    </td>
                    <td style="padding:10px 12px;text-align:center;">
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <a href="{{ route('admin.karyawan.show', $it->karyawan_id) }}#tab-ketidakhadiran"
                               class="btn btn-secondary btn-sm" title="Lihat Detail Karyawan">
                                <i class="fas fa-external-link-alt"></i> Detail
                            </a>
                            @if($it->status === 'pending' && auth()->user()->can('approve-izin-terlambat', $it))
                            <form method="POST" action="{{ route('admin.izin-terlambat.approve', $it) }}">
                                @csrf
                                <button type="submit" style="padding:4px 10px;background:#c6f6d5;color:#276749;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;" title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.izin-terlambat.reject', $it) }}">
                                @csrf
                                <button type="submit" style="padding:4px 10px;background:#fed7e2;color:#c53030;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->hasPermission('izin-terlambat.hapus'))
                            <form method="POST" action="{{ route('admin.izin-terlambat.destroy', $it) }}" onsubmit="return confirm('Hapus data izin terlambat ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:4px 8px;background:#f7fafc;color:#718096;border:1px solid #e2e8f0;border-radius:6px;font-size:11px;cursor:pointer;" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:50px;color:#a0aec0;">
                        <i class="fas fa-clock" style="font-size:36px;display:block;margin-bottom:12px;"></i>
                        Tidak ada data izin terlambat ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div style="padding:12px 16px;border-top:1px solid #f0f0f0;">{{ $izinTerlambatList->appends(request()->query())->links() }}</div>
</div>
@endsection
