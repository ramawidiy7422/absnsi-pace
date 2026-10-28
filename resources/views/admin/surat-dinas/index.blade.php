@extends('layouts.admin')
@section('title', 'Surat Perintah Dinas')
@section('page-title', 'Surat Perintah Dinas')

@section('content')
<div class="card" style="border-radius:14px;overflow:hidden;">
    <div class="card-header" style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <h6 style="margin:0;font-weight:700;font-size:15px;">
                <i class="fas fa-briefcase" style="color:#4299e1;margin-right:8px;"></i>
                Surat Perintah Dinas (SPD)
            </h6>
            <div style="font-size:12px;color:#a0aec0;margin-top:2px;">Total: {{ $dinas->total() }} SPD</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.surat-dinas.export-excel', request()->query()) }}" style="padding:7px 14px;background:#1a5c36;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.surat-dinas.export-pdf-bulk', request()->query()) }}" target="_blank" style="padding:7px 14px;background:#c53030;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-file-pdf"></i> PDF Semua
            </a>
            <a href="{{ route('admin.surat-dinas.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Buat SPD Baru
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nomor, keperluan, kota..."
                style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;width:220px;">
            <select name="status" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status')==='draft' ? 'selected' : '' }}>Draft</option>
                <option value="disetujui" {{ request('status')==='disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="ditolak" {{ request('status')==='ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <select name="bulan" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Semua Bulan</option>
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" {{ request('bulan')==$b ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
            <select name="tahun" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                @foreach($tahunList as $t)
                <option value="{{ $t }}" {{ request('tahun')==$t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Filter
            </button>
            @if(request()->hasAny(['search','status','bulan','tahun']))
            <a href="{{ route('admin.surat-dinas.index') }}" class="btn btn-sm" style="background:#edf2f7;color:#4a5568;border-radius:8px;padding:7px 14px;font-size:13px;text-decoration:none;">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Nomor SPD</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Tanggal</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Keperluan</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Tujuan</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Staf</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Status</th>
                    <th style="padding:12px 16px;text-align:center;font-weight:700;color:#4a5568;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dinas as $spd)
                @php
                    $statusColor = match($spd->status) {
                        'disetujui' => ['bg'=>'#f0fff4','text'=>'#276749'],
                        'ditolak'   => ['bg'=>'#fff5f5','text'=>'#c53030'],
                        default     => ['bg'=>'#fffbeb','text'=>'#c05621'],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;">
                        <a href="{{ route('admin.surat-dinas.show', $spd) }}" style="font-weight:700;color:#2b6cb0;text-decoration:none;font-family:monospace;">
                            {{ $spd->nomor_spd }}
                        </a>
                    </td>
                    <td style="padding:12px 16px;color:#718096;">
                        {{ $spd->tanggal_dibuat->translatedFormat('d M Y') }}
                    </td>
                    <td style="padding:12px 16px;font-weight:500;max-width:200px;">
                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $spd->keperluan }}</div>
                        <div style="font-size:11px;color:#a0aec0;">{{ $spd->department?->name ?? '-' }}</div>
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600;">{{ $spd->kota_tujuan }}</div>
                        <div style="font-size:11px;color:#a0aec0;">
                            {{ $spd->tanggal_berangkat->translatedFormat('d M') }} –
                            {{ $spd->tanggal_kembali->translatedFormat('d M Y') }}
                        </div>
                    </td>
                    <td style="padding:12px 16px;">
                        @foreach(($spd->staf ?? collect())->take(2) as $s)
                        <div style="font-size:12px;">{{ $s->nama }}</div>
                        @endforeach
                        @if(($spd->staf?->count() ?? 0) > 2)
                        <div style="font-size:11px;color:#a0aec0;">+{{ $spd->staf->count() - 2 }} lainnya</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;">
                        <span style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
                            {{ $spd->label_status }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <a href="{{ route('admin.surat-dinas.show', $spd) }}" class="btn btn-sm" style="background:#ebf8ff;color:#2b6cb0;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.surat-dinas.pdf', $spd) }}" target="_blank" class="btn btn-sm" style="background:#f0fff4;color:#276749;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <a href="{{ route('admin.surat-dinas.edit', $spd) }}" class="btn btn-sm" style="background:#fffbeb;color:#c05621;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.surat-dinas.destroy', $spd) }}"
                                onsubmit="return confirm('Hapus SPD {{ $spd->nomor_spd }}?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:#fff5f5;color:#c53030;border:none;padding:5px 10px;border-radius:6px;font-size:12px;cursor:pointer;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:50px 20px;color:#a0aec0;">
                        <i class="fas fa-briefcase" style="font-size:36px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                        Belum ada Surat Perintah Dinas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dinas->hasPages())
    <div style="padding:16px 20px;">{{ $dinas->links() }}</div>
    @endif
</div>
@endsection
