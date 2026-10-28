@extends('layouts.admin')
@section('title','Buat SPK Lembur')
@section('page-title','Buat Surat Perintah Kerja Lembur')

@section('content')
<div style="max-width:740px;">
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:28px;">
        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.spk-lembur.store') }}">@csrf

        {{-- Target --}}
        <div class="form-group" style="margin-bottom:18px;">
            <label style="font-weight:600;display:block;margin-bottom:8px;">Target Lembur <span style="color:#dc2626;">*</span></label>
            <div style="display:flex;gap:16px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                    <input type="radio" name="target" value="karyawan" {{ old('target','karyawan')==='karyawan'?'checked':'' }} onchange="toggleTarget(this.value)">
                    Karyawan Tertentu
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                    <input type="radio" name="target" value="departemen" {{ old('target')==='departemen'?'checked':'' }} onchange="toggleTarget(this.value)">
                    Seluruh Departemen
                </label>
            </div>
        </div>

        <div id="wrapKaryawan" style="{{ old('target')!=='departemen' ? '' : 'display:none;' }}margin-bottom:18px;">
            <label class="form-label">Karyawan <span style="color:#dc2626;">*</span></label>
            <select name="karyawan_id" class="form-select">
                <option value="">-- Pilih Karyawan --</option>
                @foreach($karyawan as $k)
                <option value="{{ $k->id }}" {{ old('karyawan_id')==$k->id?'selected':'' }}>
                    {{ $k->nama }} — {{ $k->department?->name ?? '-' }}
                </option>
                @endforeach
            </select>
        </div>

        <div id="wrapDept" style="{{ old('target')==='departemen' ? '' : 'display:none;' }}margin-bottom:18px;">
            <label class="form-label">Departemen <span style="color:#dc2626;">*</span></label>
            <select name="department_id" class="form-select">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tanggal & Jam --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:18px;">
            <div>
                <label class="form-label">Tanggal Lembur <span style="color:#dc2626;">*</span></label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', today()->toDateString()) }}" required>
            </div>
            <div>
                <label class="form-label">Jam Mulai <span style="color:#dc2626;">*</span></label>
                <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai','17:00') }}" required>
            </div>
            <div>
                <label class="form-label">Jam Selesai <span style="color:#dc2626;">*</span></label>
                <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai','21:00') }}" required>
            </div>
        </div>

        {{-- Estimasi & Lokasi --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px;">
            <div>
                <label class="form-label">Estimasi Jam Lembur</label>
                <input type="number" name="estimasi_jam" class="form-control" step="0.5" min="0.5" max="12" value="{{ old('estimasi_jam') }}" placeholder="mis. 4">
            </div>
            <div>
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi') }}" placeholder="Kantor / Work from Home">
            </div>
        </div>

        {{-- Pekerjaan --}}
        <div style="margin-bottom:18px;">
            <label class="form-label">Pekerjaan yang Dilakukan <span style="color:#dc2626;">*</span></label>
            <textarea name="pekerjaan" class="form-control" rows="3" maxlength="500" placeholder="Deskripsi detail pekerjaan/tugas yang harus diselesaikan..." required>{{ old('pekerjaan') }}</textarea>
        </div>

        {{-- Catatan --}}
        <div style="margin-bottom:18px;">
            <label class="form-label">Catatan Internal</label>
            <textarea name="catatan" class="form-control" rows="2" maxlength="300" placeholder="Catatan opsional untuk HRD...">{{ old('catatan') }}</textarea>
        </div>

        {{-- Langsung aktif --}}
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:14px 16px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
            <input type="hidden" name="langsung_aktif" value="0">
            <input type="checkbox" id="langsung_aktif" name="langsung_aktif" value="1" {{ old('langsung_aktif') ? 'checked' : 'checked' }}>
            <label for="langsung_aktif" style="font-size:13px;font-weight:600;margin:0;cursor:pointer;">
                Langsung Aktifkan & Notifikasi Karyawan
                <span style="display:block;font-size:11px;font-weight:400;color:#92400e;">Jika tidak dicentang, SPK akan disimpan sebagai Draft dan perlu diaktifkan secara manual.</span>
            </label>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan SPK
            </button>
            <a href="{{ route('admin.spk-lembur.index') }}" class="btn btn-secondary">Batal</a>
        </div>

        </form>
    </div>
</div>

<script>
function toggleTarget(val) {
    document.getElementById('wrapKaryawan').style.display = val === 'karyawan' ? '' : 'none';
    document.getElementById('wrapDept').style.display     = val === 'departemen' ? '' : 'none';
}
</script>
@endsection
