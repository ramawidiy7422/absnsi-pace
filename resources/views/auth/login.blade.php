<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Global Multi Pharmalab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo { text-align: center; margin-bottom: 30px; }
        .login-logo .icon { font-size: 48px; color: #4299e1; margin-bottom: 10px; }
        .login-logo h2 { font-size: 22px; color: #2d3748; font-weight: 700; }
        .login-logo p { color: #718096; font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; color: #4a5568; }
        .form-group input {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #2d3748;
            transition: border 0.2s;
        }
        .form-group input:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66,153,225,0.15); }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a0aec0; }
        .input-icon input { padding-left: 38px; }
        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #4299e1, #2b6cb0);
            color: white; border: none;
            border-radius: 8px; font-size: 15px;
            font-weight: 600; cursor: pointer;
            margin-top: 8px; transition: transform 0.2s;
        }
        .btn-login:hover { transform: translateY(-1px); }
        .alert-error { background: #fed7e2; color: #c53030; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .default-info { margin-top: 20px; padding: 12px; background: #f7fafc; border-radius: 8px; font-size: 12px; color: #718096; text-align: center; }
        .kiosk-link { text-align: center; margin-top: 15px; }
        .kiosk-link a { color: #4299e1; font-size: 13px; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <div class="icon"><i class="fas fa-capsules"></i></div>
        <h2>PT Global Multi Pharmalab</h2>
        <p>Sistem Absensi Face Recognition</p>
    </div>

    @if($errors->any())
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <div class="input-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@pabrikfarmasi.com" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Masuk</button>
    </form>

    <div class="default-info">
        <strong>Default Login:</strong><br>
        Email: admin@pabrikfarmasi.com | Password: admin123
    </div>

    <div class="kiosk-link">
        <a href="{{ route('kiosk.index') }}"><i class="fas fa-camera"></i> Buka Terminal Absensi</a>
    </div>
</div>
</body>
</html>
