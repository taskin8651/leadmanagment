<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign in — LeadFlow CRM</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
<style>
body{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}
.auth-side{background:linear-gradient(155deg,#0f172a,#302b63 140%);color:#fff;padding:60px;display:flex;flex-direction:column;justify-content:space-between}
.auth-side .brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:19px}
.auth-side .mark{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.14);display:grid;place-items:center;font-size:19px}
.auth-form-wrap{display:flex;align-items:center;justify-content:center;padding:40px}
.auth-box{width:min(400px,100%)}
.feature-row{display:flex;gap:12px;align-items:flex-start;margin-bottom:18px}
.feature-row .fi{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.12);display:grid;place-items:center;flex-shrink:0}
@media(max-width:900px){body{grid-template-columns:1fr}.auth-side{display:none}}
</style>
</head>
<body>
<div class="auth-side fade-in">
    <div class="brand"><span class="mark"><i class="bi bi-diagram-3"></i></span>LeadFlow CRM</div>
    <div>
        <h2 class="fw-800 mb-3" style="font-size:28px;max-width:420px">Run your entire sales pipeline in one elegant place.</h2>
        <div class="mt-4">
            <div class="feature-row"><span class="fi"><i class="bi bi-people"></i></span><div><div class="fw-bold">Lead management</div><div style="color:#c7cbe8;font-size:12.8px">Track every prospect from first touch to close.</div></div></div>
            <div class="feature-row"><span class="fi"><i class="bi bi-calendar-check"></i></span><div><div class="fw-bold">Smart follow-ups</div><div style="color:#c7cbe8;font-size:12.8px">Never miss a scheduled call again.</div></div></div>
            <div class="feature-row"><span class="fi"><i class="bi bi-arrow-repeat"></i></span><div><div class="fw-bold">Team &amp; attendance</div><div style="color:#c7cbe8;font-size:12.8px">Manage staff and track attendance in one place.</div></div></div>
        </div>
    </div>
    <div style="color:#8891b8;font-size:12px">© {{ date('Y') }} LeadFlow CRM</div>
</div>

<div class="auth-form-wrap">
    <div class="auth-box fade-up">
        <div class="d-lg-none text-center mb-4"><span class="mark d-inline-grid" style="background:var(--primary-soft);color:var(--primary);width:44px;height:44px;border-radius:12px;place-items:center"><i class="bi bi-diagram-3" style="font-size:20px"></i></span></div>
        <h3 class="fw-800 mb-1">Welcome back</h3>
        <p class="muted mb-4">Sign in to your account to continue.</p>

        @if($errors->any())
            <div class="alert border-0 mb-3 d-flex gap-2 align-items-start" style="background:var(--danger-soft);color:#b91c1c;border-radius:12px">
                <i class="bi bi-exclamation-triangle mt-1"></i>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <label class="form-label">Email</label>
            <div class="input-icon-group mb-3"><i class="bi bi-envelope"></i><input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus></div>
            <label class="form-label">Password</label>
            <div class="input-icon-group mb-3"><i class="bi bi-lock"></i><input class="form-control" name="password" type="password" placeholder="••••••••" required></div>
            <div class="form-check mb-3 d-flex justify-content-between align-items-center">
                <span><input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small muted" for="remember">Remember me</label></span>
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
            </div>
            <button class="btn btn-primary w-100 py-2">Sign in</button>
        </form>

        <div class="text-center muted small mt-4">Demo: admin@example.com / password</div>
    </div>
</div>
</body>
</html>
