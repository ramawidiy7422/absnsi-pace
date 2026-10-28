<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $namaPerusahaan }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v={{ filemtime(public_path('favicon.ico')) }}">
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <script>
        // Apply dark mode before render — no flash
        (function(){
            var t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        :root {
            --rp-bg:      #ffffff;
            --rp-text:    #1a202c;
            --rp-sub:     #a0aec0;
            --rp-label:   #4a5568;
            --rp-input:   #f8fafc;
            --rp-input-focus: #ffffff;
            --rp-border:  #e2e8f0;
            --rp-divider: #f0f0f0;
            --rp-kiosk:   #4299e1;
            --rp-error-bg: #fff5f5;
            --rp-error-cl: #c53030;
            --rp-error-br: #fed7d7;
        }
        [data-theme="dark"] {
            --rp-bg:      #13151f;
            --rp-text:    #e2e8f0;
            --rp-sub:     #64748b;
            --rp-label:   #94a3b8;
            --rp-input:   #1e2235;
            --rp-input-focus: #22263a;
            --rp-border:  #2d3448;
            --rp-divider: #2d3448;
            --rp-kiosk:   #60a5fa;
            --rp-error-bg: #3a1a1a;
            --rp-error-cl: #fc8181;
            --rp-error-br: #c53030;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0d1526;
            transition: background 0.3s;
        }

        /* ── Kiri: background foto/brand ── */
        .left-panel {
            flex: 1;
            background:
                linear-gradient(135deg, rgba(10,14,26,0.82) 0%, rgba(13,21,38,0.75) 100%),
                url('/images/gmp1.jpg') center center / cover no-repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            gap: 24px;
        }
        .left-panel img.big-logo {
            width: 180px;
            opacity: 0.95;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
        }
        .left-panel .company-name {
            text-align: center;
        }
        .left-panel .company-name h2 {
            color: white;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }
        .left-panel .company-name p {
            color: rgba(255,255,255,0.55);
            font-size: 13px;
            margin-top: 6px;
        }
        .left-panel .divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #4299e1, #34d399);
            border-radius: 2px;
        }
        .left-panel .desc {
            color: rgba(255,255,255,0.45);
            font-size: 12px;
            text-align: center;
            max-width: 280px;
            line-height: 1.8;
        }

        /* ── Kanan: form login ── */
        .right-panel {
            width: 420px;
            flex-shrink: 0;
            background: var(--rp-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            transition: background 0.3s;
        }

        /* Dark mode toggle di pojok */
        .dm-btn {
            position: absolute;
            top: 20px; right: 20px;
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--rp-border);
            background: var(--rp-input);
            color: var(--rp-label);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            transition: all 0.2s;
        }
        .dm-btn:hover { background: var(--rp-border); }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
            width: 100%;
        }
        .login-header .logo-small { width: 140px; margin-bottom: 12px; }
        .login-header h3 { font-size: 20px; font-weight: 800; color: var(--rp-text); }
        .login-header p  { font-size: 13px; color: var(--rp-sub); margin-top: 4px; }

        .form-group { margin-bottom: 16px; width: 100%; }
        .form-group label {
            display: block; margin-bottom: 6px;
            font-size: 12px; font-weight: 700;
            color: var(--rp-label); letter-spacing: 0.3px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--rp-border); font-size: 14px;
        }
        .input-wrap input {
            width: 100%; padding: 11px 14px 11px 38px;
            border: 1.5px solid var(--rp-border); border-radius: 9px;
            font-size: 14px; color: var(--rp-text);
            transition: border-color .2s, box-shadow .2s, background .2s;
            background: var(--rp-input);
        }
        .input-wrap input:focus {
            outline: none; border-color: #4299e1;
            background: var(--rp-input-focus);
            box-shadow: 0 0 0 3px rgba(66,153,225,0.12);
        }
        .input-wrap input::placeholder { color: var(--rp-sub); }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #2b6cb0, #4299e1);
            color: white; border: none; border-radius: 9px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            margin-top: 8px; transition: opacity .2s, transform .2s;
            letter-spacing: 0.3px;
        }
        .btn-login:hover { opacity: .92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .alert-error {
            background: var(--rp-error-bg); color: var(--rp-error-cl);
            border: 1px solid var(--rp-error-br);
            padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-bottom: 16px; width: 100%;
        }

        .kiosk-link {
            margin-top: 28px; text-align: center; width: 100%;
            padding-top: 20px; border-top: 1px solid var(--rp-divider);
        }
        .kiosk-link a { color: var(--rp-kiosk); font-size: 13px; text-decoration: none; font-weight: 500; }
        .kiosk-link a:hover { text-decoration: underline; }

        /* Mobile: sembunyikan panel kiri */
        @media (max-width: 700px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>

{{-- Panel Kiri --}}
<div class="left-panel">
    <img class="big-logo" src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
    <div class="company-name">
        <h2>{{ $namaPerusahaan }}</h2>
        <p>Kab. Demak, Jawa Tengah</p>
    </div>
    <div class="divider"></div>
    <p class="desc">
        Sistem Absensi Digital berbasis<br>
        Face Recognition & QR Code
    </p>
</div>

{{-- Panel Kanan --}}
<div class="right-panel">
    <button class="dm-btn" id="dmToggle" onclick="toggleDM()" title="Ganti tema">
        <i id="dmIcon" class="fas fa-moon"></i>
    </button>

    <div class="login-header">
        <img class="logo-small" src="{{ $logoPerusahaan ?? '/images/logokecil.png' }}" alt="{{ $namaPerusahaan ?? 'Logo' }}">
        <h3>Selamat Datang</h3>
        <p>Masuk untuk melanjutkan ke sistem</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="width:100%;">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <div class="input-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="email@perusahaan.com" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </button>
        <div style="text-align:center;margin-top:14px;">
            <a href="{{ route('lupa-password') }}" style="font-size:12px;color:var(--rp-sub);text-decoration:none;">
                <i class="fas fa-key"></i> Lupa password?
            </a>
        </div>
    </form>

    {{-- Terminal Absensi Digital disembunyikan dari halaman login --}}
    {{--
    <div class="kiosk-link">
        <a href="{{ route('kiosk.index') }}">
            <i class="fas fa-fingerprint"></i> Buka Terminal Absensi Digital
        </a>
    </div>
    --}}
</div>

<script>
(function(){
    var t = localStorage.getItem('theme') || 'light';
    var icon = document.getElementById('dmIcon');
    if (icon) icon.className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
})();

function toggleDM(){
    var html = document.documentElement;
    var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    var icon = document.getElementById('dmIcon');
    if (icon) icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}
</script>
</body>
</html>
