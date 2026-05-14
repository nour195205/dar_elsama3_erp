<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - دار السمع</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <style>
        body { background: #0b0f19; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 3rem; width: 100%; max-width: 420px; }
        .login-box h1 { text-align: center; margin-bottom: 0.5rem; font-size: 1.6rem; }
        .login-box p { text-align: center; color: #64748b; margin-bottom: 2rem; font-size: 0.9rem; }
        .login-box input { width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; margin-bottom: 1rem; font-size: 1rem; }
        .login-box button { width: 100%; padding: 1rem; border-radius: 12px; background: #00e699; color: #111; font-weight: 700; border: none; cursor: pointer; font-size: 1rem; }
        .login-box button:hover { background: #00cc88; }
        .login-logo { text-align: center; margin-bottom: 1.5rem; }
        .login-logo i { font-size: 3rem; color: #00e699; }
        .alert-err { background: rgba(255,77,77,0.1); color: #ff4d4d; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo"><i class="fas fa-ear-listen"></i></div>
        <h1 style="color: white;">دار السمع</h1>
        <p>سجل دخولك للوصول للوحة الإدارة</p>
        @if(session('error'))
            <div class="alert-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</button>
        </form>
    </div>
</body>
</html>
