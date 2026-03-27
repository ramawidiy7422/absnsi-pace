@extends('layouts.admin')
@section('title', 'Tambah Shift')
@section('page-title', 'Tambah Shift Kerja')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h6><i class="fas fa-plus"></i> Form Tambah Shift</h6>
        <a href="{{ route('admin.shift.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.shift.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Shift *</label>
                <input type="text" name="nama_shift" class="form-control" value="{{ old('nama_shift') }}" required placeholder="Contoh: Shift Pagi">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Jam Masuk *</label>
                    <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk') }}" required>
                </div>
                <div class="form-group">
                    <label>Jam Keluar *</label>
                    <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Toleransi Keterlambatan (menit) *</label>
                <input type="number" name="toleransi_menit" class="form-control" value="{{ old('toleransi_menit', 15) }}" min="0" max="60" required>
                <small style="color:#718096; font-size:12px;">Karyawan masuk melebihi waktu ini dianggap terlambat</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Shift</button>
        </form>
    </div>
</div>
@endsection
