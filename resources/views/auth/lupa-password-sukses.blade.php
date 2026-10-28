<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Terkirim — {{ $namaPerusahaan }}</title>
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
            --rp-label: #4a5568; --rp-border: #e2e8f0; --rp-divider: #f0f0f0;
        }
        [data-theme="dark"] {
            --rp-bg: #13151f; --rp-text: #e2e8f0; --rp-sub: #64748b;
            --rp-label: #94a3b8; --rp-border: #2d3448; --rp-divider: #2d3448;
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
        .left-panel img { width:180px; opacity:0.95; }
        .left-panel h2 { color:white; font-size:22px; font-weight:800; text-align:center; }
        .left-panel p { color:rgba(255,255,255,0.55); font-size:13px; margin-top:6px; text-align:center; }
        .left-panel .divider { width:48px; height:3px; background:linear-gradient(90deg,#4299e1,#34d399); border-radius:2px; }
        .right-panel { width:440px; flex-shrink:0; background:var(--rp-bg); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 40px; }
        .icon-check { width:72px; height:72px; background:#f0fff4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
        .icon-check i { font-size:32px; color:#38a169; }
        h3 { font-size:20px; font-weight:800; color:var(--rp-text); text-align:center; margin-bottom:6px; }
        .sub { font-size:13px; color:var(--rp-sub); text-align:center; margin-bottom:24px; }
        .password-box {
            background:#1a202c; border-radius:12px; padding:20px 24px;
            width:100%; text-align:center; margin-bottom:20px; position:relative;
        }
        .password-box .label { font-size:11px; color:#718096; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; }
        .password-box .pw { font-size:28px; font-weight:800; color:#68d391; font-family:monospace; letter-spacing:4px; }
        .copy-btn {
            margin-top:12px; padding:7px 18px; background:#2d3748; color:#a0aec0;
            border:none; border-radius:7px; font-size:12px; cursor:pointer;
            transition:background .2s;
        }
        .copy-btn:hover { background:#4a5568; color:white; }
        .warning-box { background:#fffbeb; border:1px solid #fbd38d; border-radius:10px; padding:14px; width:100%; margin-bottom:20px; font-size:12px; color:#92400e; line-height:1.7; }
        .btn-login { width:100%; padding:13px; background:linear-gradient(135deg,#2b6cb0,#4299e1); color:white; border:none; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; text-decoration:none; display:block; text-align:center; transition:opacity .2s; }
        .btn-login:hover { opacity:.92; }
        @media (max-width:700px) { .left-panel { display:none; } .right-panel { width:100%; padding:40px 24px; } }
    </style>
</head>
<body>
<div class="left-panel">
    <img src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
    <div>
        <h2>{{ $namaPerusahaan }}</h2>
        <p>Kab. Demak, Jawa Tengah</p>
    </div>
    <div class="divider"></div>
</div>

<div class="right-panel">
    <div class="icon-check"><i class="fas fa-paper-plane"></i></div>
    <h3>Pengajuan Terkirim!</h3>
    <p class="sub">Halo, <strong>{{ session('reset_name') }}</strong>. Permintaan reset password Anda sudah dikirim.</p>

    <div class="warning-box">
        <i class="fas fa-exclamation-triangle"></i>
        Permintaan Anda akan diperiksa oleh <strong>Admin/HRD</strong>. Setelah disetujui, password baru akan
        disampaikan langsung kepada Anda (tatap muka atau melalui atasan/HRD). Halaman ini tidak menampilkan
        password baru demi keamanan akun Anda.
    </div>

    <a href="{{ route('login') }}" class="btn-login">
        <i class="fas fa-arrow-left"></i> Kembali ke Login
    </a>
</div>
</body>
</html>
