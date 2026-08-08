<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify Email — LeadFlow CRM</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
<style>body{min-height:100vh;display:grid;place-items:center;background:var(--background);padding:24px}.box{width:min(440px,100%);background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);padding:36px;text-align:center}</style>
</head>
<body>
<div class="box fade-up">
    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft);color:var(--primary);font-size:26px"><i class="bi bi-envelope-paper"></i></div>
    <h4 class="fw-800 mb-2">Verify your email</h4>
    <p class="muted mb-4" style="font-size:13.3px">We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Click it to confirm your account.</p>

    @if(session('success'))<div class="alert border-0 mb-3" style="background:var(--success-soft);color:#15803d;border-radius:12px">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary w-100 py-2">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button class="btn btn-ghost w-100 py-2">Logout</button>
    </form>
</div>
</body>
</html>
