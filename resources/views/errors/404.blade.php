<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <script>
        (function(){
            var t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        :root {
            --bg:      #f0f2f5;
            --card:    #ffffff;
            --text:    #2d3748;
            --sub:     #718096;
            --border:  #e2e8f0;
            --accent:  #4299e1;
        }
        [data-theme="dark"] {
            --bg:      #0f1117;
            --card:    #1a1d27;
            --text:    #e2e8f0;
            --sub:     #94a3b8;
            --border:  #2d3448;
            --accent:  #60a5fa;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            transition: background 0.3s, color 0.3s;
        }

        .container {
            text-align: center;
            max-width: 480px;
            background: var(--card);
            border-radius: 24px;
            padding: 48px 40px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
        }

        .code {
            font-size: 96px;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #4299e1, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .icon-wrap {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #ebf8ff, #e9d8fd);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
        }
        [data-theme="dark"] .icon-wrap { background: linear-gradient(135deg, #1e3a5f, #2a1a3a); }

        h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 10px;
        }

        p {
            font-size: 14px;
            color: var(--sub);
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .btn-group { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }

        .btn {
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary { background: #4299e1; color: white; }
        .btn-primary:hover { background: #2b6cb0; transform: translateY(-1px); }
        .btn-secondary { background: var(--bg); color: var(--sub); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); color: var(--text); }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 28px 0;
        }

        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        .quick-link {
            font-size: 12px;
            color: var(--accent);
            text-decoration: none;
            padding: 5px 12px;
            border: 1px solid var(--border);
            border-radius: 20px;
            transition: all 0.15s;
        }
        .quick-link:hover { background: var(--border); }

        .logo-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 32px;
        }
        .logo-box {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #4299e1, #667eea);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 14px;
        }
        .logo-text { font-size: 13px; font-weight: 700; color: var(--text); }
        .logo-sub  { font-size: 10px; color: var(--sub); }
    </style>
</head>
<body>
<div class="container">

    <div class="logo-bar">
        <div class="logo-box">GMP</div>
        <div>
            <div class="logo-text">{{ $namaPerusahaan }}</div>
            <div class="logo-sub">Sistem Absensi Digital</div>
        </div>
    </div>

    <div class="icon-wrap">
        <i class="fas fa-map-signs" style="background:linear-gradient(135deg,#4299e1,#667eea);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"></i>
    </div>

    <div class="code">404</div>

    <h1>Halaman Tidak Ditemukan</h1>
    <p>Halaman yang Anda cari tidak ada, sudah dipindahkan, atau Anda tidak memiliki akses ke halaman ini.</p>

    <div class="btn-group">
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @auth
            @if(auth()->user()->role === 'karyawan')
                <a href="{{ url('/portal') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Portal Saya
                </a>
            @else
                <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            @endif
        @else
            <a href="{{ url('/login') }}" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        @endauth
    </div>

    <hr class="divider">

    <div class="quick-links">
        @auth
        <a href="{{ url('/admin/karyawan') }}" class="quick-link"><i class="fas fa-users"></i> Karyawan</a>
        <a href="{{ url('/admin/absensi') }}" class="quick-link"><i class="fas fa-calendar-check"></i> Absensi</a>
        <a href="{{ url('/admin/dashboard') }}" class="quick-link"><i class="fas fa-chart-bar"></i> Dashboard</a>
        <a href="{{ url('/portal') }}" class="quick-link"><i class="fas fa-user"></i> Portal</a>
        @else
        <a href="{{ url('/login') }}" class="quick-link"><i class="fas fa-sign-in-alt"></i> Login</a>
        @endauth
    </div>

</div>
</body>
</html>
