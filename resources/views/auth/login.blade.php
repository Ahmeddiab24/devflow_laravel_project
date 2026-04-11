<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DevFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#0d0f14; --surface:#161920; --border:#2a2f3e; --accent:#6c63ff; --text:#e8eaf0; --muted:#7a8099; --danger:#ff4d6d; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--bg); color:var(--text); font-family:'Inter',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; }

        /* animated grid background */
        body::before {
            content:'';
            position:fixed; inset:0;
            background-image: linear-gradient(rgba(108,99,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(108,99,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events:none;
        }

        .auth-container { width:100%; max-width:400px; padding:24px; position:relative; z-index:1; }

        .logo { text-align:center; margin-bottom:32px; }
        .logo-icon { display:inline-flex; width:48px; height:48px; background:var(--accent); border-radius:12px; align-items:center; justify-content:center; font-size:24px; margin-bottom:12px; }
        .logo-text { font-family:'Syne',sans-serif; font-size:28px; font-weight:800; }
        .logo-sub { font-size:13px; color:var(--muted); margin-top:4px; font-family:'JetBrains Mono',monospace; }

        .card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:32px; }
        .card-title { font-family:'Syne',sans-serif; font-size:20px; font-weight:700; margin-bottom:6px; }
        .card-sub { font-size:13px; color:var(--muted); margin-bottom:24px; }

        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:12px; font-weight:600; color:var(--muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
        .form-control { width:100%; background:#1e2230; border:1px solid var(--border); border-radius:8px; padding:10px 14px; color:var(--text); font-size:14px; font-family:'Inter',sans-serif; transition:border-color .15s; }
        .form-control:focus { outline:none; border-color:var(--accent); }
        .form-error { font-size:12px; color:var(--danger); margin-top:4px; }

        .btn { display:block; width:100%; padding:11px; border-radius:8px; border:none; font-size:14px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; transition:all .15s; }
        .btn-primary { background:var(--accent); color:white; }
        .btn-primary:hover { background:#5a54e0; }

        .auth-footer { text-align:center; margin-top:20px; font-size:13px; color:var(--muted); }
        .auth-footer a { color:var(--accent); text-decoration:none; }

        .demo-creds { background:rgba(108,99,255,0.08); border:1px solid rgba(108,99,255,0.2); border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:12px; font-family:'JetBrains Mono',monospace; color:var(--muted); }
        .demo-creds span { color:var(--text); }

        .alert-danger { background:rgba(255,77,109,0.1); border:1px solid rgba(255,77,109,0.3); border-radius:8px; padding:10px 14px; font-size:13px; color:var(--danger); margin-bottom:16px; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="logo">
        <div class="logo-icon">⚡</div>
        <div class="logo-text">DevFlow</div>
        <div class="logo-sub">DevOps Practice App</div>
    </div>

    <div class="card">
        <div class="card-title">Welcome back</div>
        <div class="card-sub">Sign in to your account</div>

        <div class="demo-creds">
            Demo: <span>admin@devflow.local</span> / <span>password</span>
        </div>

        @if($errors->any())
            <div class="alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Create one →</a>
    </div>
</div>
</body>
</html>
