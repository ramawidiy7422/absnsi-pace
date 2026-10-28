@extends('layouts.admin')
@section('title', 'Input Absensi Manual')
@section('page-title', 'Input Absensi Manual')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h6><i class="fas fa-plus"></i> Input Absensi Manual</h6>
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.absensi.store') }}">
            @csrf
            <div class="form-group">
                <label>Karyawan *</label>
                <select name="karyawan_id" class="form-select" required>
                    <option value="">Pilih Karyawan...</option>
                    @foreach($karyawan as $k)
                        <option value="{{ $k->id }}" {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nik }} - {{ $k->nama }} ({{ $k->department?->name ?? $k->departemen }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal *</label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', today()->format('Y-m-d')) }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk') }}">
                </div>
                <div class="form-group">
                    <label>Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar') }}">
                </div>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-select" required>
                    <option value="hadir">Hadir</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional...">{{ old('keterangan') }}</textarea>
            </div>
            <div class="form-group" id="box-keterangan-terlambat" style="display:none;">
                <label style="color:#c05621;font-weight:600;">
                    <i class="fas fa-clock"></i> Alasan Keterlambatan
                    <small style="font-weight:400;color:#718096;"> (opsional)</small>
                </label>
                <textarea name="keterangan_terlambat" class="form-control" rows="2"
                    placeholder="Contoh: Kemacetan, ban bocor, urusan keluarga mendesak..."
                    >{{ old('keterangan_terlambat') }}</textarea>
                <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:11px;color:#718096;">Alasan umum:</span>
                    @foreach(['Kemacetan','Ban bocor','Hujan deras','Urusan keluarga','Kendaraan mogok','Sakit mendadak'] as $alasan)
                    <button type="button" onclick="setAlasan('{{ $alasan }}')"
                        style="font-size:11px;padding:2px 8px;border:1px solid #e2e8f0;border-radius:5px;background:#f7fafc;cursor:pointer;color:#4a5568;">
                        {{ $alasan }}
                    </button>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Absensi</button>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.querySelector('select[name=status]').addEventListener('change', function() {
    document.getElementById('box-keterangan-terlambat').style.display = this.value === 'terlambat' ? '' : 'none';
});
function setAlasan(text) {
    document.querySelector('textarea[name=keterangan_terlambat]').value = text;
}
</script>
@endpush
@endsection
