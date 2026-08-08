<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password — LeadFlow CRM</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
<style>body{min-height:100vh;display:grid;place-items:center;background:var(--background);padding:24px}.box{width:min(420px,100%);background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);padding:36px}</style>
</head>
<body>
<div class="box fade-up">
    <h3 class="fw-800 mb-1">Forgot your password?</h3>
    <p class="muted mb-4" style="font-size:13.3px">Enter your email and we'll send you a reset link.</p>

    @if(session('success'))<div class="alert border-0 mb-3" style="background:var(--success-soft);color:#15803d;border-radius:12px">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert border-0 mb-3" style="background:var(--danger-soft);color:#b91c1c;border-radius:12px">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label class="form-label">Email</label>
        <div class="input-icon-group mb-3"><i class="bi bi-envelope"></i><input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
        <button class="btn btn-primary w-100 py-2">Send reset link</button>
    </form>
    <div class="text-center small mt-4"><a href="{{ route('login') }}" class="text-decoration-none">&larr; Back to sign in</a></div>
</div>
</body>
</html>
