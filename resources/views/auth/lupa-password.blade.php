<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — {{ $namaPerusahaan }}</title>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <script>
        (function(){
            var t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        :root {
            --rp-bg: #ffffff; --rp-text: #1a202c; --rp-sub: #a0aec0;
            --rp-label: #4a5568; --rp-input: #f8fafc; --rp-input-focus: #ffffff;
            --rp-border: #e2e8f0; --rp-divider: #f0f0f0;
            --rp-error-bg: #fff5f5; --rp-error-cl: #c53030; --rp-error-br: #fed7d7;
        }
        [data-theme="dark"] {
            --rp-bg: #13151f; --rp-text: #e2e8f0; --rp-sub: #64748b;
            --rp-label: #94a3b8; --rp-input: #1e2235; --rp-input-focus: #22263a;
            --rp-border: #2d3448; --rp-divider: #2d3448;
            --rp-error-bg: #3a1a1a; --rp-error-cl: #fc8181; --rp-error-br: #c53030;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; min-height:100vh; display:flex; background:#0d1526; }
        .left-panel {
            flex:1;
            background: linear-gradient(135deg,rgba(10,14,26,0.82) 0%,rgba(13,21,38,0.75) 100%),
                url('/images/gmp1.jpg') center center/cover no-repeat;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            padding:48px; gap:24px;
        }
        .left-panel img.big-logo { width:180px; opacity:0.95; filter:drop-shadow(0 2px 8px rgba(0,0,0,0.3)); }
        .left-panel h2 { color:white; font-size:22px; font-weight:800; text-align:center; }
        .left-panel p.sub { color:rgba(255,255,255,0.55); font-size:13px; margin-top:6px; text-align:center; }
        .left-panel .divider { width:48px; height:3px; background:linear-gradient(90deg,#4299e1,#34d399); border-radius:2px; }
        .left-panel .desc { color:rgba(255,255,255,0.45); font-size:12px; text-align:center; max-width:280px; line-height:1.8; }
        .right-panel { width:420px; flex-shrink:0; background:var(--rp-bg); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 40px; position:relative; }
        .dm-btn { position:absolute; top:20px; right:20px; width:34px; height:34px; border-radius:8px; border:1px solid var(--rp-border); background:var(--rp-input); color:var(--rp-label); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .login-header { text-align:center; margin-bottom:28px; width:100%; }
        .login-header .logo-small { width:100px; margin-bottom:12px; }
        .login-header h3 { font-size:20px; font-weight:800; color:var(--rp-text); }
        .login-header p { font-size:13px; color:var(--rp-sub); margin-top:4px; }
        .form-group { margin-bottom:16px; width:100%; }
        .form-group label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; color:var(--rp-label); }
        .input-wrap { position:relative; }
        .input-wrap i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--rp-border); font-size:14px; }
        .input-wrap input { width:100%; padding:11px 14px 11px 38px; border:1.5px solid var(--rp-border); border-radius:9px; font-size:14px; color:var(--rp-text); background:var(--rp-input); transition:border-color .2s,box-shadow .2s; }
        .input-wrap input:focus { outline:none; border-color:#4299e1; background:var(--rp-input-focus); box-shadow:0 0 0 3px rgba(66,153,225,0.12); }
        .input-wrap input::placeholder { color:var(--rp-sub); }
        .btn-submit { width:100%; padding:13px; background:linear-gradient(135deg,#2b6cb0,#4299e1); color:white; border:none; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; margin-top:8px; transition:opacity .2s,transform .2s; }
        .btn-submit:hover { opacity:.92; transform:translateY(-1px); }
        .alert-error { background:var(--rp-error-bg); color:var(--rp-error-cl); border:1px solid var(--rp-error-br); padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px; width:100%; }
        .info-box { background:#ebf8ff; border:1px solid #bee3f8; border-radius:10px; padding:14px; margin-bottom:20px; width:100%; font-size:12px; color:#2b6cb0; line-height:1.7; }
        .back-link { margin-top:24px; text-align:center; width:100%; padding-top:18px; border-top:1px solid var(--rp-divider); }
        .back-link a { color:#4299e1; font-size:13px; text-decoration:none; font-weight:500; }
        .back-link a:hover { text-decoration:underline; }
        @media (max-width:700px) { .left-panel { display:none; } .right-panel { width:100%; padding:40px 24px; } }
    </style>
</head>
<body>
<div class="left-panel">
    <img class="big-logo" src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
    <div>
        <h2>{{ $namaPerusahaan }}</h2>
        <p class="sub">Kab. Demak, Jawa Tengah</p>
    </div>
    <div class="divider"></div>
    <p class="desc">Reset password menggunakan<br>email dan ID Karyawan Anda</p>
</div>

<div class="right-panel">
    <button class="dm-btn" onclick="toggleDM()"><i id="dmIcon" class="fas fa-moon"></i></button>

    <div class="login-header">
        <img class="logo-small" src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
        <h3>Lupa Password</h3>
        <p>Verifikasi identitas Anda untuk reset password</p>
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        Masukkan <strong>email</strong> dan <strong>ID Karyawan</strong> (format: <code>YYYYMMNN</code>, contoh: <code>20240101</code>).
        Password baru akan ditampilkan setelah verifikasi berhasil.
    </div>

    @if($errors->any())
    <div class="alert-error">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('lupa-password.submit') }}" style="width:100%;">
        @csrf
        <div class="form-group">
            <label>Email Akun</label>
            <div class="input-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@perusahaan.com" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label>ID Karyawan (NIK Sistem)</label>
            <div class="input-wrap">
                <i class="fas fa-id-badge"></i>
                <input type="text" name="nik" value="{{ old('nik') }}" placeholder="Contoh: 20240101" required>
            </div>
        </div>
        <button type="submit" class="btn-submit">
            <i class="fas fa-key"></i> Reset Password Saya
        </button>
    </form>

    <div class="back-link">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
    </div>
</div>

<script>
(function(){
    var t = localStorage.getItem('theme') || 'light';
    document.getElementById('dmIcon').className = t==='dark' ? 'fas fa-sun' : 'fas fa-moon';
})();
function toggleDM(){
    var html = document.documentElement;
    var next = html.getAttribute('data-theme')==='dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('dmIcon').className = next==='dark' ? 'fas fa-sun' : 'fas fa-moon';
}
</script>
</body>
</html>
