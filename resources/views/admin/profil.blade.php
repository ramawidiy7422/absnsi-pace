@extends('layouts.admin')
@section('title', 'Profil')
@section('page-title', 'Profil Akun')

@section('content')
<div style="max-width:640px;">

    {{-- Info Akun --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h6><i class="fas fa-user-circle" style="color:#4299e1;"></i> Informasi Akun</h6>
        </div>
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px;">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#4299e1,#667eea);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:white;flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:#2d3748;">{{ auth()->user()->name }}</div>
                    <div style="font-size:13px;color:#718096;">{{ auth()->user()->email }}</div>
                    <span style="font-size:11px;background:#ebf8ff;color:#2b6cb0;padding:2px 10px;border-radius:10px;font-weight:600;margin-top:4px;display:inline-block;">
                        {{ ucfirst(auth()->user()->role ?? 'admin') }}
                    </span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.profil.nama') }}">
                @csrf
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name') <small style="color:#c53030;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled style="background:#f7fafc;color:#718096;">
                    <small style="color:#a0aec0;">Email tidak dapat diubah</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>

    {{-- Ganti Password --}}
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-lock" style="color:#e53e3e;"></i> Ganti Password</h6>
        </div>
        <div class="card-body">
            @if(session('success_pass'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success_pass') }}</div>
            @endif
            @if(session('error_pass'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error_pass') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.profil.password') }}">
                @csrf
                <div class="form-group">
                    <label>Password Lama</label>
                    <div style="position:relative;">
                        <input type="password" name="password_lama" id="pwdLama" class="form-control" required style="padding-right:40px;">
                        <i class="fas fa-eye" onclick="togglePwd('pwdLama',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                    </div>
                    @error('password_lama') <small style="color:#c53030;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <div style="position:relative;">
                        <input type="password" name="password_baru" id="pwdBaru" class="form-control" required style="padding-right:40px;">
                        <i class="fas fa-eye" onclick="togglePwd('pwdBaru',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                    </div>
                    @error('password_baru') <small style="color:#c53030;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <div style="position:relative;">
                        <input type="password" name="password_baru_confirmation" id="pwdKonfirm" class="form-control" required style="padding-right:40px;">
                        <i class="fas fa-eye" onclick="togglePwd('pwdKonfirm',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#a0aec0;cursor:pointer;"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger"><i class="fas fa-key"></i> Ganti Password</button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function togglePwd(id, icon) {
    const el = document.getElementById(id);
    if (el.type === 'password') {
        el.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        el.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
