@extends('layouts.admin')
@section('title', 'Edit Absensi')
@section('page-title', 'Edit Data Absensi')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h6><i class="fas fa-edit"></i> Edit Absensi: {{ $absensi->karyawan->nama ?? '' }}</h6>
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <div style="background:#f7fafc; padding:12px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            <strong>{{ $absensi->karyawan->nama }}</strong> — {{ $absensi->karyawan->nik }}<br>
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
                    @foreach(['hadir','terlambat','izin','sakit','alpha'] as $s)
                        <option value="{{ $s }}" {{ $absensi->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $absensi->keterangan) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        </form>
    </div>
</div>
@endsection
