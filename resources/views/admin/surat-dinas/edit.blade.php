@extends('layouts.admin')
@section('title', 'Edit SPD — '.$suratDinas->nomor_spd)
@section('page-title', 'Edit Surat Perintah Dinas')

@section('content')
<div style="max-width:860px;margin:0 auto;">

@if($errors->any())
<div style="background:#fff5f5;border:1px solid #fc8181;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
    @foreach($errors->all() as $e)
    <div style="font-size:13px;color:#c53030;"><i class="fas fa-exclamation-circle"></i> {{ $e }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('admin.surat-dinas.update', $suratDinas) }}">
@csrf @method('PUT')

<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:white;">
    <div style="font-size:11px;font-weight:600;letter-spacing:1px;opacity:0.8;">EDIT SPD</div>
    <div style="font-size:20px;font-weight:800;font-family:monospace;margin-top:4px;">{{ $suratDinas->nomor_spd }}</div>
</div>

{{-- Status --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-toggle-on" style="color:#4299e1;margin-right:8px;"></i>Status SPD
    </div>
    <div style="display:flex;gap:12px;">
        @foreach(['draft'=>['#fffbeb','#c05621'],'disetujui'=>['#f0fff4','#276749'],'ditolak'=>['#fff5f5','#c53030']] as $s=>[$bg,$tc])
        <label style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;background:{{ $bg }};cursor:pointer;border:2px solid {{ $suratDinas->status===$s ? $tc : 'transparent' }};">
            <input type="radio" name="status" value="{{ $s }}" {{ $suratDinas->status===$s ? 'checked' : '' }} style="accent-color:{{ $tc }};">
            <span style="font-size:13px;font-weight:700;color:{{ $tc }};">{{ ucfirst($s) }}</span>
        </label>
        @endforeach
    </div>
</div>

{{-- Info Dinas --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-info-circle" style="color:#4299e1;margin-right:8px;"></i>Informasi Dinas
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Dibuat</label>
            <input type="date" name="tanggal_dibuat" class="form-control"
                value="{{ old('tanggal_dibuat', $suratDinas->tanggal_dibuat->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Departemen</label>
            <select name="department_id" class="form-control">
                <option value="">— Pilih Departemen —</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id',$suratDinas->department_id)==$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Keperluan / Tujuan Dinas</label>
            <input type="text" name="keperluan" class="form-control"
                value="{{ old('keperluan', $suratDinas->keperluan) }}" required>
        </div>
    </div>
</div>

{{-- Staf --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-users" style="color:#4299e1;margin-right:8px;"></i>Staf yang Bertugas
    </div>
    <input type="text" id="searchStaf" placeholder="Cari nama karyawan..."
        style="padding:8px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;margin-bottom:12px;box-sizing:border-box;">
    <div id="stafList" style="max-height:220px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;padding:8px;">
        @foreach($karyawan as $k)
        <label style="display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:8px;cursor:pointer;"
            onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">
            <input type="checkbox" name="staf_ids[]" value="{{ $k->id }}"
                {{ in_array($k->id, old('staf_ids', $stafTerpilih)) ? 'checked' : '' }}
                style="width:16px;height:16px;accent-color:#4299e1;">
            <div>
                <div style="font-size:13px;font-weight:600;">{{ $k->nama }}</div>
                <div style="font-size:11px;color:#a0aec0;">{{ $k->department?->name ?? '-' }} · {{ $k->position?->name ?? $k->jabatan ?? '-' }}</div>
            </div>
        </label>
        @endforeach
    </div>
    <div id="stafCount" style="font-size:12px;color:#718096;margin-top:8px;">0 staf dipilih</div>
</div>

{{-- Jadwal & Kendaraan --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-calendar-alt" style="color:#4299e1;margin-right:8px;"></i>Jadwal & Kendaraan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Berangkat</label>
            <input type="date" name="tanggal_berangkat" class="form-control"
                value="{{ old('tanggal_berangkat', $suratDinas->tanggal_berangkat->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Kembali</label>
            <input type="date" name="tanggal_kembali" class="form-control"
                value="{{ old('tanggal_kembali', $suratDinas->tanggal_kembali->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Jam Berangkat</label>
            <input type="time" name="jam_berangkat" class="form-control"
                value="{{ old('jam_berangkat', $suratDinas->jam_berangkat ? substr($suratDinas->jam_berangkat,0,5) : '') }}">
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Jenis Kendaraan</label>
            <select name="jenis_kendaraan" class="form-control" required>
                @foreach(['motor'=>'Motor','mobil_dinas'=>'Mobil Dinas','mobil_pribadi'=>'Mobil Pribadi','angkutan_umum'=>'Angkutan Umum','pesawat'=>'Pesawat','kereta'=>'Kereta','lainnya'=>'Lainnya'] as $val=>$lbl)
                <option value="{{ $val }}" {{ old('jenis_kendaraan',$suratDinas->jenis_kendaraan)===$val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Nomor Kendaraan</label>
            <input type="text" name="nomor_kendaraan" class="form-control"
                value="{{ old('nomor_kendaraan', $suratDinas->nomor_kendaraan) }}" style="text-transform:uppercase;">
        </div>
    </div>
</div>

{{-- Lokasi --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-map-marker-alt" style="color:#4299e1;margin-right:8px;"></i>Lokasi Tujuan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Kota Tujuan</label>
            <input type="text" name="kota_tujuan" class="form-control"
                value="{{ old('kota_tujuan', $suratDinas->kota_tujuan) }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tempat / Lokasi Spesifik</label>
            <input type="text" name="lokasi_tujuan" class="form-control"
                value="{{ old('lokasi_tujuan', $suratDinas->lokasi_tujuan) }}" required>
        </div>
    </div>
</div>

{{-- Approval --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-signature" style="color:#4299e1;margin-right:8px;"></i>Tanda Tangan & Persetujuan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div>
            <div style="font-size:12px;font-weight:700;color:#4a5568;margin-bottom:10px;">Yang Memberi Tugas (SPV/Manager)</div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Nama</label>
                <input type="text" name="pemberi_tugas_nama" class="form-control"
                    value="{{ old('pemberi_tugas_nama', $suratDinas->pemberi_tugas_nama) }}" placeholder="Nama SPV/Manager">
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Jabatan</label>
                <input type="text" name="pemberi_tugas_jabatan" class="form-control"
                    value="{{ old('pemberi_tugas_jabatan', $suratDinas->pemberi_tugas_jabatan) }}" placeholder="Jabatan">
            </div>
        </div>
        <div>
            <div style="font-size:12px;font-weight:700;color:#4a5568;margin-bottom:10px;">Mengetahui HRD</div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Nama HRD</label>
                <input type="text" name="hrd_nama" class="form-control"
                    value="{{ old('hrd_nama', $suratDinas->hrd_nama) }}" placeholder="Nama HRD">
            </div>
        </div>
    </div>
    <div class="form-group" style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#4a5568;">Catatan</label>
        <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $suratDinas->catatan) }}</textarea>
    </div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:24px;">
    <a href="{{ route('admin.surat-dinas.show', $suratDinas) }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update SPD</button>
</div>

</form>
</div>

<script>
document.getElementById('searchStaf').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#stafList label').forEach(lbl => {
        lbl.style.display = lbl.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
function updateCount() {
    const n = document.querySelectorAll('#stafList input:checked').length;
    document.getElementById('stafCount').textContent = n + ' staf dipilih';
}
document.querySelectorAll('#stafList input').forEach(cb => cb.addEventListener('change', updateCount));
updateCount();
</script>
@endsection
