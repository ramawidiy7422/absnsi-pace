@extends('layouts.admin')
@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')

@section('content')
<div style="max-width:480px;">
    <div class="card">
        <div class="card-header">
            <h6 style="margin:0;font-weight:700;"><i class="fas fa-key" style="color:#6b46c1;"></i> Ganti Password</h6>
        </div>
        <div class="card-body">

            @if($errors->any())
            <div style="background:#fed7d7;color:#c53030;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('portal.ganti-password.update') }}">
                @csrf

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">
                        Password Lama *
                    </label>
                    <input type="password" name="password_lama" class="form-control"
                           placeholder="Masukkan password saat ini" required autocomplete="current-password"
                           style="@error('password_lama') border-color:#fc8181; @enderror">
                    @error('password_lama')
                    <div style="color:#c53030;font-size:12px;margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">
                        Password Baru * <span style="font-weight:400;color:#a0aec0;">(min. 8 karakter)</span>
                    </label>
                    <input type="password" name="password_baru" class="form-control"
                           placeholder="Password baru" required minlength="8" autocomplete="new-password"
                           id="pw_baru">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:12px;font-weight:700;color:#4a5568;display:block;margin-bottom:5px;">
                        Konfirmasi Password Baru *
                    </label>
                    <input type="password" name="password_baru_confirmation" class="form-control"
                           placeholder="Ulangi password baru" required minlength="8" autocomplete="new-password"
                           id="pw_konfirm">
                    <div id="matchHint" style="font-size:12px;margin-top:4px;display:none;"></div>
                </div>

                <div style="display:flex;gap:10px;align-items:center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Password
                    </button>
                    <a href="{{ route('portal.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div style="margin-top:14px;background:#fefcbf;border:1px solid #f6e05e;border-radius:8px;padding:12px 16px;font-size:12px;color:#744210;">
        <strong><i class="fas fa-info-circle"></i> Tips keamanan:</strong>
        <ul style="margin:6px 0 0 16px;padding:0;">
            <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
            <li>Jangan gunakan tanggal lahir atau nama sebagai password</li>
            <li>Jangan bagikan password kepada siapapun</li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
const pwBaru    = document.getElementById('pw_baru');
const pwKonfirm = document.getElementById('pw_konfirm');
const hint      = document.getElementById('matchHint');

function checkMatch() {
    if (!pwKonfirm.value) { hint.style.display = 'none'; return; }
    if (pwBaru.value === pwKonfirm.value) {
        hint.style.display = 'block';
        hint.style.color   = '#276749';
        hint.textContent   = '✓ Password cocok';
    } else {
        hint.style.display = 'block';
        hint.style.color   = '#c53030';
        hint.textContent   = '✗ Password tidak cocok';
    }
}

pwBaru.addEventListener('input', checkMatch);
pwKonfirm.addEventListener('input', checkMatch);
</script>
@endpush
@endsection
