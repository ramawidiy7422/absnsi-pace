@extends('layouts.admin')
@section('title','Detail SPK Lembur')
@section('page-title','Detail SPK Lembur')

@section('content')
@php $badge = $spkLembur->status_badge; @endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

{{-- Kiri: Detail --}}
<div>
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:20px;">
        <div style="background:#1e40af;color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:11px;opacity:.8;text-transform:uppercase;letter-spacing:1px;">Surat Perintah Kerja Lembur</div>
                <div style="font-size:22px;font-weight:800;letter-spacing:1px;">{{ $spkLembur->nomor_spk }}</div>
            </div>
            <span style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border-radius:20px;padding:6px 18px;font-weight:700;font-size:13px;">
                {{ $badge['label'] }}
            </span>
        </div>
        <div style="padding:24px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Target</div>
                    <div style="font-weight:700;font-size:15px;">{{ $spkLembur->target_label }}</div>
                    @if($spkLembur->department_id && !$spkLembur->karyawan_id)
                    <div style="font-size:12px;color:#94a3b8;">Seluruh departemen</div>
                    @endif
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Tanggal</div>
                    <div style="font-weight:700;font-size:15px;">{{ $spkLembur->tanggal->translatedFormat('l, d F Y') }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Jam Lembur</div>
                    <div style="font-weight:700;font-size:15px;">
                        {{ substr($spkLembur->jam_mulai,0,5) }} – {{ substr($spkLembur->jam_selesai,0,5) }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Estimasi Jam</div>
                    <div style="font-weight:700;font-size:15px;">
                        {{ $spkLembur->estimasi_jam ? number_format($spkLembur->estimasi_jam,1).' jam' : '—' }}
                    </div>
                </div>
                @if($spkLembur->lokasi)
                <div style="grid-column:1/-1;">
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Lokasi</div>
                    <div style="font-weight:600;">{{ $spkLembur->lokasi }}</div>
                </div>
                @endif
            </div>

            <div style="background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Pekerjaan yang Dilakukan</div>
                <div style="font-size:14px;line-height:1.6;color:#1e293b;">{{ $spkLembur->pekerjaan }}</div>
            </div>

            @if($spkLembur->catatan)
            <div style="border-left:3px solid #cbd5e1;padding-left:12px;">
                <div style="font-size:11px;color:#64748b;margin-bottom:4px;">Catatan</div>
                <div style="font-size:13px;color:#475569;">{{ $spkLembur->catatan }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Realisasi Lembur --}}
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-weight:700;font-size:15px;">Realisasi Lembur <span style="background:#f1f5f9;color:#64748b;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">{{ $spkLembur->lemburan->count() }}</span></div>
        </div>
        @if($spkLembur->lemburan->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jam Aktual</th>
                        <th>Total Jam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($spkLembur->lemburan as $l)
                @php $sb = $l->status_badge; @endphp
                <tr>
                    <td style="font-weight:600;">{{ $l->karyawan?->nama ?? '—' }}</td>
                    <td style="font-size:12px;color:#64748b;">
                        {{ $l->jam_mulai ? substr($l->jam_mulai,0,5).' – '.substr($l->jam_selesai,0,5) : '—' }}
                    </td>
                    <td style="font-weight:700;">{{ $l->total_jam ? number_format($l->total_jam,1).' j' : '—' }}</td>
                    <td>
                        <span style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;">
                            {{ $sb['label'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align:center;padding:28px;color:#94a3b8;font-size:13px;">Belum ada karyawan yang mengajukan realisasi lembur.</div>
        @endif
    </div>
</div>

{{-- Kanan: Info & Aksi --}}
<div>
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:16px;">
        <div style="font-weight:700;margin-bottom:16px;font-size:14px;color:#374151;">Informasi</div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <div style="font-size:11px;color:#94a3b8;">Dibuat oleh</div>
                <div style="font-size:13px;font-weight:600;">{{ $spkLembur->pembuat?->name ?? '—' }}</div>
                <div style="font-size:11px;color:#94a3b8;">{{ $spkLembur->created_at->translatedFormat('d M Y H:i') }}</div>
            </div>
            @if($spkLembur->disetujui_oleh)
            <div>
                <div style="font-size:11px;color:#94a3b8;">Diaktifkan oleh</div>
                <div style="font-size:13px;font-weight:600;">{{ $spkLembur->penyetuju?->name ?? '—' }}</div>
                <div style="font-size:11px;color:#94a3b8;">{{ $spkLembur->disetujui_at?->translatedFormat('d M Y H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($spkLembur->status === 'draft')
    @can('lembur.approve')
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:16px;">
        <div style="font-weight:700;margin-bottom:12px;font-size:14px;">Aksi</div>
        <form method="POST" action="{{ route('admin.spk-lembur.aktifkan', $spkLembur) }}">@csrf
            <button class="btn btn-success" style="width:100%;margin-bottom:8px;">
                <i class="fas fa-check"></i> Aktifkan & Notifikasi Karyawan
            </button>
        </form>
        @can('lembur.hapus')
        <form method="POST" action="{{ route('admin.spk-lembur.destroy', $spkLembur) }}" onsubmit="return confirm('Hapus SPK ini?')">@csrf @method('DELETE')
            <button class="btn btn-danger" style="width:100%;">
                <i class="fas fa-trash"></i> Hapus SPK
            </button>
        </form>
        @endcan
    </div>
    @endcan
    @elseif($spkLembur->status === 'aktif')
    @can('lembur.approve')
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:16px;">
        <div style="font-weight:700;margin-bottom:12px;font-size:14px;">Aksi</div>
        <form method="POST" action="{{ route('admin.spk-lembur.selesaikan', $spkLembur) }}">@csrf
            <button class="btn btn-primary" style="width:100%;margin-bottom:8px;">
                <i class="fas fa-flag-checkered"></i> Tandai Selesai
            </button>
        </form>
        <form method="POST" action="{{ route('admin.spk-lembur.batalkan', $spkLembur) }}" onsubmit="return confirm('Batalkan SPK ini?')">@csrf
            <button class="btn btn-danger" style="width:100%;">
                <i class="fas fa-ban"></i> Batalkan SPK
            </button>
        </form>
    </div>
    @endcan
    @endif

    <a href="{{ route('admin.spk-lembur.index') }}" class="btn btn-secondary" style="width:100%;">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

</div>
@endsection
