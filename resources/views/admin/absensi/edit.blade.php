@extends('layouts.admin')
@section('title', 'Edit Absensi')
@section('page-title', 'Edit Data Absensi')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h6><i class="fas fa-edit"></i> Edit Absensi: {{ $absensi->karyawan?->nama ?? '' }}</h6>
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <div style="background:#f7fafc; padding:12px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            <strong>{{ $absensi->karyawan?->nama ?? '-' }}</strong> — {{ $absensi->karyawan?->nik ?? '-' }}<br>
            Tanggal: {{ \Carbon\Carbon::parse($absensi->tanggal)->format('d F Y') }}
        </div>

        <form method="POST" action="{{ route('admin.absensi.update', $absensi) }}">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $absensi->jam_masuk) }}">
                </div>
                <div class="form-group">
                    <label>Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar', $absensi->jam_keluar) }}">
                </div>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-select" required>
                    @php $statusLabels = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha','cuti'=>'Cuti']; @endphp
                    @foreach($statusLabels as $sv => $sl)
                        <option value="{{ $sv }}" {{ $absensi->status == $sv ? 'selected' : '' }}>{{ $sl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $absensi->keterangan) }}</textarea>
            </div>
            <div class="form-group" id="box-keterangan-terlambat" style="{{ old('status', $absensi->status) === 'terlambat' ? '' : 'display:none;' }}">
                <label style="color:#c05621;font-weight:600;">
                    <i class="fas fa-clock"></i> Alasan Keterlambatan
                    <small style="font-weight:400;color:#718096;"> (opsional — jika ada alasan yang dapat diterima)</small>
                </label>
                <textarea name="keterangan_terlambat" class="form-control" rows="2"
                    placeholder="Contoh: Macet parah di tol, ban bocor, urusan keluarga mendesak..."
                    >{{ old('keterangan_terlambat', $absensi->keterangan_terlambat) }}</textarea>
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
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
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
