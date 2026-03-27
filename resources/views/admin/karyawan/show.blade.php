@extends('layouts.admin')
@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div style="display:grid; grid-template-columns:1fr 2fr; gap:20px;">
    <div class="card">
        <div class="card-body" style="text-align:center;">
            @if($karyawan->foto_wajah)
                <img src="{{ asset('storage/' . $karyawan->foto_wajah) }}" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #4299e1;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="width:120px; height:120px; background:#e2e8f0; border-radius:50%; display:none; align-items:center; justify-content:center; margin:0 auto; font-size:48px; color:#a0aec0;">
                    <i class="fas fa-user"></i>
                </div>
            @else
                <div style="width:120px; height:120px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto; font-size:48px; color:#a0aec0;">
                    <i class="fas fa-user"></i>
                </div>
            @endif
            <h4 style="margin-top:15px; font-size:18px;">{{ $karyawan->nama }}</h4>
            <p style="color:#718096; font-size:13px;">{{ $karyawan->jabatan }}</p>
            <span class="badge badge-{{ $karyawan->status }}" style="margin-top:5px;">{{ ucfirst($karyawan->status) }}</span>

            <div style="margin-top:20px; text-align:left;">
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0; font-size:13px;">
                    <span style="color:#718096;">NIK</span><strong>{{ $karyawan->nik }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0; font-size:13px;">
                    <span style="color:#718096;">Departemen</span><strong>{{ $karyawan->departemen }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0; font-size:13px;">
                    <span style="color:#718096;">Shift</span><strong>{{ $karyawan->shift->nama_shift ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px;">
                    <span style="color:#718096;">Tgl Masuk</span><strong>{{ $karyawan->tanggal_masuk }}</strong>
                </div>
            </div>

            {{-- QR Code --}}
            <div style="margin-top:20px; padding:16px; background:#f8f4ff; border-radius:12px; border:1px solid #d6bcfa; text-align:center;">
                <p style="font-size:12px; color:#805ad5; font-weight:600; margin-bottom:10px;"><i class="fas fa-qrcode"></i> QR Code Absensi</p>
                <img src="{{ route('admin.karyawan.qrcode', $karyawan) }}" alt="QR Code {{ $karyawan->nik }}"
                     style="width:150px; height:150px; border-radius:8px; background:#fff; padding:4px;">
                <p style="font-size:11px; color:#718096; margin-top:8px;">Scan QR ini di kiosk sebagai backup</p>
                <a href="{{ route('admin.karyawan.print-qr', $karyawan) }}" target="_blank"
                   style="display:inline-block; margin-top:8px; padding:6px 16px; background:#805ad5; color:#fff; border-radius:6px; font-size:12px; text-decoration:none;">
                    <i class="fas fa-print"></i> Print Kartu QR
                </a>
            </div>

            <div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap;">
                @if(in_array(auth()->user()->role, ['admin','hrd']))
                <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-warning" style="flex:1; justify-content:center; min-width:100px;">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary" style="flex:1; justify-content:center; min-width:100px;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-history"></i> Riwayat Absensi (30 Terakhir)</h6>
        </div>
        <div class="card-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Akurasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensiTerbaru as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td>{{ $a->jam_keluar ?? '-' }}</td>
                        <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td>
                            @if(($a->metode ?? 'wajah') === 'qrcode')
                                <span style="font-size:11px; background:#f3e8ff; color:#805ad5; padding:2px 8px; border-radius:10px;"><i class="fas fa-qrcode"></i> QR</span>
                            @else
                                <span style="font-size:11px; background:#ebf8ff; color:#2b6cb0; padding:2px 8px; border-radius:10px;"><i class="fas fa-face-smile"></i> Wajah</span>
                            @endif
                        </td>
                        <td>{{ $a->confidence_masuk ? $a->confidence_masuk . '%' : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; padding:20px; color:#718096;">Belum ada data absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
