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
                            {{ $k->nik }} - {{ $k->nama }} ({{ $k->departemen }})
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
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional...">{{ old('keterangan') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Absensi</button>
        </form>
    </div>
</div>
@endsection
