@extends('layouts.admin')
@section('title', 'Data Pelatihan')
@section('page-title', 'Pelatihan & Sertifikasi')

@section('content')


{{-- Header --}}
<div style="background:linear-gradient(135deg,#553c9a,#805ad5);color:white;border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-graduation-cap"></i> Pelatihan & Sertifikasi</h4>
        <p style="margin:4px 0 0;opacity:.85;font-size:13px;">CPOB, K3, Higiene, SOP, Internal, Eksternal</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($stats['near_expired'] > 0)
        <span style="background:rgba(255,200,100,0.3);border:1px solid rgba(255,200,100,0.5);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="fas fa-exclamation-triangle"></i> {{ $stats['near_expired'] }} akan expired
        </span>
        @endif
        @if($stats['expired'] > 0)
        <span style="background:rgba(255,100,100,0.3);border:1px solid rgba(255,100,100,0.5);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            <i class="fas fa-times-circle"></i> {{ $stats['expired'] }} expired
        </span>
        @endif
    </div>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px;">
    <div style="background:white;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#553c9a;">{{ $stats['total'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Total</div>
    </div>
    <div style="background:white;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#276749;">{{ $stats['lulus'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Lulus / Tersertifikasi</div>
    </div>
    <div style="background:#f0fff4;border-radius:10px;padding:14px;border:1px solid #9ae6b4;text-align:center;cursor:pointer;" onclick="setFilter('tipe','internal')">
        <div style="font-size:26px;font-weight:800;color:#276749;">{{ $stats['internal'] }}</div>
        <div style="font-size:11px;color:#276749;margin-top:3px;font-weight:600;"><i class="fas fa-home"></i> Internal</div>
    </div>
    <div style="background:#ebf8ff;border-radius:10px;padding:14px;border:1px solid #90cdf4;text-align:center;cursor:pointer;" onclick="setFilter('tipe','eksternal')">
        <div style="font-size:26px;font-weight:800;color:#2b6cb0;">{{ $stats['eksternal'] }}</div>
        <div style="font-size:11px;color:#2b6cb0;margin-top:3px;font-weight:600;"><i class="fas fa-globe"></i> Eksternal</div>
    </div>
    <div style="background:#fffaf0;border-radius:10px;padding:14px;border:1px solid #fbd38d;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c05621;">{{ $stats['near_expired'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Akan Expired (&le;30 hari)</div>
    </div>
    <div style="background:#fff5f5;border-radius:10px;padding:14px;border:1px solid #fed7d7;text-align:center;">
        <div style="font-size:26px;font-weight:800;color:#c53030;">{{ $stats['expired'] }}</div>
        <div style="font-size:11px;color:#718096;margin-top:3px;">Sudah Expired</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Tipe</label>
                <select name="tipe" class="form-select" id="filter-tipe" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua Tipe</option>
                    <option value="internal"  {{ ($tipe??'')==='internal'  ?'selected':'' }}>Internal</option>
                    <option value="eksternal" {{ ($tipe??'')==='eksternal' ?'selected':'' }}>Eksternal</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Materi / Bidang</label>
                <select name="jenis" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua Bidang</option>
                    @foreach(\App\Models\Pelatihan::jenisLabel() as $k => $v)
                    <option value="{{ $k }}" {{ $jenis==$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua Status</option>
                    <option value="lulus"           {{ $status=='lulus'           ?'selected':'' }}>Lulus</option>
                    <option value="tidak_lulus"     {{ $status=='tidak_lulus'     ?'selected':'' }}>Tidak Lulus</option>
                    <option value="sedang_berjalan" {{ $status=='sedang_berjalan' ?'selected':'' }}>Sedang Berjalan</option>
                    <option value="belum_ikut"      {{ $status=='belum_ikut'      ?'selected':'' }}>Belum Ikut</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Departemen</label>
                <select name="department_id" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    @foreach($deptList as $d)
                    <option value="{{ $d->id }}" {{ $department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">Sertifikat</label>
                <select name="expiring" class="form-select" style="padding:8px 12px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="30"      {{ $expiring=='30'      ?'selected':'' }}>Akan Expired (30 hari)</option>
                    <option value="expired" {{ $expiring=='expired' ?'selected':'' }}>Sudah Expired</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.pelatihan.index') }}" class="btn btn-secondary" style="padding:8px 16px;">Reset</a>
            @if(auth()->user()->hasPermission('laporan.export'))
            <a href="{{ route('admin.pelatihan.export-pdf', request()->query()) }}" class="btn btn-danger" style="margin-left:auto;padding:8px 16px;">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.pelatihan.export-csv', request()->query()) }}" class="btn btn-secondary" style="padding:8px 16px;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="table" style="margin:0;">
                <thead>
                    <tr style="background:#f7fafc;">
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Karyawan</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Nama Pelatihan</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Tipe</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Bidang</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Penyelenggara</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Tgl Mulai</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Status</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Expired</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelatihan as $p)
                    @php
                        $sc = $p->status_color;
                        $expiredAlert = '';
                        if ($p->tanggal_expired) {
                            if ($p->is_expired)       $expiredAlert = 'expired';
                            elseif ($p->is_near_expired) $expiredAlert = 'near';
                        }
                    @endphp
                    <tr style="{{ $expiredAlert === 'expired' ? 'background:#fff5f5;' : ($expiredAlert === 'near' ? 'background:#fffaf0;' : '') }}">
                        <td style="padding:10px 14px;">
                            <div style="font-weight:600;font-size:13px;">{{ $p->karyawan?->nama ?? '-' }}</div>
                            <div style="font-size:11px;color:#718096;">
                                {{ $p->karyawan?->nik ?? '' }}
                                @if($p->karyawan?->department)
                                &middot; {{ $p->karyawan?->department?->name }}
                                @endif
                            </div>
                        </td>
                        <td style="padding:10px 14px;">
                            <div style="font-weight:600;font-size:13px;">{{ $p->nama_pelatihan }}</div>
                            @if($p->nomor_sertifikat)
                            <div style="font-size:11px;color:#718096;">Sertif: {{ $p->nomor_sertifikat }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;">
                            @php $tb = $p->tipe_badge; @endphp
                            <span style="background:{{ $tb['bg'] }};color:{{ $tb['color'] }};border:1px solid {{ $tb['border'] }};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">
                                <i class="fas {{ $tb['icon'] }}"></i> {{ $p->tipe_label }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="background:#ede9fe;color:#553c9a;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ $p->jenis_label }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;font-size:12px;color:#4a5568;">{{ $p->penyelenggara ?? '-' }}</td>
                        <td style="padding:10px 14px;font-size:12px;white-space:nowrap;">
                            {{ $p->tanggal_mulai?->format('d/m/Y') ?? '-' }}
                            @if($p->tanggal_selesai)
                            <div style="font-size:11px;color:#718096;">s.d. {{ $p->tanggal_selesai->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['border'] }};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ str_replace('_', ' ', ucfirst($p->status)) }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;font-size:12px;white-space:nowrap;">
                            @if($p->tanggal_expired)
                                @if($p->is_expired)
                                <span style="color:#c53030;font-weight:700;"><i class="fas fa-times-circle"></i> {{ $p->tanggal_expired->format('d/m/Y') }}</span>
                                @elseif($p->is_near_expired)
                                <span style="color:#c05621;font-weight:700;"><i class="fas fa-exclamation-triangle"></i> {{ $p->tanggal_expired->format('d/m/Y') }}</span>
                                <div style="font-size:10px;color:#c05621;">{{ $p->sisa_hari }} hari lagi</div>
                                @else
                                <span style="color:#276749;">{{ $p->tanggal_expired->format('d/m/Y') }}</span>
                                <div style="font-size:10px;color:#718096;">{{ $p->sisa_hari }} hari</div>
                                @endif
                            @else
                                <span style="color:#a0aec0;">-</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;">
                            @if($p->karyawan_id)
                            <a href="{{ route('admin.karyawan.show', $p->karyawan_id) }}#tab-pelatihan"
                               class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @endif
                            @if($p->lampiran)
                            <a href="{{ asset('storage/' . $p->lampiran) }}" target="_blank"
                               class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-top:3px;">
                                <i class="fas fa-file"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fas fa-graduation-cap" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                            Tidak ada data pelatihan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pelatihan->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #f0f0f0;">
            {{ $pelatihan->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function setFilter(key, value) {
    var url = new URL(window.location.href);
    url.searchParams.set(key, value);
    window.location.href = url.toString();
}
</script>
@endpush

@endsection
