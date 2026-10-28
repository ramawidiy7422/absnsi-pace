@extends('layouts.admin')
@section('title', 'Buat Surat Perintah Dinas')
@section('page-title', 'Buat Surat Perintah Dinas')

@section('content')
<div style="max-width:860px;margin:0 auto;">

@if($errors->any())
<div style="background:#fff5f5;border:1px solid #fc8181;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
    @foreach($errors->all() as $e)
    <div style="font-size:13px;color:#c53030;"><i class="fas fa-exclamation-circle"></i> {{ $e }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('admin.surat-dinas.store') }}">
@csrf

{{-- HEADER CARD --}}
<div style="background:linear-gradient(135deg,#2b6cb0,#4299e1);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:white;">
    <div style="font-size:11px;font-weight:600;letter-spacing:1px;opacity:0.8;">NOMOR SPD (AUTO)</div>
    <div style="font-size:20px;font-weight:800;font-family:monospace;margin-top:4px;">{{ $nomorSPD }}</div>
    <div style="font-size:12px;opacity:0.7;margin-top:4px;">PT Global Multi Pharmalab</div>
</div>

{{-- BAGIAN 1: INFO DINAS --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-info-circle" style="color:#4299e1;margin-right:8px;"></i>Informasi Dinas
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Dibuat <span style="color:red">*</span></label>
            <input type="date" name="tanggal_dibuat" class="form-control"
                value="{{ old('tanggal_dibuat', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Departemen</label>
            <select name="department_id" class="form-control">
                <option value="">— Pilih Departemen —</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id')==$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Keperluan / Tujuan Dinas <span style="color:red">*</span></label>
            <input type="text" name="keperluan" class="form-control"
                value="{{ old('keperluan') }}" placeholder="Contoh: Pengiriman dokumen ke klien, Monitoring proyek..." required>
        </div>
    </div>
</div>

{{-- BAGIAN 2: STAF BERTUGAS --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-users" style="color:#4299e1;margin-right:8px;"></i>Staf yang Bertugas
        <span style="font-size:11px;color:#a0aec0;font-weight:400;margin-left:8px;">(bisa pilih lebih dari satu)</span>
    </div>

    {{-- Search staf --}}
    <input type="text" id="searchStaf" placeholder="Cari nama karyawan..."
        style="padding:8px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;width:100%;margin-bottom:12px;box-sizing:border-box;">

    <div id="stafList" style="max-height:260px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;padding:8px;">
        @foreach($karyawan as $k)
        <label style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;cursor:pointer;transition:background 0.15s;"
            onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">
            <input type="checkbox" name="staf_ids[]" value="{{ $k->id }}"
                {{ in_array($k->id, old('staf_ids',[])) ? 'checked' : '' }}
                style="width:16px;height:16px;accent-color:#4299e1;">
            <div>
                <div style="font-size:13px;font-weight:600;color:#2d3748;">{{ $k->nama }}</div>
                <div style="font-size:11px;color:#a0aec0;">{{ $k->department?->name ?? '-' }} · {{ $k->position?->name ?? $k->jabatan ?? '-' }}</div>
            </div>
        </label>
        @endforeach
    </div>
    <div id="stafSelectedCount" style="font-size:12px;color:#718096;margin-top:8px;">0 staf dipilih</div>
</div>

{{-- BAGIAN 3: JADWAL & KENDARAAN --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-calendar-alt" style="color:#4299e1;margin-right:8px;"></i>Jadwal & Kendaraan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Berangkat <span style="color:red">*</span></label>
            <input type="date" name="tanggal_berangkat" class="form-control"
                value="{{ old('tanggal_berangkat') }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tanggal Kembali <span style="color:red">*</span></label>
            <input type="date" name="tanggal_kembali" class="form-control"
                value="{{ old('tanggal_kembali') }}" required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Jam Berangkat</label>
            <input type="time" name="jam_berangkat" class="form-control"
                value="{{ old('jam_berangkat') }}">
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Jenis Kendaraan <span style="color:red">*</span></label>
            <select name="jenis_kendaraan" class="form-control" required>
                <option value="motor"         {{ old('jenis_kendaraan')==='motor' ? 'selected' : '' }}>Motor</option>
                <option value="mobil_dinas"   {{ old('jenis_kendaraan')==='mobil_dinas' ? 'selected' : '' }}>Mobil Dinas</option>
                <option value="mobil_pribadi" {{ old('jenis_kendaraan')==='mobil_pribadi' ? 'selected' : '' }}>Mobil Pribadi</option>
                <option value="angkutan_umum" {{ old('jenis_kendaraan')==='angkutan_umum' ? 'selected' : '' }}>Angkutan Umum</option>
                <option value="pesawat"       {{ old('jenis_kendaraan')==='pesawat' ? 'selected' : '' }}>Pesawat</option>
                <option value="kereta"        {{ old('jenis_kendaraan')==='kereta' ? 'selected' : '' }}>Kereta</option>
                <option value="lainnya"       {{ old('jenis_kendaraan')==='lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Nomor Kendaraan</label>
            <input type="text" name="nomor_kendaraan" class="form-control"
                value="{{ old('nomor_kendaraan') }}" placeholder="Contoh: B 1234 ABC" style="text-transform:uppercase;">
        </div>
    </div>
</div>

{{-- BAGIAN 4: LOKASI TUJUAN --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-map-marker-alt" style="color:#4299e1;margin-right:8px;"></i>Lokasi Tujuan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Kota Tujuan <span style="color:red">*</span></label>
            <input type="text" name="kota_tujuan" class="form-control"
                value="{{ old('kota_tujuan') }}" placeholder="Contoh: Jakarta, Bandung, Surabaya..." required>
        </div>
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#4a5568;">Tempat / Lokasi Spesifik <span style="color:red">*</span></label>
            <input type="text" name="lokasi_tujuan" class="form-control"
                value="{{ old('lokasi_tujuan') }}" placeholder="Contoh: PT ABC, Jl. Sudirman No.10..." required>
        </div>
    </div>
</div>

{{-- BAGIAN 5: APPROVAL --}}
<div style="background:white;border-radius:14px;border:1px solid #e2e8f0;padding:22px 24px;margin-bottom:16px;">
    <div style="font-weight:700;font-size:14px;color:#2d3748;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #ebf8ff;">
        <i class="fas fa-signature" style="color:#4299e1;margin-right:8px;"></i>Tanda Tangan & Persetujuan
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- Pemberi Tugas --}}
        <div>
            <div style="font-size:12px;font-weight:700;color:#4a5568;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;">
                <i class="fas fa-user-tie" style="color:#805ad5;margin-right:4px;"></i>Yang Memberi Tugas (SPV/Manager)
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Pilih dari karyawan</label>
                <select name="pemberi_tugas_id" class="form-control" id="spvSelect">
                    <option value="">— Ketik nama manual di bawah —</option>
                    @foreach($karyawan as $k)
                    <option value="{{ $k->id }}" {{ old('pemberi_tugas_id')==$k->id ? 'selected' : '' }}>
                        {{ $k->nama }} — {{ $k->position?->name ?? $k->jabatan ?? '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Atau ketik nama</label>
                <input type="text" name="pemberi_tugas_nama" id="spvNama" class="form-control"
                    value="{{ old('pemberi_tugas_nama') }}" placeholder="Nama SPV/Manager">
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Jabatan</label>
                <input type="text" name="pemberi_tugas_jabatan" id="spvJabatan" class="form-control"
                    value="{{ old('pemberi_tugas_jabatan') }}" placeholder="Jabatan">
            </div>
        </div>
        {{-- HRD --}}
        <div>
            <div style="font-size:12px;font-weight:700;color:#4a5568;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;">
                <i class="fas fa-user-check" style="color:#38a169;margin-right:4px;"></i>Mengetahui HRD
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Pilih dari karyawan</label>
                <select name="hrd_id" class="form-control" id="hrdSelect">
                    <option value="">— Ketik nama manual di bawah —</option>
                    @foreach($karyawan as $k)
                    <option value="{{ $k->id }}" {{ old('hrd_id')==$k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label style="font-size:12px;color:#718096;">Atau ketik nama</label>
                <input type="text" name="hrd_nama" id="hrdNama" class="form-control"
                    value="{{ old('hrd_nama') }}" placeholder="Nama HRD">
            </div>
        </div>
    </div>
    <div class="form-group" style="margin-top:8px;">
        <label style="font-size:12px;font-weight:600;color:#4a5568;">Catatan (opsional)</label>
        <textarea name="catatan" class="form-control" rows="2"
            placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
    </div>
</div>

{{-- ACTION BUTTONS --}}
<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:24px;">
    <a href="{{ route('admin.surat-dinas.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Simpan SPD
    </button>
</div>

</form>
</div>

<script>
// Search staf
document.getElementById('searchStaf').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#stafList label').forEach(lbl => {
        lbl.style.display = lbl.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Hitung staf terpilih
function updateCount() {
    const n = document.querySelectorAll('#stafList input:checked').length;
    document.getElementById('stafSelectedCount').textContent = n + ' staf dipilih';
}
document.querySelectorAll('#stafList input').forEach(cb => cb.addEventListener('change', updateCount));
updateCount();

// Auto-isi nama SPV dari dropdown
document.getElementById('spvSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (this.value) {
        const parts = opt.text.split('—');
        document.getElementById('spvNama').value = (parts[0] || '').trim();
        document.getElementById('spvJabatan').value = (parts[1] || '').trim();
    }
});

// Auto-isi nama HRD dari dropdown
document.getElementById('hrdSelect').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('hrdNama').value = this.options[this.selectedIndex].text.split('—')[0].trim();
    }
});
</script>
@endsection
