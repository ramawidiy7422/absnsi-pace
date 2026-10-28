@extends('layouts.admin')
@section('title', 'Detail FMK — '.$meninggalkanKerja->nomor_fmk)
@section('page-title', 'Formulir Meninggalkan Kerja')

@section('content')
<div style="max-width:680px;margin:0 auto;">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
    <a href="{{ route('admin.formulir.meninggalkan-kerja.index') }}" style="color:#718096;text-decoration:none;font-size:13px;">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if(!$meninggalkanKerja->isFinal() && auth()->user()->can('approve-fmk', $meninggalkanKerja))
        <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.approve', $meninggalkanKerja) }}" style="margin:0;">
            @csrf
            <button style="background:#f0fff4;color:#276749;border:1px solid #9ae6b4;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-check"></i> Setujui
            </button>
        </form>
        <button onclick="document.getElementById('modalTolak').style.display='flex'"
            style="background:#fff5f5;color:#c53030;border:1px solid #fc8181;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-times"></i> Tolak
        </button>
        @endif
        @if(!$meninggalkanKerja->isFinal() && auth()->user()->hasPermission('fmk.edit'))
        <a href="{{ route('admin.formulir.meninggalkan-kerja.edit', $meninggalkanKerja) }}"
            style="background:#fffbeb;color:#c05621;border:1px solid #f6ad55;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
            <i class="fas fa-edit"></i> Edit
        </a>
        @endif
        <a href="{{ route('admin.formulir.meninggalkan-kerja.pdf', $meninggalkanKerja) }}" target="_blank"
            style="background:#ebf8ff;color:#2b6cb0;border:1px solid #90cdf4;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>
    </div>
</div>

@php
    $sc = match($meninggalkanKerja->status) {
        'disetujui' => ['#f0fff4','#276749'],
        'ditolak'   => ['#fff5f5','#c53030'],
        default     => ['#fffbeb','#c05621'],
    };
@endphp

<div style="background:linear-gradient(135deg,#c05621,#ed8936);border-radius:14px;padding:24px 28px;margin-bottom:16px;color:white;">
    <div style="font-size:11px;opacity:0.8;font-weight:600;letter-spacing:1px;">FORMULIR MENINGGALKAN KERJA</div>
    <div style="font-size:22px;font-weight:800;font-family:monospace;margin:6px 0;">{{ $meninggalkanKerja->nomor_fmk }}</div>
    <span style="background:{{ $sc[0] }};color:{{ $sc[1] }};font-size:12px;font-weight:700;padding:3px 12px;border-radius:12px;">
        {{ $meninggalkanKerja->label_status }}
    </span>
</div>

<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-user" style="color:#ed8936;margin-right:6px;"></i>Identitas Karyawan
    </div>
    @foreach([
        ['Nama',       $meninggalkanKerja->karyawan?->nama],
        ['NIK',        $meninggalkanKerja->karyawan?->nik],
        ['Departemen', $meninggalkanKerja->karyawan?->department?->name ?? '—'],
        ['Bagian',     $meninggalkanKerja->bagian ?: '—'],
        ['Jabatan',    $meninggalkanKerja->karyawan?->position?->name ?? $meninggalkanKerja->karyawan?->jabatan ?? '—'],
        ['Shift',      $meninggalkanKerja->karyawan?->shift ? $meninggalkanKerja->karyawan->shift->nama_shift . ' ('.substr($meninggalkanKerja->karyawan->shift->jam_masuk,0,5).'-'.substr($meninggalkanKerja->karyawan->shift->jam_keluar,0,5).')' : '—'],
    ] as [$l,$v])
    <div style="display:flex;padding:8px 0;border-bottom:1px solid #f7fafc;">
        <div style="width:140px;font-size:12px;color:#a0aec0;flex-shrink:0;">{{ $l }}</div>
        <div style="font-size:13px;font-weight:600;color:#2d3748;">{{ $v }}</div>
    </div>
    @endforeach
</div>

<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-clock" style="color:#ed8936;margin-right:6px;"></i>Keterangan Meninggalkan
    </div>
    @foreach([
        ['Tanggal',         $meninggalkanKerja->tanggal->translatedFormat('d F Y')],
        ['Jam Keluar',      substr($meninggalkanKerja->jam_keluar,0,5) . ' WIB'],
        ['Jam Kembali',     $meninggalkanKerja->jam_kembali ? substr($meninggalkanKerja->jam_kembali,0,5).' WIB' : 'Belum kembali'],
        ['Alasan',          $meninggalkanKerja->label_alasan],
        ['Keterangan',      $meninggalkanKerja->keterangan ?: '—'],
        ['Keterangan Kerja', $meninggalkanKerja->kembali_bekerja === 'kembali' ? '✓ Kembali Bekerja' : '✗ Tidak Kembali'],
        ['SPV / Manager',   $meninggalkanKerja->spv_nama ?: '—'],
        ['HRD',             $meninggalkanKerja->hrd_nama ?: '—'],
    ] as [$l,$v])
    <div style="display:flex;padding:8px 0;border-bottom:1px solid #f7fafc;">
        <div style="width:140px;font-size:12px;color:#a0aec0;flex-shrink:0;">{{ $l }}</div>
        <div style="font-size:13px;font-weight:600;color:#2d3748;">{{ $v }}</div>
    </div>
    @endforeach
</div>

<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-signature" style="color:#ed8936;margin-right:6px;"></i>Progress Approval
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
        @foreach([
            ['SPV', $meninggalkanKerja->spvApprover?->name, $meninggalkanKerja->spv_at, $meninggalkanKerja->spv_note, 'spv'],
            ['Manajer', $meninggalkanKerja->manajerApprover?->name, $meninggalkanKerja->manajer_at, $meninggalkanKerja->manajer_note, 'manajer'],
            ['HRD', $meninggalkanKerja->hrdApprover?->name, $meninggalkanKerja->hrd_at, $meninggalkanKerja->hrd_note, 'hrd'],
        ] as [$label, $nama, $waktu, $note, $stageKey])
        @php
            $ditolakDiSini = $meninggalkanKerja->status === 'ditolak' && $meninggalkanKerja->rejected_stage === $stageKey;
            $bg = $ditolakDiSini ? '#fff5f5' : ($nama ? '#f0fff4' : '#f8fafc');
            $col = $ditolakDiSini ? '#c53030' : ($nama ? '#276749' : '#a0aec0');
        @endphp
        <div style="background:{{ $bg }};border-radius:10px;padding:10px;text-align:center;">
            <i class="fas {{ $ditolakDiSini ? 'fa-times-circle' : ($nama ? 'fa-check-circle' : 'fa-clock') }}" style="color:{{ $col }};font-size:15px;"></i>
            <div style="font-size:12px;font-weight:700;color:#2d3748;margin-top:4px;">{{ $label }}</div>
            <div style="font-size:11px;color:{{ $col }};">{{ $nama ?? 'Menunggu' }}</div>
            @if($waktu)
            <div style="font-size:10px;color:#a0aec0;">{{ $waktu->translatedFormat('d/m/Y H:i') }}</div>
            @endif
            @if($note)
            <div style="font-size:10px;color:#718096;font-style:italic;margin-top:2px;">"{{ $note }}"</div>
            @endif
        </div>
        @endforeach
    </div>
</div>

@if($meninggalkanKerja->status === 'disetujui')
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:24px;">
    <div style="font-weight:700;font-size:13px;color:#4a5568;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fas fa-walking" style="color:#ed8936;margin-right:6px;"></i>Absen Pergi / Kembali (Kiosk)
    </div>
    <div style="display:flex;padding:8px 0;border-bottom:1px solid #f7fafc;">
        <div style="width:140px;font-size:12px;color:#a0aec0;">Absen Pergi</div>
        <div style="font-size:13px;font-weight:600;">{{ $meninggalkanKerja->pergi_at?->translatedFormat('d F Y, H:i') ?? 'Belum absen pergi' }}</div>
    </div>
    <div style="display:flex;padding:8px 0;border-bottom:1px solid #f7fafc;">
        <div style="width:140px;font-size:12px;color:#a0aec0;">Absen Kembali</div>
        <div style="font-size:13px;font-weight:600;">{{ $meninggalkanKerja->kembali_at?->translatedFormat('d F Y, H:i') ?? 'Belum absen kembali' }}</div>
    </div>
    <div style="display:flex;padding:8px 0;">
        <div style="width:140px;font-size:12px;color:#a0aec0;">Durasi</div>
        <div style="font-size:13px;font-weight:600;">
            @if($meninggalkanKerja->alasan === 'keperluan_dinas')
                Dihitung jam kerja penuh (dinas)
            @elseif($meninggalkanKerja->durasi_menit !== null)
                {{ $meninggalkanKerja->durasi_menit }} menit
            @else
                —
            @endif
        </div>
    </div>
</div>
@endif

</div>

{{-- Modal Tolak --}}
<div id="modalTolak" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;padding:28px 32px;width:100%;max-width:440px;margin:16px;">
        <div style="font-size:16px;font-weight:700;color:#2d3748;margin-bottom:6px;"><i class="fas fa-times-circle" style="color:#c53030;"></i> Tolak Formulir</div>
        <div style="font-size:13px;color:#718096;margin-bottom:18px;">Masukkan catatan alasan penolakan untuk karyawan.</div>
        <form method="POST" action="{{ route('admin.formulir.meninggalkan-kerja.tolak', $meninggalkanKerja) }}">
            @csrf
            <textarea name="catatan_approver" rows="3" required
                style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#2d3748;resize:vertical;margin-bottom:16px;"
                placeholder="Contoh: Alasan tidak cukup kuat, harap mengajukan ulang..."></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modalTolak').style.display='none'"
                    style="padding:9px 20px;background:#edf2f7;color:#4a5568;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                    style="padding:9px 20px;background:#c53030;color:white;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-times"></i> Konfirmasi Tolak
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
