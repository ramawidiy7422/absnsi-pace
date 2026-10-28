@extends('layouts.admin')
@section('title','SPK Lembur')
@section('page-title','Surat Perintah Kerja Lembur')

@section('content')
{{-- Stat --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #3b82f6;">
        <div style="font-size:26px;font-weight:800;color:#1e40af;">{{ $statTotal }}</div>
        <div style="font-size:11px;color:#718096;">Total SPK</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #059669;">
        <div style="font-size:26px;font-weight:800;color:#065f46;">{{ $statAktif }}</div>
        <div style="font-size:11px;color:#718096;">Aktif Sekarang</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #e2e8f0;text-align:center;border-top:3px solid #d97706;">
        <div style="font-size:26px;font-weight:800;color:#92400e;">{{ $statHariIni }}</div>
        <div style="font-size:11px;color:#718096;">Hari Ini</div>
    </div>
</div>

{{-- Toolbar --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:16px;margin-bottom:16px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;flex:1;">
            <input type="text" name="search" placeholder="Cari nomor/pekerjaan/karyawan..." class="form-control" value="{{ request('search') }}" style="min-width:200px;flex:1;">
            <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}" style="width:140px;">
            <select name="status" class="form-select" style="width:140px;">
                <option value="">Semua Status</option>
                @foreach(['draft','aktif','selesai','dibatalkan'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="department_id" class="form-select" style="width:160px;">
                <option value="">Semua Dept.</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.spk-lembur.index') }}" class="btn btn-secondary">Reset</a>
        </form>
        @can('lembur.tambah')
        <a href="{{ route('admin.spk-lembur.create') }}" class="btn btn-success" style="white-space:nowrap;">
            <i class="fas fa-plus"></i> Buat SPK
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nomor SPK</th>
                    <th>Target</th>
                    <th>Tanggal & Jam</th>
                    <th>Pekerjaan</th>
                    <th style="text-align:center;">Est. Jam</th>
                    <th style="text-align:center;">Status</th>
                    <th>Dibuat Oleh</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($spkList as $spk)
            @php $badge = $spk->status_badge; @endphp
            <tr>
                <td>
                    <a href="{{ route('admin.spk-lembur.show', $spk) }}" style="font-weight:700;color:#1e40af;text-decoration:none;">
                        {{ $spk->nomor_spk }}
                    </a>
                    @if($spk->is_mendadak ?? false)
                    <span style="display:block;font-size:10px;color:#dc2626;font-weight:600;">MENDADAK</span>
                    @endif
                </td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $spk->target_label }}</div>
                    @if($spk->department_id && !$spk->karyawan_id)
                    <div style="font-size:11px;color:#94a3b8;">Seluruh departemen</div>
                    @endif
                </td>
                <td style="font-size:12px;">
                    <div style="font-weight:600;">{{ $spk->tanggal->translatedFormat('d M Y') }}</div>
                    <div style="color:#64748b;">{{ substr($spk->jam_mulai,0,5) }} – {{ substr($spk->jam_selesai,0,5) }}</div>
                </td>
                <td style="max-width:200px;font-size:12px;color:#374151;">
                    {{ Str::limit($spk->pekerjaan, 60) }}
                </td>
                <td style="text-align:center;font-weight:700;color:#374151;">
                    {{ $spk->estimasi_jam ? number_format($spk->estimasi_jam,1).' j' : '—' }}
                </td>
                <td style="text-align:center;">
                    <span style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border-radius:20px;padding:3px 12px;font-size:11px;font-weight:700;">
                        {{ $badge['label'] }}
                    </span>
                </td>
                <td style="font-size:12px;color:#64748b;">{{ $spk->pembuat?->name ?? '-' }}</td>
                <td>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        <a href="{{ route('admin.spk-lembur.show', $spk) }}" class="btn btn-sm btn-secondary" title="Detail"><i class="fas fa-eye"></i></a>
                        @if($spk->status === 'draft')
                            @can('lembur.approve')
                            <form method="POST" action="{{ route('admin.spk-lembur.aktifkan', $spk) }}">@csrf
                                <button class="btn btn-sm btn-success" title="Aktifkan"><i class="fas fa-check"></i></button>
                            </form>
                            @endcan
                            @can('lembur.hapus')
                            <form method="POST" action="{{ route('admin.spk-lembur.destroy', $spk) }}" onsubmit="return confirm('Hapus SPK ini?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        @elseif($spk->status === 'aktif')
                            @can('lembur.approve')
                            <form method="POST" action="{{ route('admin.spk-lembur.selesaikan', $spk) }}">@csrf
                                <button class="btn btn-sm btn-primary" title="Selesaikan"><i class="fas fa-flag-checkered"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.spk-lembur.batalkan', $spk) }}" onsubmit="return confirm('Batalkan SPK ini?')">@csrf
                                <button class="btn btn-sm btn-danger" title="Batalkan"><i class="fas fa-ban"></i></button>
                            </form>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Belum ada SPK Lembur.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px;">{{ $spkList->links() }}</div>
@endsection
