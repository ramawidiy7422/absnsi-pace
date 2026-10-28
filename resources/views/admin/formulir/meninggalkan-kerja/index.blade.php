@extends('layouts.admin')
@section('title', 'Formulir Meninggalkan Kerja')
@section('page-title', 'Formulir Meninggalkan Kerja')

@section('content')
<div class="card" style="border-radius:14px;overflow:hidden;">
    <div class="card-header" style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <h6 style="margin:0;font-weight:700;font-size:15px;">
                <i class="fas fa-walking" style="color:#ed8936;margin-right:8px;"></i>
                Formulir Meninggalkan Kerja (FMK)
            </h6>
            <div style="font-size:12px;color:#a0aec0;margin-top:2px;">Total: {{ $formulir->total() }} formulir</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.formulir.index') }}" style="padding:7px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-th-large"></i> Semua Formulir
            </a>
            <a href="{{ route('admin.formulir.meninggalkan-kerja.export-excel', request()->query()) }}" style="padding:7px 14px;background:#1a5c36;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.formulir.meninggalkan-kerja.create') }}" class="btn btn-primary btn-sm" style="background:#c05621;border-color:#c05621;">
                <i class="fas fa-plus"></i> Buat FMK
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIK..."
                style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;width:200px;">
            <select name="status" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Semua Status</option>
                <option value="pending_spv"     {{ request('status')==='pending_spv'     ? 'selected':'' }}>Menunggu SPV</option>
                <option value="pending_manajer" {{ request('status')==='pending_manajer' ? 'selected':'' }}>Menunggu Manajer</option>
                <option value="pending_hrd"     {{ request('status')==='pending_hrd'     ? 'selected':'' }}>Menunggu HRD</option>
                <option value="disetujui" {{ request('status')==='disetujui' ? 'selected':'' }}>Disetujui</option>
                <option value="ditolak"   {{ request('status')==='ditolak'   ? 'selected':'' }}>Ditolak</option>
            </select>
            <select name="alasan" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Semua Alasan</option>
                @foreach(['sakit'=>'Sakit','urusan_keluarga'=>'Urusan Keluarga','urusan_pribadi'=>'Urusan Pribadi','keperluan_dinas'=>'Keperluan Dinas','lainnya'=>'Lainnya'] as $v=>$l)
                <option value="{{ $v }}" {{ request('alasan')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="bulan" style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Semua Bulan</option>
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" {{ request('bulan')==$b ? 'selected':'' }}>
                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['search','status','alasan','bulan','tahun']))
            <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}" style="padding:7px 14px;background:#edf2f7;color:#4a5568;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f7fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Nomor</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Karyawan</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Tanggal</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Jam Keluar</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Alasan</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:700;color:#4a5568;">Status</th>
                    <th style="padding:12px 16px;text-align:center;font-weight:700;color:#4a5568;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($formulir as $fmk)
                @php
                    $sc = match($fmk->status) {
                        'disetujui' => ['#f0fff4','#276749'],
                        'ditolak'   => ['#fff5f5','#c53030'],
                        default     => ['#fffbeb','#c05621'],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;font-family:monospace;font-weight:700;color:#2b6cb0;font-size:12px;">{{ $fmk->nomor_fmk }}</td>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600;">{{ $fmk->karyawan?->nama }}</div>
                        <div style="font-size:11px;color:#a0aec0;">{{ $fmk->karyawan?->department?->name }}</div>
                    </td>
                    <td style="padding:12px 16px;color:#718096;">{{ $fmk->tanggal->translatedFormat('d M Y') }}</td>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600;">{{ substr($fmk->jam_keluar,0,5) }}</div>
                        @if($fmk->jam_kembali)
                        <div style="font-size:11px;color:#a0aec0;">Kembali: {{ substr($fmk->jam_kembali,0,5) }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;">{{ $fmk->label_alasan }}</td>
                    <td style="padding:12px 16px;">
                        <span style="background:{{ $sc[0] }};color:{{ $sc[1] }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
                            {{ $fmk->label_status }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <a href="{{ route('admin.formulir.meninggalkan-kerja.show', $fmk) }}" style="background:#ebf8ff;color:#2b6cb0;border:none;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.formulir.meninggalkan-kerja.pdf', $fmk) }}" target="_blank" style="background:#f0fff4;color:#276749;border:none;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @if(!$fmk->isFinal() && auth()->user()->can('approve-fmk', $fmk))
                            <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.approve', $fmk) }}" style="margin:0;">
                                @csrf
                                <button title="Setujui" style="background:#f0fff4;color:#276749;border:none;padding:5px 10px;border-radius:6px;font-size:12px;cursor:pointer;">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->hasPermission('fmk.hapus'))
                            <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.destroy', $fmk) }}"
                                onsubmit="return confirm('Hapus formulir ini?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button style="background:#fff5f5;color:#c53030;border:none;padding:5px 10px;border-radius:6px;font-size:12px;cursor:pointer;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:50px;color:#a0aec0;">
                        <i class="fas fa-walking" style="font-size:36px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                        Belum ada formulir meninggalkan kerja
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($formulir->hasPages())
    <div style="padding:16px 20px;">{{ $formulir->links() }}</div>
    @endif
</div>
@endsection
