@extends('layouts.admin')
@section('title', 'Edit Shift')
@section('page-title', 'Edit Shift Kerja')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h6><i class="fas fa-edit"></i> Edit: {{ $shift->nama_shift }}</h6>
        <a href="{{ route('admin.shift.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.shift.update', $shift) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Shift *</label>
                <input type="text" name="nama_shift" class="form-control" value="{{ old('nama_shift', $shift->nama_shift) }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Jam Masuk *</label>
                    <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $shift->jam_masuk) }}" required>
                </div>
                <div class="form-group">
                    <label>Jam Keluar *</label>
                    <input type="time" name="jam_keluar" class="form-control" value="{{ old('jam_keluar', $shift->jam_keluar) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Toleransi Menit *</label>
                <input type="number" name="toleransi_menit" class="form-control" value="{{ old('toleransi_menit', $shift->toleransi_menit) }}" min="0" max="60" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="aktif" {{ $shift->status=='aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ $shift->status=='nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Shift</button>
        </form>
    </div>
</div>
@endsection
